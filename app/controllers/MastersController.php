<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

function masters_fetch_all(): array
{
    return db()->query("
    SELECT id, name, phone, email, token, created_at
    FROM masters
    ORDER BY name ASC
  ")->fetchAll();
}

/**
 * GET /masters
 */
function masters_index(): void
{
    $masters = masters_fetch_all();

    render('masters.php', [
        'masters' => $masters,
        'error' => null
    ], 'Meistari');
}

/**
 * POST /masters
 */
function masters_store(): void
{
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        render('masters.php', [
            'masters' => masters_fetch_all(),
            'error' => 'Vārds ir obligāts.'
        ], 'Meistari');
        return;
    }

    $token = bin2hex(random_bytes(32));

    $stmt = db()->prepare("
    INSERT INTO masters (name, phone, email, token)
    VALUES (:name, :phone, :email, :token)
  ");
    $stmt->execute([
        ':name' => $name,
        ':phone' => ($phone === '' ? null : $phone),
        ':email' => ($email === '' ? null : $email),
        ':token' => $token
    ]);

    redirect(url('/masters'));
}

/**
 * GET /my-jobs?token=...
 * Meistars redz savus darbus bez login.
 */
function my_jobs(): void
{
    $token = trim($_GET['token'] ?? '');

    if ($token === '') {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    $stmt = db()->prepare("SELECT id, name FROM masters WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $master = $stmt->fetch();

    if (!$master) {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    $stmt = db()->prepare("
    SELECT j.*, c.name AS client_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    WHERE j.master_id = :mid
    ORDER BY (j.scheduled_at IS NULL) ASC, j.scheduled_at ASC, j.id DESC
  ");
    $stmt->execute([':mid' => (int)$master['id']]);
    $jobs = $stmt->fetchAll();

    render('my_jobs.php', [
        'master' => $master,
        'jobs' => $jobs
    ], 'Mani darbi');
}
