<?php

/** @var string $title */ ?>
<?php /** @var string $content */ ?>
<!-- Layout fails: kopējais HTML karkass visām lapām -->

<!doctype html>
<html lang="lv">

<head>
  <!-- Simbolu kodējums -->
  <meta charset="utf-8">

  <!-- Responsīvs dizains -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Lapas virsraksts (droši ar h()) -->
  <title><?= h($title) ?></title>

  <!-- Globālais CRM interfeisa CSS -->
  <link rel="stylesheet" href="<?= url('/assets/app.css') ?>">
</head>

<body>
  <div class="app-shell">
    <!-- Galvenā navigācija -->
    <nav class="top-nav" aria-label="Galvenā navigācija">
      <a class="nav-link <?= path() === '/' ? 'nav-link-active' : '' ?>" href="<?= url('/') ?>">Sākums</a>
      <a class="nav-link <?= path() === '/clients' ? 'nav-link-active' : '' ?>" href="<?= url('/clients') ?>">Klienti</a>
      <a class="nav-link <?= path() === '/jobs' ? 'nav-link-active' : '' ?>" href="<?= url('/jobs') ?>">Darbi</a>
      <a class="nav-link <?= path() === '/masters' ? 'nav-link-active' : '' ?>" href="<?= url('/masters') ?>">Meistari</a>
      <a class="nav-link <?= path() === '/invoices' ? 'nav-link-active' : '' ?>" href="<?= url('/invoices') ?>">Rēķini</a>
      <a class="nav-link <?= path() === '/invoice-settings' ? 'nav-link-active' : '' ?>" href="<?= url('/invoice-settings') ?>">Rēķinu iestatījumi</a>
      <a class="nav-link <?= path() === '/manual-invoice' ? 'nav-link-active' : '' ?>" href="<?= url('/manual-invoice') ?>">Rēķinu ģenerators</a>
    </nav>

    <main class="page">
      <header class="page-header">
        <!-- Lapas virsraksts -->
        <h1><?= h($title) ?></h1>
      </header>

      <div class="content-stack">
        <!-- Dinamiskais saturs no konkrētā view -->
        <?= $content ?>
      </div>
    </main>
  </div>

</body>

</html>
