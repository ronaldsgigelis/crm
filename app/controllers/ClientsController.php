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

/*
Paņem visus klientus no DB (SELECT * FROM clients)
sakārto pēdējos augšā (ORDER BY id DESC)
nodod datus uz view views/clients.php caur render()
*/
function clients_index(): void
{
  $clients = clients_fetch_all();
  render('clients.php', ['clients' => $clients, 'error' => null], 'Klienti');
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

  // Validācija
  /*
    atkal paņem sarakstu (lai lapa var parādīt datus)
    renderē to pašu view ar kļūdas ziņu error
    return pārtrauc izpildi
  */

  // Vārds ir obligāts
  if ($name === '') {
    render('clients.php', [
      'clients' => clients_fetch_all(),
      'error' => 'Vārds ir obligāts.'
    ], 'Klienti');
    return;
  }

  // Phone validācija: atļauj +, ciparus, atstarpes, iekavas, domuzīmes
  if ($phone !== '') {

    // 1) Atļautie simboli
    if (!preg_match('/^[0-9+\s()-]+$/', $phone)) {
      render('clients.php', [
        'clients' => clients_fetch_all(),
        'error' => 'Tālrunī drīkst būt tikai cipari, +, atstarpes, iekavas un domuzīmes.'
      ], 'Klienti');
      return;
    }

    // 2) Plus zīme tikai sākumā (ja vispār ir)
    if (substr_count($phone, '+') > 1 || (strpos($phone, '+') !== false && strpos($phone, '+') !== 0)) {
      render('clients.php', [
        'clients' => clients_fetch_all(),
        'error' => 'Plus zīme drīkst būt tikai sākumā.'
      ], 'Klienti');
      return;
    }

    // 3) Ciparu skaits (izmetam visu, kas nav cipars)
    $digits = preg_replace('/\D+/', '', $phone);

    // ļaujam starptautiski: 5..25 cipari
    if (strlen($digits) < 5 || strlen($digits) > 25) {
      render('clients.php', [
        'clients' => clients_fetch_all(),
        'error' => 'Tālruņa numurs ir pārāk īss vai pārāk garš.'
      ], 'Klienti');
      return;
    }
  }

  // E-pasta validācija
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render('clients.php', [
      'clients' => clients_fetch_all(),
      'error' => 'E-pasts nav korekts.'
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
  // id no query string
  $id = (int)($_GET['id'] ?? 0);

  // Ja id nav korekts, rādam "nav atrasts"
  if ($id <= 0) {
    render('client_show.php', [
      'client' => null,
      'jobs' => [],
      'jobsCount' => 0,
      'totalAmount' => 0
    ], 'Klienta kartīte');
    return;
  }

  // Atrodam klientu pēc id
  $stmt = db()->prepare("
    SELECT *
    FROM clients
    WHERE id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $id]);
  $client = $stmt->fetch();

  // Ja klienta nav, rādam "nav atrasts"
  if (!$client) {
    render('client_show.php', [
      'client' => null,
      'jobs' => [],
      'jobsCount' => 0,
      'totalAmount' => 0
    ], 'Klienta kartīte');
    return;
  }

  // Paņemam visus šī klienta darbus
  // Pievienojam meistara vārdu, ja ir piešķirts
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

  // Darbu skaits
  $jobsCount = count($jobs);

  // Kopējā summa
  // Saskaitām tikai tās cenas, kas nav NULL
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
    'totalAmount' => $totalAmount
  ], 'Klienta kartīte');
}
