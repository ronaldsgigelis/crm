<?php /** @var string $title */ ?>
<?php /** @var string $content */ ?>
<!doctype html>
<html lang="lv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    nav a { margin-right: 12px; }
    table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f4f4f4; text-align: left; }
    .card { border: 1px solid #ddd; padding: 12px; border-radius: 8px; margin-top: 12px; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    input, select, textarea { width: 100%; padding: 8px; }
    button { padding: 8px 12px; cursor: pointer; }
    .muted { color:#666; }
    .error { color:#b00020; }
  </style>
</head>
<body>
  <nav>
    <a href="<?= url('/') ?>">Sākums</a>
    <a href="<?= url('/clients') ?>">Klienti</a>
    <a href="<?= url('/jobs') ?>">Darbi</a>

  </nav>

  <h1><?= h($title) ?></h1>
  <?= $content ?>
</body>
</html>
