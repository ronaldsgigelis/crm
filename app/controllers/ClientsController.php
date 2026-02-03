<?php

//Kontrolieris izmanto DB slāni (db()), renderēšanu (render) un URL ģenerēšanu (url)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../routes.php';

/*
paņem visus klientus no DB (SELECT * FROM clients)
sakārto pēdējos augšā (ORDER BY id DESC)
nodod datus uz view views/clients.php caur render()
*/
function clients_index(): void
{
  $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
  render('clients.php', ['clients' => $clients, 'error' => null], 'Klienti');
}

//Ievadi normalizēju ar trim un droši paņemu ar null-coalescing
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

  //Validācija
  /*
    atkal paņem sarakstu (lai lapa var parādīt datus)
    renderē to pašu view ar kļūdas ziņu error
    return pārtrauc izpildi
  */

  //izlaboju bagu epastam
  if ($name === '') {
    $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
    render('clients.php', ['clients' => $clients, 'error' => 'Vārds ir obligāts.'], 'Klienti');
    return;
  }




  // Phone validācija: atļauj +, ciparus, atstarpes, iekavas, domuzīmes
  if ($phone !== '') {

    // 1) Atļautie simboli
    if (!preg_match('/^[0-9+\s()-]+$/', $phone)) {
      $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
      render('clients.php', ['clients' => $clients, 'error' => 'Tālrunī drīkst būt tikai cipari, +, atstarpes, iekavas un domuzīmes.'], 'Klienti');
      return;
    }

    // 2) Plus zīme tikai sākumā (ja vispār ir)
    if (substr_count($phone, '+') > 1 || (strpos($phone, '+') !== false && strpos($phone, '+') !== 0)) {
      $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
      render('clients.php', ['clients' => $clients, 'error' => 'Plus zīme drīkst būt tikai sākumā.'], 'Klienti');
      return;
    }

    // 3) Ciparu skaits (izmetam visu, kas nav cipars)
    $digits = preg_replace('/\D+/', '', $phone);

    // ļaujam starptautiski: 5..25 cipari
    if (strlen($digits) < 5 || strlen($digits) > 25) {
      $clients = db()->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
      render('clients.php', ['clients' => $clients, 'error' => 'Tālruņa numurs ir pārāk īss vai pārāk garš.'], 'Klienti');
      return;
    }
  }





  //Insertu taisu ar prepared statement, lai būtu droši un korekti
  //prepare + execute pasargā no SQL injection
  //tukšas vērtības pārvērš uz NULL (ja DB kolonnas atļauj NULL)
  $stmt = db()->prepare("INSERT INTO clients (name, phone, email) VALUES (:name, :phone, :email)");
  $stmt->execute([
    ':name' => $name,
    ':phone' => ($phone === '' ? null : $phone),
    ':email' => ($email === '' ? null : $email),
  ]);

  redirect(url('/clients'));
}
