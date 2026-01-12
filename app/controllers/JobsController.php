<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

function jobs_index(): void {
  $clients = db()->query("SELECT id, name FROM clients ORDER BY name ASC")->fetchAll();

  $jobs = db()->query("
    SELECT j.*, c.name AS client_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    ORDER BY j.id DESC
  ")->fetchAll();

  render('jobs.php', ['jobs' => $jobs, 'clients' => $clients, 'error' => null], 'Darbi');
}

function jobs_store(): void {
  $validStatuses = ['new','in_progress','done','canceled'];

  $clientId    = (int)($_POST['client_id'] ?? 0);
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $status      = trim($_POST['status'] ?? 'new');
  $priceRaw    = trim($_POST['price'] ?? '');
  $scheduledAt = trim($_POST['scheduled_at'] ?? '');

  $clients = db()->query("SELECT id, name FROM clients ORDER BY name ASC")->fetchAll();

  if ($clientId <= 0) {
    $jobs = [];
    render('jobs.php', ['jobs' => $jobs, 'clients' => $clients, 'error' => 'Izvēlies klientu.'], 'Darbi');
    return;
  }
  if ($title === '') {
    $jobs = [];
    render('jobs.php', ['jobs' => $jobs, 'clients' => $clients, 'error' => 'Virsraksts ir obligāts.'], 'Darbi');
    return;
  }
  if (!in_array($status, $validStatuses, true)) $status = 'new';

  $price = null;
  if ($priceRaw !== '') {
    if (!is_numeric($priceRaw)) {
      $jobs = [];
      render('jobs.php', ['jobs' => $jobs, 'clients' => $clients, 'error' => 'Cena nav korekta.'], 'Darbi');
      return;
    }
    $price = (float)$priceRaw;
  }

  $stmt = db()->prepare("
    INSERT INTO jobs (client_id, title, description, status, price, scheduled_at)
    VALUES (:client_id, :title, :description, :status, :price, :scheduled_at)
  ");
  $stmt->execute([
    ':client_id' => $clientId,
    ':title' => $title,
    ':description' => ($description === '' ? null : $description),
    ':status' => $status,
    ':price' => $price,
    ':scheduled_at' => ($scheduledAt === '' ? null : $scheduledAt),
  ]);

  redirect(url('/jobs'));
}

function job_show(): void {
  $id = (int)($_GET['id'] ?? 0);

  $stmt = db()->prepare("
    SELECT j.*, c.name AS client_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    WHERE j.id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $id]);
  $job = $stmt->fetch() ?: null;

  render('job_show.php', ['job' => $job, 'error' => null], 'Darba kartīte');
}

function job_update_status(): void {
  $validStatuses = ['new','in_progress','done','canceled'];

  $id = (int)($_POST['id'] ?? 0);
  $status = trim($_POST['status'] ?? 'new');
  if (!in_array($status, $validStatuses, true)) $status = 'new';

  $stmt = db()->prepare("UPDATE jobs SET status = :status WHERE id = :id");
  $stmt->execute([':status' => $status, ':id' => $id]);

  redirect(url('/jobs'));

}
