<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../routes.php';


function clients_index(): void {
  $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
  render('clients.php', ['clients' => $clients, 'error' => null], 'Klienti');
}

function clients_store(): void {
  $name  = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($name === '') {
    $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
    render('clients.php', ['clients' => $clients, 'error' => 'Vārds ir obligāts.'], 'Klienti');
    return;
  }

  $stmt = db()->prepare("INSERT INTO clients (name, phone, email) VALUES (:name, :phone, :email)");
  $stmt->execute([
    ':name' => $name,
    ':phone' => ($phone === '' ? null : $phone),
    ':email' => ($email === '' ? null : $email),
  ]);

  redirect(url('/clients'));

}
