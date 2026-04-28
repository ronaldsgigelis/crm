<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../routes.php';

function invoice_settings_defaults(): array
{
  return [
    'seller_name' => '',
    'seller_registration_no' => '',
    'seller_vat_no' => '',
    'seller_address' => '',
    'seller_email' => '',
    'seller_phone' => '',
    'seller_bank_name' => '',
    'seller_iban' => '',
    'seller_swift' => '',
    'invoice_note' => '',
    'logo_path' => '',
  ];
}

function invoice_settings_fetch(): array
{
  $stmt = db()->query("SELECT * FROM invoice_settings WHERE id = 1 LIMIT 1");
  $settings = $stmt->fetch() ?: [];

  return array_merge(invoice_settings_defaults(), $settings);
}

function invoice_settings_index(?string $error = null): void
{
  render('invoice_settings.php', [
    'settings' => invoice_settings_fetch(),
    'error' => $error,
    'saved' => (int)($_GET['saved'] ?? 0) === 1,
  ], 'Rēķinu iestatījumi');
}

function invoice_payer_type_label(string $type): string
{
  return match ($type) {
    'private_person' => 'Privātpersona',
    'company' => 'Uzņēmums',
    'self_employed' => 'Pašnodarbinātais',
    'association' => 'Biedrība',
    'public_institution' => 'Publiska iestāde',
    'other' => 'Cits',
    default => $type,
  };
}

function invoice_handle_logo_upload(string $currentLogo): array
{
  if (empty($_FILES['logo']) || ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return ['ok' => true, 'path' => $currentLogo, 'error' => null];
  }

  $file = $_FILES['logo'];
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Logo augšupielāde neizdevās.'];
  }

  if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Logo fails ir pārāk liels. Maksimālais izmērs ir 2 MB.'];
  }

  $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
  $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  if (!in_array($extension, $allowedExtensions, true)) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Logo drīkst būt tikai PNG, JPG, JPEG vai WEBP fails.'];
  }

  $info = @getimagesize($file['tmp_name']);
  if ($info === false) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Logo fails nav derīgs attēls.'];
  }

  $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
  if (!in_array($info['mime'] ?? '', $allowedMimeTypes, true)) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Logo faila tips nav atļauts.'];
  }

  $uploadDir = __DIR__ . '/../../public/uploads/invoice';
  if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Neizdevās izveidot logo augšupielādes mapi.'];
  }

  $fileName = 'logo-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
  $target = $uploadDir . '/' . $fileName;

  if (!move_uploaded_file($file['tmp_name'], $target)) {
    return ['ok' => false, 'path' => $currentLogo, 'error' => 'Neizdevās saglabāt logo failu.'];
  }

  return ['ok' => true, 'path' => 'uploads/invoice/' . $fileName, 'error' => null];
}

function invoice_settings_store(): void
{
  $current = invoice_settings_fetch();
  $logo = invoice_handle_logo_upload($current['logo_path'] ?? '');
  if (!$logo['ok']) {
    render('invoice_settings.php', [
      'settings' => array_merge($current, $_POST),
      'error' => $logo['error'],
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }

  $data = [
    'seller_name' => trim($_POST['seller_name'] ?? ''),
    'seller_registration_no' => trim($_POST['seller_registration_no'] ?? ''),
    'seller_vat_no' => trim($_POST['seller_vat_no'] ?? ''),
    'seller_address' => trim($_POST['seller_address'] ?? ''),
    'seller_email' => trim($_POST['seller_email'] ?? ''),
    'seller_phone' => trim($_POST['seller_phone'] ?? ''),
    'seller_bank_name' => trim($_POST['seller_bank_name'] ?? ''),
    'seller_iban' => trim($_POST['seller_iban'] ?? ''),
    'seller_swift' => trim($_POST['seller_swift'] ?? ''),
    'invoice_note' => trim($_POST['invoice_note'] ?? ''),
    'logo_path' => $logo['path'],
  ];

  if ($data['seller_name'] === '' || $data['seller_registration_no'] === '' || $data['seller_address'] === '') {
    render('invoice_settings.php', [
      'settings' => $data,
      'error' => 'Uzņēmuma nosaukums, reģistrācijas numurs un juridiskā adrese ir obligāti.',
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }

  if ($data['seller_email'] !== '' && !filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
    render('invoice_settings.php', [
      'settings' => $data,
      'error' => 'E-pasts nav korekts.',
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }

  $stmt = db()->prepare("
    INSERT INTO invoice_settings (
      id, seller_name, seller_registration_no, seller_vat_no, seller_address,
      seller_email, seller_phone, seller_bank_name, seller_iban, seller_swift,
      invoice_note, logo_path
    ) VALUES (
      1, :seller_name, :seller_registration_no, :seller_vat_no, :seller_address,
      :seller_email, :seller_phone, :seller_bank_name, :seller_iban, :seller_swift,
      :invoice_note, :logo_path
    )
    ON DUPLICATE KEY UPDATE
      seller_name = VALUES(seller_name),
      seller_registration_no = VALUES(seller_registration_no),
      seller_vat_no = VALUES(seller_vat_no),
      seller_address = VALUES(seller_address),
      seller_email = VALUES(seller_email),
      seller_phone = VALUES(seller_phone),
      seller_bank_name = VALUES(seller_bank_name),
      seller_iban = VALUES(seller_iban),
      seller_swift = VALUES(seller_swift),
      invoice_note = VALUES(invoice_note),
      logo_path = VALUES(logo_path)
  ");
  $stmt->execute($data);

  redirect(url('/invoice-settings?saved=1'));
}

function invoices_fetch_all(): array
{
  return db()->query("
    SELECT i.id, i.invoice_number, i.invoice_date, i.due_date, i.payer_name,
           i.total, i.currency, i.job_id, j.title AS job_title
    FROM invoices i
    LEFT JOIN jobs j ON j.id = i.job_id
    ORDER BY i.invoice_date DESC, i.id DESC
  ")->fetchAll();
}

function invoices_index(): void
{
  render('invoices.php', [
    'invoices' => invoices_fetch_all(),
  ], 'Rēķini');
}

function invoice_fetch_job(int $jobId): ?array
{
  $stmt = db()->prepare("
    SELECT j.*, c.name AS client_name, c.email AS client_email, c.phone AS client_phone
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    WHERE j.id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $jobId]);

  return $stmt->fetch() ?: null;
}

function invoice_find_by_job(int $jobId): ?array
{
  $stmt = db()->prepare("SELECT id FROM invoices WHERE job_id = :job_id LIMIT 1");
  $stmt->execute([':job_id' => $jobId]);

  return $stmt->fetch() ?: null;
}

function invoice_create_form(): void
{
  $jobId = (int)($_GET['job_id'] ?? 0);
  if ($jobId <= 0) {
    http_response_code(404);
    echo '404 Not Found';
    return;
  }

  $existing = invoice_find_by_job($jobId);
  if ($existing) {
    redirect(url('/invoice?id=' . (int)$existing['id']));
  }

  $job = invoice_fetch_job($jobId);
  if (!$job) {
    http_response_code(404);
    echo '404 Not Found';
    return;
  }

  $settings = invoice_settings_fetch();
  $today = date('Y-m-d');

  $form = [
    'job_id' => $jobId,
    'payer_type' => 'private_person',
    'payer_name' => $job['client_name'] ?? '',
    'payer_registration_no' => '',
    'payer_vat_no' => '',
    'payer_address' => '',
    'payer_email' => $job['client_email'] ?? '',
    'payer_phone' => $job['client_phone'] ?? '',
    'service_receiver_name' => $job['client_name'] ?? '',
    'service_receiver_address' => '',
    'service_receiver_notes' => '',
    'invoice_date' => $today,
    'due_date' => date('Y-m-d', strtotime($today . ' +14 days')),
    'service_description' => $job['title'] ?? '',
    'quantity' => '1',
    'unit' => 'gab.',
    'unit_price' => $job['price'] !== null ? (string)$job['price'] : '0.00',
    'vat_rate' => '0',
    'currency' => 'EUR',
    'notes' => $settings['invoice_note'] ?? '',
  ];

  render('invoice_form.php', [
    'job' => $job,
    'settings' => $settings,
    'form' => $form,
    'error' => null,
  ], 'Izveidot rēķinu');
}

function invoice_validate_form(array $data): ?string
{
  $allowedPayerTypes = ['private_person', 'company', 'self_employed', 'association', 'public_institution', 'other'];

  if (!in_array($data['payer_type'], $allowedPayerTypes, true)) {
    return 'Maksātāja tips nav korekts.';
  }
  if ($data['invoice_date'] === '' || $data['payer_name'] === '' || $data['service_description'] === '') {
    return 'Rēķina datums, maksātāja nosaukums un pakalpojuma apraksts ir obligāti.';
  }
  $invoiceDate = DateTime::createFromFormat('Y-m-d', $data['invoice_date']);
  if (!$invoiceDate || $invoiceDate->format('Y-m-d') !== $data['invoice_date']) {
    return 'Rēķina datums nav korekts.';
  }
  if ($data['due_date'] !== '') {
    $dueDate = DateTime::createFromFormat('Y-m-d', $data['due_date']);
    if (!$dueDate || $dueDate->format('Y-m-d') !== $data['due_date']) {
      return 'Apmaksas termiņš nav korekts.';
    }
  }
  if ($data['payer_email'] !== '' && !filter_var($data['payer_email'], FILTER_VALIDATE_EMAIL)) {
    return 'Maksātāja e-pasts nav korekts.';
  }
  if (!is_numeric($data['quantity']) || (float)$data['quantity'] <= 0) {
    return 'Daudzumam jābūt lielākam par 0.';
  }
  if (!is_numeric($data['unit_price']) || (float)$data['unit_price'] < 0) {
    return 'Cena nav korekta.';
  }
  if (!is_numeric($data['vat_rate']) || (float)$data['vat_rate'] < 0 || (float)$data['vat_rate'] > 100) {
    return 'PVN likmei jābūt no 0 līdz 100.';
  }

  return null;
}

function invoice_next_number(PDO $pdo, int $year): string
{
  $prefix = 'R-' . $year . '-';
  $stmt = $pdo->prepare("
    SELECT invoice_number
    FROM invoices
    WHERE invoice_number LIKE :prefix
    ORDER BY invoice_number DESC
    LIMIT 1
    FOR UPDATE
  ");
  $stmt->execute([':prefix' => $prefix . '%']);
  $last = $stmt->fetch();
  $next = 1;

  if ($last && preg_match('/^R-\d{4}-(\d{4})$/', $last['invoice_number'], $matches)) {
    $next = (int)$matches[1] + 1;
  }

  return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function invoice_store(): void
{
  $jobId = (int)($_POST['job_id'] ?? 0);
  $job = invoice_fetch_job($jobId);
  if (!$job) {
    http_response_code(404);
    echo '404 Not Found';
    return;
  }

  $existing = invoice_find_by_job($jobId);
  if ($existing) {
    redirect(url('/invoice?id=' . (int)$existing['id']));
  }

  $settings = invoice_settings_fetch();
  $data = [
    'job_id' => $jobId,
    'payer_type' => trim($_POST['payer_type'] ?? 'private_person'),
    'payer_name' => trim($_POST['payer_name'] ?? ''),
    'payer_registration_no' => trim($_POST['payer_registration_no'] ?? ''),
    'payer_vat_no' => trim($_POST['payer_vat_no'] ?? ''),
    'payer_address' => trim($_POST['payer_address'] ?? ''),
    'payer_email' => trim($_POST['payer_email'] ?? ''),
    'payer_phone' => trim($_POST['payer_phone'] ?? ''),
    'service_receiver_name' => trim($_POST['service_receiver_name'] ?? ''),
    'service_receiver_address' => trim($_POST['service_receiver_address'] ?? ''),
    'service_receiver_notes' => trim($_POST['service_receiver_notes'] ?? ''),
    'invoice_date' => trim($_POST['invoice_date'] ?? ''),
    'due_date' => trim($_POST['due_date'] ?? ''),
    'service_description' => trim($_POST['service_description'] ?? ''),
    'quantity' => trim($_POST['quantity'] ?? '1'),
    'unit' => trim($_POST['unit'] ?? 'gab.'),
    'unit_price' => trim($_POST['unit_price'] ?? ''),
    'vat_rate' => trim($_POST['vat_rate'] ?? '0'),
    'currency' => trim($_POST['currency'] ?? 'EUR') ?: 'EUR',
    'notes' => trim($_POST['notes'] ?? ''),
  ];

  if (
    trim($settings['seller_name'] ?? '') === '' ||
    trim($settings['seller_registration_no'] ?? '') === '' ||
    trim($settings['seller_address'] ?? '') === ''
  ) {
    render('invoice_form.php', [
      'job' => $job,
      'settings' => $settings,
      'form' => $data,
      'error' => 'Vispirms aizpildi rēķinu iestatījumus.',
    ], 'Izveidot rēķinu');
    return;
  }

  $error = invoice_validate_form($data);
  if ($error) {
    render('invoice_form.php', [
      'job' => $job,
      'settings' => $settings,
      'form' => $data,
      'error' => $error,
    ], 'Izveidot rēķinu');
    return;
  }

  $quantity = round((float)$data['quantity'], 2);
  $unitPrice = round((float)$data['unit_price'], 2);
  $vatRate = round((float)$data['vat_rate'], 2);
  $subtotal = round($quantity * $unitPrice, 2);
  $vatAmount = round($subtotal * $vatRate / 100, 2);
  $total = round($subtotal + $vatAmount, 2);
  $year = (int)date('Y', strtotime($data['invoice_date']));

  $pdo = db();
  for ($attempt = 0; $attempt < 3; $attempt++) {
    try {
      $pdo->beginTransaction();
      $invoiceNumber = invoice_next_number($pdo, $year);

      $stmt = $pdo->prepare("
        INSERT INTO invoices (
          invoice_number, job_id, invoice_date, due_date,
          seller_name, seller_registration_no, seller_vat_no, seller_address,
          seller_email, seller_phone, seller_bank_name, seller_iban, seller_swift,
          seller_logo_path,
          payer_type, payer_name, payer_registration_no, payer_vat_no, payer_address,
          payer_email, payer_phone,
          service_receiver_name, service_receiver_address, service_receiver_notes,
          service_description, quantity, unit, unit_price, vat_rate,
          subtotal, vat_amount, total, currency, notes
        ) VALUES (
          :invoice_number, :job_id, :invoice_date, :due_date,
          :seller_name, :seller_registration_no, :seller_vat_no, :seller_address,
          :seller_email, :seller_phone, :seller_bank_name, :seller_iban, :seller_swift,
          :seller_logo_path,
          :payer_type, :payer_name, :payer_registration_no, :payer_vat_no, :payer_address,
          :payer_email, :payer_phone,
          :service_receiver_name, :service_receiver_address, :service_receiver_notes,
          :service_description, :quantity, :unit, :unit_price, :vat_rate,
          :subtotal, :vat_amount, :total, :currency, :notes
        )
      ");

      $stmt->execute([
        ':invoice_number' => $invoiceNumber,
        ':job_id' => $jobId,
        ':invoice_date' => $data['invoice_date'],
        ':due_date' => ($data['due_date'] === '' ? null : $data['due_date']),
        ':seller_name' => $settings['seller_name'],
        ':seller_registration_no' => $settings['seller_registration_no'],
        ':seller_vat_no' => ($settings['seller_vat_no'] === '' ? null : $settings['seller_vat_no']),
        ':seller_address' => $settings['seller_address'],
        ':seller_email' => ($settings['seller_email'] === '' ? null : $settings['seller_email']),
        ':seller_phone' => ($settings['seller_phone'] === '' ? null : $settings['seller_phone']),
        ':seller_bank_name' => ($settings['seller_bank_name'] === '' ? null : $settings['seller_bank_name']),
        ':seller_iban' => ($settings['seller_iban'] === '' ? null : $settings['seller_iban']),
        ':seller_swift' => ($settings['seller_swift'] === '' ? null : $settings['seller_swift']),
        ':seller_logo_path' => ($settings['logo_path'] === '' ? null : $settings['logo_path']),
        ':payer_type' => $data['payer_type'],
        ':payer_name' => $data['payer_name'],
        ':payer_registration_no' => ($data['payer_registration_no'] === '' ? null : $data['payer_registration_no']),
        ':payer_vat_no' => ($data['payer_vat_no'] === '' ? null : $data['payer_vat_no']),
        ':payer_address' => ($data['payer_address'] === '' ? null : $data['payer_address']),
        ':payer_email' => ($data['payer_email'] === '' ? null : $data['payer_email']),
        ':payer_phone' => ($data['payer_phone'] === '' ? null : $data['payer_phone']),
        ':service_receiver_name' => ($data['service_receiver_name'] === '' ? null : $data['service_receiver_name']),
        ':service_receiver_address' => ($data['service_receiver_address'] === '' ? null : $data['service_receiver_address']),
        ':service_receiver_notes' => ($data['service_receiver_notes'] === '' ? null : $data['service_receiver_notes']),
        ':service_description' => $data['service_description'],
        ':quantity' => $quantity,
        ':unit' => ($data['unit'] === '' ? 'gab.' : $data['unit']),
        ':unit_price' => $unitPrice,
        ':vat_rate' => $vatRate,
        ':subtotal' => $subtotal,
        ':vat_amount' => $vatAmount,
        ':total' => $total,
        ':currency' => $data['currency'],
        ':notes' => ($data['notes'] === '' ? null : $data['notes']),
      ]);

      $invoiceId = (int)$pdo->lastInsertId();
      $pdo->commit();
      redirect(url('/invoice?id=' . $invoiceId));
    } catch (PDOException $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }

      if (str_contains($e->getMessage(), 'Duplicate') && $attempt < 2) {
        $existingAfterDuplicate = invoice_find_by_job($jobId);
        if ($existingAfterDuplicate) {
          redirect(url('/invoice?id=' . (int)$existingAfterDuplicate['id']));
        }
        continue;
      }

      throw $e;
    }
  }
}

function invoice_fetch(int $id): ?array
{
  $stmt = db()->prepare("
    SELECT i.*, j.title AS job_title
    FROM invoices i
    LEFT JOIN jobs j ON j.id = i.job_id
    WHERE i.id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $id]);

  return $stmt->fetch() ?: null;
}

function invoice_show(): void
{
  $invoice = invoice_fetch((int)($_GET['id'] ?? 0));
  render('invoice_show.php', [
    'invoice' => $invoice,
  ], 'Rēķins');
}

function invoice_public_logo_url(?string $path): string
{
  if (!$path) {
    return '';
  }

  return url('/' . ltrim($path, '/'));
}

function invoice_logo_full_path(?string $path): string
{
  if (!$path) {
    return '';
  }

  $publicDir = realpath(__DIR__ . '/../../public');
  $fullPath = realpath(__DIR__ . '/../../public/' . ltrim($path, '/'));
  if (!$fullPath || !is_file($fullPath)) {
    return '';
  }

  if (!$publicDir || !str_starts_with($fullPath, $publicDir . DIRECTORY_SEPARATOR)) {
    return '';
  }

  return $fullPath;
}

function invoice_pdf_logo_src(?string $path): string
{
  if (!extension_loaded('gd')) {
    return '';
  }

  $fullPath = invoice_logo_full_path($path);
  if ($fullPath === '') {
    return '';
  }

  $info = @getimagesize($fullPath);
  if ($info === false || empty($info['mime'])) {
    return '';
  }

  $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
  if (!in_array($info['mime'], $allowedMimeTypes, true)) {
    return '';
  }

  $data = file_get_contents($fullPath);
  if ($data === false) {
    return '';
  }

  return 'data:' . $info['mime'] . ';base64,' . base64_encode($data);
}

function invoice_pdf(): void
{
  $invoice = invoice_fetch((int)($_GET['id'] ?? 0));
  if (!$invoice) {
    http_response_code(404);
    echo '404 Not Found';
    return;
  }

  $autoload = __DIR__ . '/../../vendor/autoload.php';
  if (!is_file($autoload)) {
    render('invoice_show.php', [
      'invoice' => $invoice,
      'error' => 'PDF ģenerēšanai nav instalēta Dompdf bibliotēka. Palaid composer install projekta saknē.',
    ], 'Rēķins');
    return;
  }

  require_once $autoload;

  $logoSrc = invoice_pdf_logo_src($invoice['seller_logo_path'] ?? null);
  ob_start();
  ?>
  <!doctype html>
  <html lang="lv">
  <head>
    <meta charset="utf-8">
    <style>
      body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #222; }
      h1 { font-size: 22px; margin: 0 0 12px; }
      h2 { font-size: 14px; margin: 18px 0 6px; }
      table { border-collapse: collapse; width: 100%; margin-top: 12px; }
      th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
      th { background: #f2f2f2; }
      .top { width: 100%; margin-bottom: 18px; }
      .top td { border: 0; padding: 0; }
      .right { text-align: right; }
      .logo { max-width: 150px; max-height: 90px; }
    </style>
  </head>
  <body>
    <table class="top">
      <tr>
        <td>
          <?php if ($logoSrc): ?>
            <img class="logo" src="<?= h($logoSrc) ?>" alt="">
          <?php endif; ?>
        </td>
        <td class="right">
          <h1>Rēķins <?= h($invoice['invoice_number']) ?></h1>
          <div>Datums: <?= h($invoice['invoice_date']) ?></div>
          <div>Apmaksas termiņš: <?= h($invoice['due_date'] ?? '') ?></div>
        </td>
      </tr>
    </table>

    <h2>Pārdevējs</h2>
    <div><strong>Nosaukums:</strong> <?= h($invoice['seller_name']) ?></div>
    <div><strong>Reģ. Nr.:</strong> <?= h($invoice['seller_registration_no']) ?></div>
    <?php if (!empty($invoice['seller_vat_no'])): ?><div><strong>PVN Nr.:</strong> <?= h($invoice['seller_vat_no']) ?></div><?php endif; ?>
    <div><strong>Juridiskā adrese:</strong><br><?= nl2br(h($invoice['seller_address'])) ?></div>
    <?php if (!empty($invoice['seller_email'])): ?><div><strong>E-pasts:</strong> <?= h($invoice['seller_email']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['seller_phone'])): ?><div><strong>Telefons:</strong> <?= h($invoice['seller_phone']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['seller_bank_name'])): ?><div><strong>Banka:</strong> <?= h($invoice['seller_bank_name']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['seller_iban'])): ?><div><strong>IBAN:</strong> <?= h($invoice['seller_iban']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['seller_swift'])): ?><div><strong>SWIFT/BIC:</strong> <?= h($invoice['seller_swift']) ?></div><?php endif; ?>

    <h2>Maksātājs</h2>
    <div><strong>Tips:</strong> <?= h(invoice_payer_type_label($invoice['payer_type'])) ?></div>
    <div><strong>Nosaukums / vārds:</strong> <?= h($invoice['payer_name']) ?></div>
    <?php if (!empty($invoice['payer_registration_no'])): ?><div><strong>Reģ. Nr.:</strong> <?= h($invoice['payer_registration_no']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['payer_vat_no'])): ?><div><strong>PVN Nr.:</strong> <?= h($invoice['payer_vat_no']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['payer_address'])): ?><div><strong>Adrese:</strong><br><?= nl2br(h($invoice['payer_address'])) ?></div><?php endif; ?>
    <?php if (!empty($invoice['payer_email'])): ?><div><strong>E-pasts:</strong> <?= h($invoice['payer_email']) ?></div><?php endif; ?>
    <?php if (!empty($invoice['payer_phone'])): ?><div><strong>Telefons:</strong> <?= h($invoice['payer_phone']) ?></div><?php endif; ?>

    <?php if (!empty($invoice['service_receiver_name']) || !empty($invoice['service_receiver_address']) || !empty($invoice['service_receiver_notes'])): ?>
      <h2>Pakalpojuma saņēmējs</h2>
      <?php if (!empty($invoice['service_receiver_name'])): ?><div><strong>Nosaukums / vārds:</strong> <?= h($invoice['service_receiver_name']) ?></div><?php endif; ?>
      <?php if (!empty($invoice['service_receiver_address'])): ?><div><strong>Adrese:</strong><br><?= nl2br(h($invoice['service_receiver_address'])) ?></div><?php endif; ?>
      <?php if (!empty($invoice['service_receiver_notes'])): ?><div><strong>Piezīmes:</strong><br><?= nl2br(h($invoice['service_receiver_notes'])) ?></div><?php endif; ?>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Pakalpojums</th>
          <th>Daudzums</th>
          <th>Mērv.</th>
          <th>Cena</th>
          <th>PVN %</th>
          <th>Kopā</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= nl2br(h($invoice['service_description'])) ?></td>
          <td><?= h((string)$invoice['quantity']) ?></td>
          <td><?= h($invoice['unit']) ?></td>
          <td><?= number_format((float)$invoice['unit_price'], 2, '.', '') ?></td>
          <td><?= number_format((float)$invoice['vat_rate'], 2, '.', '') ?></td>
          <td><?= number_format((float)$invoice['subtotal'], 2, '.', '') ?> <?= h($invoice['currency']) ?></td>
        </tr>
      </tbody>
    </table>

    <p class="right">Summa bez PVN: <?= number_format((float)$invoice['subtotal'], 2, '.', '') ?> <?= h($invoice['currency']) ?></p>
    <p class="right">PVN: <?= number_format((float)$invoice['vat_amount'], 2, '.', '') ?> <?= h($invoice['currency']) ?></p>
    <p class="right"><strong>Apmaksai: <?= number_format((float)$invoice['total'], 2, '.', '') ?> <?= h($invoice['currency']) ?></strong></p>

    <?php if (!empty($invoice['notes'])): ?>
      <h2>Piezīmes</h2>
      <div><?= nl2br(h($invoice['notes'])) ?></div>
    <?php endif; ?>
  </body>
  </html>
  <?php
  $html = ob_get_clean();

  try {
    $options = new Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $fileName = 'rekins-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $invoice['invoice_number']) . '.pdf';
    $dompdf->stream($fileName, ['Attachment' => true]);
    exit;
  } catch (Throwable $e) {
    render('invoice_show.php', [
      'invoice' => $invoice,
      'error' => 'PDF ģenerēšana neizdevās. Pārbaudi PHP GD extension vai noņem logo.',
    ], 'Rēķins');
  }
}
