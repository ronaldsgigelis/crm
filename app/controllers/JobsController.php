<?php
// DB savienojums (PDO)
require_once __DIR__ . '/../db.php';
// Palīgfunkcijas: render(), redirect(), url(), h()
require_once __DIR__ . '/../helpers.php';


/**
 * Helperis: atgriež darbu sarakstu (ar klienta vārdu un meistara vārdu).
 * Ja $masterId > 0, filtrē tikai tos darbus, kas piešķirti konkrētam meistaram.
 */
function jobs_fetch_all(int $masterId = 0): array
{
  // Pamata vaicājums: jobs + clients + masters (masters var nebūt -> LEFT JOIN)
  $sql = "
    SELECT j.*, c.name AS client_name, m.name AS master_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    LEFT JOIN masters m ON m.id = j.master_id
  ";

  $params = [];

  // Ja izvēlēts konkrēts meistars, pievienojam WHERE
  if ($masterId > 0) {
    $sql .= " WHERE j.master_id = :mid";
    $params[':mid'] = $masterId;
  }

  // Kārtošana: jaunākie augšā
  $sql .= " ORDER BY j.id DESC";

  // Prepared statement drošībai
  $stmt = db()->prepare($sql);
  $stmt->execute($params);

  return $stmt->fetchAll();
}


/**
 * GET /jobs
 * Parāda darbu sarakstu + formu jauna darba pievienošanai.
 * Iekļauj meistaru dropdown un filtrēšanu pēc meistara (?master_id=...).
 */
function jobs_index(): void
{
  // Klienti dropdownam
  $clients = db()->query(
    "SELECT id, name FROM clients ORDER BY name ASC"
  )->fetchAll();

  // Meistari dropdownam un filtram
  $masters = db()->query(
    "SELECT id, name FROM masters ORDER BY name ASC"
  )->fetchAll();

  // Filtrs pēc meistara (no query string). 0 = visi
  $masterId = (int)($_GET['master_id'] ?? 0);

  // Darbu saraksts (ar filtru)
  $jobs = jobs_fetch_all($masterId);

  // Renderē darbu skatu
  render('jobs.php', [
    'jobs' => $jobs,
    'clients' => $clients,
    'masters' => $masters,
    'masterId' => $masterId,
    'error' => null
  ], 'Darbi');
}


/**
 * POST /jobs
 * Izveido jaunu darbu (ar iespēju piešķirt meistaru).
 */
function jobs_store(): void
{
  // Atļautie statusi (whitelist)
  $validStatuses = ['new', 'in_progress', 'done', 'canceled'];

  // Ievades datu paņemšana + normalizācija
  $clientId    = (int)($_POST['client_id'] ?? 0);
  $masterId    = (int)($_POST['master_id'] ?? 0); // var būt 0/empty
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $status      = trim($_POST['status'] ?? 'new');
  $priceRaw    = trim($_POST['price'] ?? '');
  $scheduledAt = trim($_POST['scheduled_at'] ?? '');

  // 0 vai negatīvs -> NULL (nav piešķirts)
  if ($masterId <= 0) {
    $masterId = null;
  }

  // Saraksti vajadzīgi kļūdu gadījumā (lai view nekrīt)
  $clients = db()->query(
    "SELECT id, name FROM clients ORDER BY name ASC"
  )->fetchAll();

  $mastersList = db()->query(
    "SELECT id, name FROM masters ORDER BY name ASC"
  )->fetchAll();

  // -----------------------------------------
  // scheduled_at pārveidošana no datetime-local uz DB formātu
  // -----------------------------------------
  $scheduledAtDb = null;

  if ($scheduledAt !== '') {
    // no input nāk: YYYY-MM-DDTHH:MM
    $normalized = str_replace('T', ' ', $scheduledAt);

    $dt = DateTime::createFromFormat('Y-m-d H:i', $normalized);

    // Ja parsēšana neizdevās -> kļūda
    if (!$dt) {
      render('jobs.php', [
        'jobs' => jobs_fetch_all(),
        'clients' => $clients,
        'masters' => $mastersList,
        'masterId' => 0,
        'error' => 'Datums/laiks nav korekts.'
      ], 'Darbi');
      return;
    }

    // DB formāts: YYYY-MM-DD HH:MM:SS
    $scheduledAtDb = $dt->format('Y-m-d H:i:s');
  }

  // -----------------------------------------
  // Client ID whitelist (aizsardzība pret neeksistējošu klientu)
  // -----------------------------------------
  $clientIds = array_map(fn($c) => (int)$c['id'], $clients);

  if ($clientId <= 0 || !in_array($clientId, $clientIds, true)) {
    render('jobs.php', [
      'jobs' => jobs_fetch_all(),
      'clients' => $clients,
      'masters' => $mastersList,
      'masterId' => 0,
      'error' => 'Izvēlies korektu klientu.'
    ], 'Darbi');
    return;
  }

  // -----------------------------------------
  // Master ID whitelist (ja master_id nav NULL, pārbaudām ka eksistē)
  // -----------------------------------------
  $masterIds = array_map(fn($m) => (int)$m['id'], $mastersList);

  if ($masterId !== null && !in_array((int)$masterId, $masterIds, true)) {
    render('jobs.php', [
      'jobs' => jobs_fetch_all(),
      'clients' => $clients,
      'masters' => $mastersList,
      'masterId' => 0,
      'error' => 'Izvēlies korektu meistaru.'
    ], 'Darbi');
    return;
  }

  // -----------------------------------------
  // Validācija: virsraksts obligāts
  // -----------------------------------------
  if ($title === '') {
    render('jobs.php', [
      'jobs' => jobs_fetch_all(),
      'clients' => $clients,
      'masters' => $mastersList,
      'masterId' => 0,
      'error' => 'Virsraksts ir obligāts.'
    ], 'Darbi');
    return;
  }

  // -----------------------------------------
  // Statusa drošība (whitelist)
  // -----------------------------------------
  if (!in_array($status, $validStatuses, true)) {
    $status = 'new';
  }

  // -----------------------------------------
  // Cenas apstrāde
  // -----------------------------------------
  $price = null;

  if ($priceRaw !== '') {
    if (!is_numeric($priceRaw)) {
      render('jobs.php', [
        'jobs' => jobs_fetch_all(),
        'clients' => $clients,
        'masters' => $mastersList,
        'masterId' => 0,
        'error' => 'Cena nav korekta.'
      ], 'Darbi');
      return;
    }
    $price = (float)$priceRaw;
  }

  // -----------------------------------------
  // Saglabāšana DB (prepared statement)
  // Pievienojam master_id (var būt NULL)
  // -----------------------------------------
  $stmt = db()->prepare("
    INSERT INTO jobs (client_id, master_id, title, description, status, price, scheduled_at)
    VALUES (:client_id, :master_id, :title, :description, :status, :price, :scheduled_at)
  ");

  $stmt->execute([
    ':client_id' => $clientId,
    ':master_id' => $masterId, // NULL vai ID
    ':title' => $title,
    ':description' => ($description === '' ? null : $description),
    ':status' => $status,
    ':price' => $price,
    ':scheduled_at' => $scheduledAtDb,
  ]);

  // PRG pattern: pēc POST → redirect uz GET
  redirect(url('/jobs'));
}


/**
 * GET /job?id=...
 * Parāda viena darba kartīti (ar klientu un meistaru).
 */
function job_show(): void
{
  // ID no query string
  $id = (int)($_GET['id'] ?? 0);

  // Darbs ar klienta vārdu + meistara vārdu
  $stmt = db()->prepare("
  SELECT j.*, c.name AS client_name, m.name AS master_name
  FROM jobs j
  JOIN clients c ON c.id = j.client_id
  LEFT JOIN masters m ON m.id = j.master_id
  WHERE j.id = :id
  LIMIT 1
");

  $stmt->execute([':id' => $id]);

  $job = $stmt->fetch() ?: null;
  $masters = db()->query("SELECT id, name FROM masters ORDER BY name ASC")->fetchAll();


  // Renderē kartīti
  render('job_show.php', [
    'job' => $job,
    'masters' => $masters,
    'error' => null
  ], 'Darba kartīte');
}


/**
 * POST /job
 * Atjaunina darba statusu
 */
function job_update_status(): void
{
  // Atļautie statusi
  $validStatuses = ['new', 'in_progress', 'done', 'canceled'];

  // Datu paņemšana
  $id = (int)($_POST['id'] ?? 0);
  $status = trim($_POST['status'] ?? 'new');

  // Statusa validācija
  if (!in_array($status, $validStatuses, true)) {
    $status = 'new';
  }

  // Statusa atjaunināšana
  $stmt = db()->prepare(
    "UPDATE jobs SET status = :status WHERE id = :id"
  );
  $stmt->execute([
    ':status' => $status,
    ':id' => $id
  ]);

  // Atpakaļ uz darbu sarakstu
  // (Ja tu vēlāk gribi saglabāt filtru, var izmantot HTTP_REFERER)
  redirect(url('/jobs'));
}

/**
 * POST /job-assign
 * Piešķir (vai noņem) meistaru konkrētam darbam
 */
function job_assign_master(): void
{
  $id = (int)($_POST['id'] ?? 0);

  // no formas nāk master_id (0 = nav piešķirts)
  $masterId = (int)($_POST['master_id'] ?? 0);
  if ($masterId <= 0) {
    $masterId = null;
  }

  // Meistaru whitelist (aizsardzība)
  $masters = db()->query("SELECT id FROM masters")->fetchAll();
  $masterIds = array_map(fn($m) => (int)$m['id'], $masters);

  // Ja masterId nav null, tam jābūt eksistējošam ID
  if ($masterId !== null && !in_array($masterId, $masterIds, true)) {
    redirect(url('/job?id=' . $id));
  }

  // Update
  $stmt = db()->prepare("UPDATE jobs SET master_id = :mid WHERE id = :id");
  $stmt->execute([
    ':mid' => $masterId,
    ':id' => $id
  ]);

  redirect(url('/job?id=' . $id));
}
