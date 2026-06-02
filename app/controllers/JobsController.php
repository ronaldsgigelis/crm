<?php
// DB savienojums (PDO)
require_once __DIR__ . '/../db.php';
// Palīgfunkcijas: render(), redirect(), url(), h()
require_once __DIR__ . '/../helpers.php';


/**
 * Helperis: atgriež darbu sarakstu ar filtriem.
 *
 * Iespējamie filtri:
 * - master_id        -> konkrēts meistars
 * - status           -> konkrēts statuss
 * - client_id        -> konkrēts klients
 * - without_master   -> tikai darbi bez piešķirta meistara
 */
function jobs_fetch_all(
  int $masterId = 0,
  string $status = '',
  int $clientId = 0,
  bool $withoutMaster = false
): array {
  // Pamata vaicājums: jobs + clients + masters + job rēķins
  $sql = "
    SELECT j.*, c.name AS client_name, m.name AS master_name,
           i.id AS invoice_id, i.invoice_number AS invoice_number,
           i.due_date AS invoice_due_date,
           i.payment_status AS invoice_payment_status,
           i.paid_at AS invoice_paid_at
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    LEFT JOIN masters m ON m.id = j.master_id
    LEFT JOIN invoices i ON i.job_id = j.id
  ";

  $params = [];
  $where = [];

  // -----------------------------------------
  // Filtrs: tikai bez meistara
  // -----------------------------------------
  if ($withoutMaster) {
    $where[] = "j.master_id IS NULL";
  }

  // -----------------------------------------
  // Filtrs: konkrēts meistars
  // Ja ieslēgts "bez meistara", šo ignorējam
  // -----------------------------------------
  if (!$withoutMaster && $masterId > 0) {
    $where[] = "j.master_id = :mid";
    $params[':mid'] = $masterId;
  }

  // -----------------------------------------
  // Filtrs: statuss
  // -----------------------------------------
  if ($status !== '') {
    $where[] = "j.status = :status";
    $params[':status'] = $status;
  }

  // -----------------------------------------
  // Filtrs: klients
  // -----------------------------------------
  if ($clientId > 0) {
    $where[] = "j.client_id = :client_id";
    $params[':client_id'] = $clientId;
  }

  // Ja ir filtri, saliekam WHERE daļu
  if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
  }

  // Kārtošana: jaunākie augšā
  $sql .= " ORDER BY j.id DESC";

  // Prepared statement drošībai
  $stmt = db()->prepare($sql);
  $stmt->execute($params);

  return $stmt->fetchAll();
}

/**
 * Helperis: paņem vienu darbu pēc ID.
 * Pievienojam klienta vārdu un meistara vārdu.
 */
function job_fetch_one(int $id): ?array
{
  $stmt = db()->prepare("
    SELECT j.*, c.name AS client_name, m.name AS master_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    LEFT JOIN masters m ON m.id = j.master_id
    WHERE j.id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $id]);

  return $stmt->fetch() ?: null;
}


/**
 * GET /jobs
 * Parāda darbu sarakstu + formu jauna darba pievienošanai.
 * Iekļauj filtrus:
 * - meistars
 * - statuss
 * - klients
 * - tikai bez meistara
 */
function jobs_index(): void
{
  // Klienti dropdowniem
  $clients = db()->query(
    "SELECT id, name FROM clients ORDER BY name ASC"
  )->fetchAll();

  // Meistari dropdowniem
  $masters = db()->query(
    "SELECT id, name FROM masters ORDER BY name ASC"
  )->fetchAll();

  // Atļautie statusi
  $validStatuses = ['new', 'in_progress', 'done', 'canceled'];

  // -----------------------------------------
  // Filtru paņemšana no query string
  // -----------------------------------------
  $masterId = (int)($_GET['master_id'] ?? 0);
  $status = trim($_GET['status'] ?? '');
  $clientId = (int)($_GET['client_id'] ?? 0);
  $withoutMaster = (int)($_GET['without_master'] ?? 0) === 1;

  // -----------------------------------------
  // Whitelist priekš statusa
  // -----------------------------------------
  if ($status !== '' && !in_array($status, $validStatuses, true)) {
    $status = '';
  }

  // -----------------------------------------
  // Whitelist priekš klienta
  // -----------------------------------------
  $clientIds = array_map(fn($c) => (int)$c['id'], $clients);
  if ($clientId > 0 && !in_array($clientId, $clientIds, true)) {
    $clientId = 0;
  }

  // -----------------------------------------
  // Whitelist priekš meistara
  // -----------------------------------------
  $masterIds = array_map(fn($m) => (int)$m['id'], $masters);
  if ($masterId > 0 && !in_array($masterId, $masterIds, true)) {
    $masterId = 0;
  }

  // Darbu saraksts ar filtriem
  $jobs = jobs_fetch_all($masterId, $status, $clientId, $withoutMaster);

  // Renderē darbu skatu
  render('jobs.php', [
    'jobs' => $jobs,
    'clients' => $clients,
    'masters' => $masters,
    'masterId' => $masterId,
    'statusFilter' => $status,
    'clientId' => $clientId,
    'withoutMaster' => $withoutMaster,
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
        'statusFilter' => '',
        'clientId' => 0,
        'withoutMaster' => false,
        'error' => 'Datums/laiks nav korekts.'
      ], 'Darbi');
      return;
    }

    // DB formāts: YYYY-MM-DD HH:MM:SS
    $scheduledAtDb = $dt->format('Y-m-d H:i:s');
  }

  // -----------------------------------------
  // Client ID whitelist
  // -----------------------------------------
  $clientIds = array_map(fn($c) => (int)$c['id'], $clients);

  if ($clientId <= 0 || !in_array($clientId, $clientIds, true)) {
    render('jobs.php', [
      'jobs' => jobs_fetch_all(),
      'clients' => $clients,
      'masters' => $mastersList,
      'masterId' => 0,
      'statusFilter' => '',
      'clientId' => 0,
      'withoutMaster' => false,
      'error' => 'Izvēlies korektu klientu.'
    ], 'Darbi');
    return;
  }

  // -----------------------------------------
  // Master ID whitelist
  // -----------------------------------------
  $masterIds = array_map(fn($m) => (int)$m['id'], $mastersList);

  if ($masterId !== null && !in_array((int)$masterId, $masterIds, true)) {
    render('jobs.php', [
      'jobs' => jobs_fetch_all(),
      'clients' => $clients,
      'masters' => $mastersList,
      'masterId' => 0,
      'statusFilter' => '',
      'clientId' => 0,
      'withoutMaster' => false,
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
      'statusFilter' => '',
      'clientId' => 0,
      'withoutMaster' => false,
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
        'statusFilter' => '',
        'clientId' => 0,
        'withoutMaster' => false,
        'error' => 'Cena nav korekta.'
      ], 'Darbi');
      return;
    }

    $price = (float)$priceRaw;

    // Cena nedrīkst būt negatīva
    if ($price < 0) {
      render('jobs.php', [
        'jobs' => jobs_fetch_all(),
        'clients' => $clients,
        'masters' => $mastersList,
        'masterId' => 0,
        'statusFilter' => '',
        'clientId' => 0,
        'withoutMaster' => false,
        'error' => 'Cena nedrīkst būt negatīva.'
      ], 'Darbi');
      return;
    }
  }

  // -----------------------------------------
  // Saglabāšana DB
  // -----------------------------------------
  $stmt = db()->prepare("
    INSERT INTO jobs (client_id, master_id, title, description, status, price, scheduled_at)
    VALUES (:client_id, :master_id, :title, :description, :status, :price, :scheduled_at)
  ");

  $stmt->execute([
    ':client_id' => $clientId,
    ':master_id' => $masterId,
    ':title' => $title,
    ':description' => ($description === '' ? null : $description),
    ':status' => $status,
    ':price' => $price,
    ':scheduled_at' => $scheduledAtDb,
  ]);

  // PRG pattern: pēc POST -> redirect uz GET
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
  $job = job_fetch_one($id);

  // Meistaru saraksts dropdown laukiem
  $masters = db()->query("SELECT id, name FROM masters ORDER BY name ASC")->fetchAll();

  $invoice = null;
  if ($job) {
    $stmt = db()->prepare("
      SELECT id, invoice_number
      FROM invoices
      WHERE job_id = :job_id
      LIMIT 1
    ");
    $stmt->execute([':job_id' => (int)$job['id']]);
    $invoice = $stmt->fetch() ?: null;
  }

  // Renderē kartīti
  render('job_show.php', [
    'job' => $job,
    'invoice' => $invoice,
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

  // Pēc statusa maiņas ejam atpakaļ uz konkrēto darba kartīti
  redirect(url('/job?id=' . $id));
}

/**
 * POST /job-update
 * Pilna darba rediģēšana:
 * - virsraksts
 * - apraksts
 * - statuss
 * - cena
 * - plānotais datums/laiks
 * - meistars
 */
function job_update(): void
{
  // Atļautie statusi
  $validStatuses = ['new', 'in_progress', 'done', 'canceled'];

  // Ievades datu paņemšana
  $id          = (int)($_POST['id'] ?? 0);
  $masterId    = (int)($_POST['master_id'] ?? 0);
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $status      = trim($_POST['status'] ?? 'new');
  $priceRaw    = trim($_POST['price'] ?? '');
  $scheduledAt = trim($_POST['scheduled_at'] ?? '');

  // Ja meistars nav izvēlēts, saglabājam NULL
  if ($masterId <= 0) {
    $masterId = null;
  }

  // Paņemam esošo darbu
  $job = job_fetch_one($id);

  // Meistaru saraksts dropdown laukam
  $masters = db()->query("SELECT id, name FROM masters ORDER BY name ASC")->fetchAll();
  $masterIds = array_map(fn($m) => (int)$m['id'], $masters);

  // Ja darbs neeksistē, atgriežam kļūdu tajā pašā view
  if (!$job) {
    render('job_show.php', [
      'job' => null,
      'masters' => $masters,
      'error' => 'Darbs nav atrasts.'
    ], 'Darba kartīte');
    return;
  }

  // -----------------------------------------
  // Validācija: virsraksts obligāts
  // -----------------------------------------
  if ($title === '') {
    $job['title'] = $title;
    $job['description'] = $description;
    $job['status'] = $status;
    $job['price'] = $priceRaw;
    $job['scheduled_at'] = $scheduledAt;
    $job['master_id'] = $masterId;

    render('job_show.php', [
      'job' => $job,
      'masters' => $masters,
      'error' => 'Virsraksts ir obligāts.'
    ], 'Darba kartīte');
    return;
  }

  // -----------------------------------------
  // Statusa whitelist
  // -----------------------------------------
  if (!in_array($status, $validStatuses, true)) {
    $status = 'new';
  }

  // -----------------------------------------
  // Meistara whitelist
  // -----------------------------------------
  if ($masterId !== null && !in_array((int)$masterId, $masterIds, true)) {
    render('job_show.php', [
      'job' => $job,
      'masters' => $masters,
      'error' => 'Izvēlies korektu meistaru.'
    ], 'Darba kartīte');
    return;
  }

  // -----------------------------------------
  // Cenas apstrāde
  // -----------------------------------------
  $price = null;

  if ($priceRaw !== '') {
    if (!is_numeric($priceRaw)) {
      $job['title'] = $title;
      $job['description'] = $description;
      $job['status'] = $status;
      $job['price'] = $priceRaw;
      $job['scheduled_at'] = $scheduledAt;
      $job['master_id'] = $masterId;

      render('job_show.php', [
        'job' => $job,
        'masters' => $masters,
        'error' => 'Cena nav korekta.'
      ], 'Darba kartīte');
      return;
    }

    $price = (float)$priceRaw;

    if ($price < 0) {
      $job['title'] = $title;
      $job['description'] = $description;
      $job['status'] = $status;
      $job['price'] = $priceRaw;
      $job['scheduled_at'] = $scheduledAt;
      $job['master_id'] = $masterId;

      render('job_show.php', [
        'job' => $job,
        'masters' => $masters,
        'error' => 'Cena nedrīkst būt negatīva.'
      ], 'Darba kartīte');
      return;
    }
  }

  // -----------------------------------------
  // scheduled_at pārveidošana no datetime-local uz DB formātu
  // -----------------------------------------
  $scheduledAtDb = null;

  if ($scheduledAt !== '') {
    $normalized = str_replace('T', ' ', $scheduledAt);

    $dt = DateTime::createFromFormat('Y-m-d H:i', $normalized);

    if (!$dt) {
      $dt = DateTime::createFromFormat('Y-m-d H:i:s', $normalized);
    }

    if (!$dt) {
      $job['title'] = $title;
      $job['description'] = $description;
      $job['status'] = $status;
      $job['price'] = $priceRaw;
      $job['scheduled_at'] = $scheduledAt;
      $job['master_id'] = $masterId;

      render('job_show.php', [
        'job' => $job,
        'masters' => $masters,
        'error' => 'Datums/laiks nav korekts.'
      ], 'Darba kartīte');
      return;
    }

    $scheduledAtDb = $dt->format('Y-m-d H:i:s');
  }

  // -----------------------------------------
  // Update DB
  // -----------------------------------------
  $stmt = db()->prepare("
    UPDATE jobs
    SET
      master_id = :master_id,
      title = :title,
      description = :description,
      status = :status,
      price = :price,
      scheduled_at = :scheduled_at
    WHERE id = :id
  ");

  $stmt->execute([
    ':master_id' => $masterId,
    ':title' => $title,
    ':description' => ($description === '' ? null : $description),
    ':status' => $status,
    ':price' => $price,
    ':scheduled_at' => $scheduledAtDb,
    ':id' => $id
  ]);

  // Atpakaļ uz konkrēto darba kartīti
  redirect(url('/job?id=' . $id));
}

/**
 * POST /job-assign
 * Piešķir vai noņem meistaru konkrētam darbam
 */
function job_assign_master(): void
{
  $id = (int)($_POST['id'] ?? 0);

  // no formas nāk master_id (0 = nav piešķirts)
  $masterId = (int)($_POST['master_id'] ?? 0);
  if ($masterId <= 0) {
    $masterId = null;
  }

  // Meistaru whitelist
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
