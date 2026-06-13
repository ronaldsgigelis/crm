<?php

// Kontrolieris izmanto DB slāni (db()), renderēšanu (render) un URL ģenerēšanu (url)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../routes.php';

/*
Paņem visus klientus no DB
sakārto pēdējos augšā (ORDER BY id DESC)
šo helperi lietojam, lai nedublētu vienu un to pašu SELECT
*/
function clients_fetch_all(): array
{
  return db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
}

function client_find(int $id): ?array
{
  $stmt = db()->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);
  $client = $stmt->fetch();

  return $client ?: null;
}

function client_input_error(string $name, string $phone, string $email): ?string
{
  if ($name === '') {
    return 'Vārds ir obligāts.';
  }

  if ($phone !== '') {
    if (!preg_match('/^[0-9+\s()-]+$/', $phone)) {
      return 'Tālrunī drīkst būt tikai cipari, +, atstarpes, iekavas un domuzīmes.';
    }

    if (substr_count($phone, '+') > 1 || (strpos($phone, '+') !== false && strpos($phone, '+') !== 0)) {
      return 'Plus zīme drīkst būt tikai sākumā.';
    }

    $digits = preg_replace('/\D+/', '', $phone);
    if (strlen($digits) < 5 || strlen($digits) > 25) {
      return 'Tālruņa numurs ir pārāk īss vai pārāk garš.';
    }
  }

  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return 'E-pasts nav korekts.';
  }

  return null;
}

/*
Paņem visus klientus no DB (SELECT * FROM clients)
sakārto pēdējos augšā (ORDER BY id DESC)
nodod datus uz view views/clients.php caur render()
*/
function clients_index(): void
{
  $clients = clients_fetch_all();
  $notice = isset($_GET['deleted']) ? 'Klients izdzēsts.' : null;
  render('clients.php', ['clients' => $clients, 'error' => null, 'notice' => $notice], 'Klienti');
}

// Ievadi normalizēju ar trim un droši paņemu ar null-coalescing
/*
ņem datus no POST
trim() noņem liekas atstarpes
?? '' nodrošina, ka nav “Undefined index”
*/
function clients_store(): void
{
  $name  = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');

  $error = client_input_error($name, $phone, $email);
  if ($error !== null) {
    render('clients.php', [
      'clients' => clients_fetch_all(),
      'error' => $error,
      'notice' => null
    ], 'Klienti');
    return;
  }

  // Insertu taisu ar prepared statement, lai būtu droši un korekti
  // prepare + execute pasargā no SQL injection
  // tukšas vērtības pārvērš uz NULL (ja DB kolonnas atļauj NULL)
  $stmt = db()->prepare("
    INSERT INTO clients (name, phone, email)
    VALUES (:name, :phone, :email)
  ");

  $stmt->execute([
    ':name'  => $name,
    ':phone' => ($phone === '' ? null : $phone),
    ':email' => ($email === '' ? null : $email),
  ]);

  redirect(url('/clients'));
}

function client_update(): void
{
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($id <= 0 || !client_find($id)) {
    redirect(url('/clients'));
  }

  $error = client_input_error($name, $phone, $email);
  if ($error !== null) {
    $client = client_find($id);
    if ($client) {
      $client['name'] = $name;
      $client['phone'] = $phone;
      $client['email'] = $email;
    }
    client_render_detail($id, $error, null, $client);
    return;
  }

  $stmt = db()->prepare("
    UPDATE clients
    SET name = :name, phone = :phone, email = :email
    WHERE id = :id
  ");
  $stmt->execute([
    ':id' => $id,
    ':name' => $name,
    ':phone' => ($phone === '' ? null : $phone),
    ':email' => ($email === '' ? null : $email),
  ]);

  redirect(url('/client?id=' . $id . '&updated=1'));
}

function client_delete(): void
{
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) {
    redirect(url('/clients'));
  }

  $pdo = db();
  $pdo->beginTransaction();

  try {
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
      $pdo->rollBack();
      redirect(url('/clients'));
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE client_id = :id");
    $stmt->execute([':id' => $id]);
    if ((int)$stmt->fetchColumn() > 0) {
      $pdo->rollBack();
      redirect(url('/client?id=' . $id . '&delete_blocked=1'));
    }

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    throw $e;
  }

  redirect(url('/clients?deleted=1'));
}

/*
GET /client?id=...
Parāda viena klienta kartīti:
- klienta pamatdati
- visi klienta darbi
- darbu skaits
- kopējā darbu summa
*/
function client_show(): void
{
  $id = (int)($_GET['id'] ?? 0);
  $error = isset($_GET['delete_blocked'])
    ? 'Klientu nevar dzēst, jo tam ir piesaistīti darbi.'
    : null;
  $notice = isset($_GET['updated']) ? 'Klienta dati saglabāti.' : null;

  client_render_detail($id, $error, $notice);
}

function client_render_detail(int $id, ?string $error = null, ?string $notice = null, ?array $formClient = null): void
{
  $client = $id > 0 ? client_find($id) : null;

  if (!$client) {
    render('client_show.php', [
      'client' => null,
      'jobs' => [],
      'jobsCount' => 0,
      'totalAmount' => 0,
      'error' => $error,
      'notice' => $notice,
    ], 'Klienta kartīte');
    return;
  }

  if ($formClient !== null) {
    $client = array_merge($client, $formClient);
  }

  $stmt = db()->prepare("
    SELECT
      j.*,
      m.name AS master_name
    FROM jobs j
    LEFT JOIN masters m ON m.id = j.master_id
    WHERE j.client_id = :client_id
    ORDER BY
      (j.scheduled_at IS NULL) ASC,
      j.scheduled_at DESC,
      j.id DESC
  ");
  $stmt->execute([':client_id' => $id]);
  $jobs = $stmt->fetchAll();

  $jobsCount = count($jobs);
  $totalAmount = 0;
  foreach ($jobs as $job) {
    if ($job['price'] !== null && $job['price'] !== '') {
      $totalAmount += (float)$job['price'];
    }
  }

  render('client_show.php', [
    'client' => $client,
    'jobs' => $jobs,
    'jobsCount' => $jobsCount,
    'totalAmount' => $totalAmount,
    'error' => $error,
    'notice' => $notice,
  ], 'Klienta kartīte');
}
