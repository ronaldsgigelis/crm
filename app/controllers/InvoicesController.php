<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../routes.php';

function invoice_profile_defaults(): array
{
  return [
    'id' => 0,
    'profile_name' => '',
    'is_default' => 0,
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
    'show_logo' => 1,
    'show_seller_vat_no' => 1,
    'show_seller_email' => 1,
    'show_seller_phone' => 1,
    'show_seller_bank_name' => 1,
    'show_seller_iban' => 1,
    'show_seller_swift' => 1,
    'show_invoice_note' => 1,
    'numbering_type' => 'yearly_sequence',
    'numbering_prefix' => 'R',
    'numbering_separator' => '-',
    'numbering_padding' => 4,
  ];
}

function invoice_number_preview(array $profile): string
{
  $type = $profile['numbering_type'] ?? 'yearly_sequence';
  $prefix = trim((string)($profile['numbering_prefix'] ?? 'R'));
  $separator = trim((string)($profile['numbering_separator'] ?? '-'));
  $padding = max(1, min(10, (int)($profile['numbering_padding'] ?? 4)));
  $sequence = str_pad('1', $padding, '0', STR_PAD_LEFT);
  $year = date('Y');
  $date = date('dmY');

  return match ($type) {
    'daily_sequence' => $prefix . $date . $separator . $sequence,
    'continuous_sequence' => ($prefix !== '' ? $prefix . $separator : '') . $sequence,
    'manual' => 'Manuāls numurs',
    default => ($prefix !== '' ? $prefix . $separator : '') . $year . $separator . $sequence,
  };
}

function invoice_numbering_type(array $profile): string
{
  $type = $profile['numbering_type'] ?? 'yearly_sequence';
  return in_array($type, ['yearly_sequence', 'daily_sequence', 'continuous_sequence', 'manual'], true)
    ? $type
    : 'yearly_sequence';
}

function invoice_format_sequence_number(array $profile, string $invoiceDate, int $sequence): string
{
  $type = invoice_numbering_type($profile);
  $prefix = trim((string)($profile['numbering_prefix'] ?? 'R'));
  $separator = trim((string)($profile['numbering_separator'] ?? '-'));
  $padding = max(1, min(10, (int)($profile['numbering_padding'] ?? 4)));
  $paddedSequence = str_pad((string)$sequence, $padding, '0', STR_PAD_LEFT);
  $date = DateTime::createFromFormat('Y-m-d', $invoiceDate) ?: new DateTime();

  return match ($type) {
    'daily_sequence' => $prefix . $date->format('dmY') . $separator . $paddedSequence,
    'continuous_sequence' => ($prefix !== '' ? $prefix . $separator : '') . $paddedSequence,
    'manual' => '',
    default => ($prefix !== '' ? $prefix . $separator : '') . $date->format('Y') . $separator . $paddedSequence,
  };
}

function invoice_extract_sequence_from_number(string $invoiceNumber, array $profile, string $invoiceDate): ?int
{
  $type = invoice_numbering_type($profile);
  if ($type === 'manual') {
    return null;
  }

  $prefix = trim((string)($profile['numbering_prefix'] ?? 'R'));
  $separator = trim((string)($profile['numbering_separator'] ?? '-'));
  $date = DateTime::createFromFormat('Y-m-d', $invoiceDate) ?: new DateTime();

  $expectedPrefix = match ($type) {
    'daily_sequence' => $prefix . $date->format('dmY') . $separator,
    'continuous_sequence' => $prefix !== '' ? $prefix . $separator : '',
    default => ($prefix !== '' ? $prefix . $separator : '') . $date->format('Y') . $separator,
  };

  if (!preg_match('/^' . preg_quote($expectedPrefix, '/') . '(\d+)$/', $invoiceNumber, $matches)) {
    return null;
  }

  return (int)$matches[1];
}

function invoice_number_exists(string $invoiceNumber): bool
{
  $stmt = db()->prepare("SELECT 1 FROM invoices WHERE invoice_number = :invoice_number LIMIT 1");
  $stmt->execute([':invoice_number' => $invoiceNumber]);

  return (bool)$stmt->fetchColumn();
}

function invoice_generate_next_number(array $profile, string $invoiceDate): string
{
  if (invoice_numbering_type($profile) === 'manual') {
    return '';
  }

  $profileId = (int)($profile['id'] ?? 0);
  $date = DateTime::createFromFormat('Y-m-d', $invoiceDate) ?: new DateTime();
  $params = [':profile_id' => $profileId];

  if (invoice_numbering_type($profile) === 'yearly_sequence') {
    $where = 'invoice_profile_id = :profile_id AND YEAR(invoice_date) = :invoice_year';
    $params[':invoice_year'] = $date->format('Y');
  } elseif (invoice_numbering_type($profile) === 'daily_sequence') {
    $where = 'invoice_profile_id = :profile_id AND invoice_date = :invoice_date';
    $params[':invoice_date'] = $date->format('Y-m-d');
  } else {
    $where = 'invoice_profile_id = :profile_id';
  }

  $stmt = db()->prepare("
    SELECT invoice_number
    FROM invoices
    WHERE {$where}
    ORDER BY id ASC
  ");
  $stmt->execute($params);

  $highest = 0;
  foreach ($stmt->fetchAll() as $row) {
    $sequence = invoice_extract_sequence_from_number((string)$row['invoice_number'], $profile, $date->format('Y-m-d'));
    if ($sequence !== null && $sequence > $highest) {
      $highest = $sequence;
    }
  }

  $next = $highest + 1;
  do {
    $invoiceNumber = invoice_format_sequence_number($profile, $date->format('Y-m-d'), $next);
    $next++;
  } while (invoice_number_exists($invoiceNumber));

  return $invoiceNumber;
}

function invoice_profiles_fetch_all(): array
{
  return db()->query("
    SELECT *
    FROM invoice_profiles
    ORDER BY is_default DESC, id ASC
  ")->fetchAll();
}

function invoice_profile_fetch(int $id): ?array
{
  $stmt = db()->prepare("SELECT * FROM invoice_profiles WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);

  return $stmt->fetch() ?: null;
}

function invoice_profile_fetch_default(): ?array
{
  $profile = db()->query("
    SELECT *
    FROM invoice_profiles
    WHERE is_default = 1
    ORDER BY id ASC
    LIMIT 1
  ")->fetch();

  if ($profile) {
    return $profile;
  }

  $profile = db()->query("
    SELECT *
    FROM invoice_profiles
    ORDER BY id ASC
    LIMIT 1
  ")->fetch();

  return $profile ?: null;
}

function invoice_settings_index(?string $error = null): void
{
  $profiles = invoice_profiles_fetch_all();
  $selectedId = (int)($_GET['id'] ?? 0);
  $profile = $selectedId > 0 ? invoice_profile_fetch($selectedId) : null;

  render('invoice_settings.php', [
    'profiles' => $profiles,
    'profile' => array_merge(invoice_profile_defaults(), $profile ?: []),
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

function invoice_profile_post_data(array $existing = []): array
{
  $data = array_merge(invoice_profile_defaults(), $existing);

  foreach ([
    'profile_name', 'seller_name', 'seller_registration_no', 'seller_vat_no',
    'seller_address', 'seller_email', 'seller_phone', 'seller_bank_name',
    'seller_iban', 'seller_swift', 'invoice_note', 'numbering_type',
    'numbering_prefix', 'numbering_separator',
  ] as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
  }

  $allowedNumberingTypes = ['yearly_sequence', 'daily_sequence', 'continuous_sequence', 'manual'];
  if (!in_array($data['numbering_type'], $allowedNumberingTypes, true)) {
    $data['numbering_type'] = 'yearly_sequence';
  }

  $padding = (int)($_POST['numbering_padding'] ?? 4);
  $data['numbering_padding'] = max(1, min(10, $padding));

  foreach ([
    'is_default', 'show_logo', 'show_seller_vat_no', 'show_seller_email',
    'show_seller_phone', 'show_seller_bank_name', 'show_seller_iban',
    'show_seller_swift', 'show_invoice_note',
  ] as $field) {
    $data[$field] = isset($_POST[$field]) ? 1 : 0;
  }

  return $data;
}

function invoice_settings_store(): void
{
  $id = (int)($_POST['id'] ?? 0);
  $existing = $id > 0 ? invoice_profile_fetch($id) : null;
  if ($id > 0 && !$existing) {
    invoice_settings_index('Profils nav atrasts.');
    return;
  }

  $profiles = invoice_profiles_fetch_all();
  $data = invoice_profile_post_data($existing ?: []);
  $data['id'] = $id;

  $logo = invoice_handle_logo_upload($existing['logo_path'] ?? '');
  if (!$logo['ok']) {
    render('invoice_settings.php', [
      'profiles' => $profiles,
      'profile' => $data,
      'error' => $logo['error'],
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }
  $data['logo_path'] = $logo['path'];

  if ($data['profile_name'] === '' || $data['seller_name'] === '' || $data['seller_registration_no'] === '' || $data['seller_address'] === '') {
    render('invoice_settings.php', [
      'profiles' => $profiles,
      'profile' => $data,
      'error' => 'Profila nosaukums, pārdevējs, reģistrācijas numurs un juridiskā adrese ir obligāti.',
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }

  if ($data['seller_email'] !== '' && !filter_var($data['seller_email'], FILTER_VALIDATE_EMAIL)) {
    render('invoice_settings.php', [
      'profiles' => $profiles,
      'profile' => $data,
      'error' => 'E-pasts nav korekts.',
      'saved' => false,
    ], 'Rēķinu iestatījumi');
    return;
  }

  $isFirstProfile = count($profiles) === 0 && !$existing;
  if ($isFirstProfile) {
    $data['is_default'] = 1;
  }

  $pdo = db();
  $pdo->beginTransaction();
  try {
    if ((int)$data['is_default'] === 1) {
      $pdo->exec("UPDATE invoice_profiles SET is_default = 0");
    }

    if ($existing) {
      $stmt = $pdo->prepare("
        UPDATE invoice_profiles
        SET profile_name = :profile_name,
            is_default = :is_default,
            seller_name = :seller_name,
            seller_registration_no = :seller_registration_no,
            seller_vat_no = :seller_vat_no,
            seller_address = :seller_address,
            seller_email = :seller_email,
            seller_phone = :seller_phone,
            seller_bank_name = :seller_bank_name,
            seller_iban = :seller_iban,
            seller_swift = :seller_swift,
            invoice_note = :invoice_note,
            logo_path = :logo_path,
            show_logo = :show_logo,
            show_seller_vat_no = :show_seller_vat_no,
            show_seller_email = :show_seller_email,
            show_seller_phone = :show_seller_phone,
            show_seller_bank_name = :show_seller_bank_name,
            show_seller_iban = :show_seller_iban,
            show_seller_swift = :show_seller_swift,
            show_invoice_note = :show_invoice_note,
            numbering_type = :numbering_type,
            numbering_prefix = :numbering_prefix,
            numbering_separator = :numbering_separator,
            numbering_padding = :numbering_padding
        WHERE id = :id
      ");
      $params = [':id' => $id];
    } else {
      $stmt = $pdo->prepare("
        INSERT INTO invoice_profiles (
          profile_name, is_default, seller_name, seller_registration_no, seller_vat_no,
          seller_address, seller_email, seller_phone, seller_bank_name, seller_iban,
          seller_swift, invoice_note, logo_path, show_logo, show_seller_vat_no,
          show_seller_email, show_seller_phone, show_seller_bank_name, show_seller_iban,
          show_seller_swift, show_invoice_note, numbering_type, numbering_prefix,
          numbering_separator, numbering_padding
        ) VALUES (
          :profile_name, :is_default, :seller_name, :seller_registration_no, :seller_vat_no,
          :seller_address, :seller_email, :seller_phone, :seller_bank_name, :seller_iban,
          :seller_swift, :invoice_note, :logo_path, :show_logo, :show_seller_vat_no,
          :show_seller_email, :show_seller_phone, :show_seller_bank_name, :show_seller_iban,
          :show_seller_swift, :show_invoice_note, :numbering_type, :numbering_prefix,
          :numbering_separator, :numbering_padding
        )
      ");
      $params = [];
    }

    $stmt->execute($params + [
      ':profile_name' => $data['profile_name'],
      ':is_default' => (int)$data['is_default'],
      ':seller_name' => $data['seller_name'],
      ':seller_registration_no' => $data['seller_registration_no'],
      ':seller_vat_no' => ($data['seller_vat_no'] === '' ? null : $data['seller_vat_no']),
      ':seller_address' => $data['seller_address'],
      ':seller_email' => ($data['seller_email'] === '' ? null : $data['seller_email']),
      ':seller_phone' => ($data['seller_phone'] === '' ? null : $data['seller_phone']),
      ':seller_bank_name' => ($data['seller_bank_name'] === '' ? null : $data['seller_bank_name']),
      ':seller_iban' => ($data['seller_iban'] === '' ? null : $data['seller_iban']),
      ':seller_swift' => ($data['seller_swift'] === '' ? null : $data['seller_swift']),
      ':invoice_note' => ($data['invoice_note'] === '' ? null : $data['invoice_note']),
      ':logo_path' => ($data['logo_path'] === '' ? null : $data['logo_path']),
      ':show_logo' => (int)$data['show_logo'],
      ':show_seller_vat_no' => (int)$data['show_seller_vat_no'],
      ':show_seller_email' => (int)$data['show_seller_email'],
      ':show_seller_phone' => (int)$data['show_seller_phone'],
      ':show_seller_bank_name' => (int)$data['show_seller_bank_name'],
      ':show_seller_iban' => (int)$data['show_seller_iban'],
      ':show_seller_swift' => (int)$data['show_seller_swift'],
      ':show_invoice_note' => (int)$data['show_invoice_note'],
      ':numbering_type' => $data['numbering_type'],
      ':numbering_prefix' => ($data['numbering_prefix'] === '' ? null : $data['numbering_prefix']),
      ':numbering_separator' => ($data['numbering_separator'] === '' ? null : $data['numbering_separator']),
      ':numbering_padding' => (int)$data['numbering_padding'],
    ]);

    $defaultCount = (int)$pdo->query("SELECT COUNT(*) FROM invoice_profiles WHERE is_default = 1")->fetchColumn();
    if ($defaultCount === 0) {
      $pdo->exec("UPDATE invoice_profiles SET is_default = 1 ORDER BY id ASC LIMIT 1");
    }

    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    throw $e;
  }

  redirect(url('/invoice-settings?saved=1'));
}

function invoice_settings_default_store(): void
{
  $id = (int)($_POST['id'] ?? 0);
  $profile = invoice_profile_fetch($id);
  if (!$profile) {
    invoice_settings_index('Profils nav atrasts.');
    return;
  }

  $pdo = db();
  $pdo->beginTransaction();
  try {
    $pdo->exec("UPDATE invoice_profiles SET is_default = 0");
    $stmt = $pdo->prepare("UPDATE invoice_profiles SET is_default = 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    throw $e;
  }

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
    'deleted' => (int)($_GET['deleted'] ?? 0) === 1,
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

function invoice_select_profile(array $profiles, int $requestedId = 0): ?array
{
  if (!$profiles) {
    return null;
  }

  if ($requestedId > 0) {
    foreach ($profiles as $profile) {
      if ((int)$profile['id'] === $requestedId) {
        return $profile;
      }
    }
  }

  foreach ($profiles as $profile) {
    if ((int)$profile['is_default'] === 1) {
      return $profile;
    }
  }

  return $profiles[0];
}

function invoice_templates_fetch_all(): array
{
  try {
    return db()->query("
      SELECT id, template_name, invoice_profile_id, payer_name, service_description,
             quantity, unit_price, currency, created_at
      FROM invoice_templates
      ORDER BY template_name ASC, id DESC
    ")->fetchAll();
  } catch (PDOException $e) {
    return [];
  }
}

function invoice_template_fetch(int $id): ?array
{
  if ($id <= 0) {
    return null;
  }

  try {
    $stmt = db()->prepare("SELECT * FROM invoice_templates WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);

    return $stmt->fetch() ?: null;
  } catch (PDOException $e) {
    return null;
  }
}

function invoice_template_store(array $data, int $templateId = 0): int
{
  if ($templateId > 0) {
    $stmt = db()->prepare("
      UPDATE invoice_templates
      SET template_name = :template_name,
          invoice_profile_id = :invoice_profile_id,
          payer_type = :payer_type,
          payer_name = :payer_name,
          payer_registration_no = :payer_registration_no,
          payer_vat_no = :payer_vat_no,
          payer_address = :payer_address,
          payer_email = :payer_email,
          payer_phone = :payer_phone,
          service_receiver_name = :service_receiver_name,
          service_receiver_address = :service_receiver_address,
          service_receiver_notes = :service_receiver_notes,
          service_description = :service_description,
          quantity = :quantity,
          unit = :unit,
          unit_price = :unit_price,
          vat_rate = :vat_rate,
          currency = :currency,
          notes = :notes
      WHERE id = :id
    ");
  } else {
    $stmt = db()->prepare("
      INSERT INTO invoice_templates (
        template_name, invoice_profile_id,
        payer_type, payer_name, payer_registration_no, payer_vat_no, payer_address,
        payer_email, payer_phone,
        service_receiver_name, service_receiver_address, service_receiver_notes,
        service_description, quantity, unit, unit_price, vat_rate, currency, notes
      ) VALUES (
        :template_name, :invoice_profile_id,
        :payer_type, :payer_name, :payer_registration_no, :payer_vat_no, :payer_address,
        :payer_email, :payer_phone,
        :service_receiver_name, :service_receiver_address, :service_receiver_notes,
        :service_description, :quantity, :unit, :unit_price, :vat_rate, :currency, :notes
      )
    ");
  }

  $params = [
    ':template_name' => $data['template_name'],
    ':invoice_profile_id' => (int)$data['invoice_profile_id'] > 0 ? (int)$data['invoice_profile_id'] : null,
    ':payer_type' => $data['payer_type'],
    ':payer_name' => $data['payer_name'],
    ':payer_registration_no' => $data['payer_registration_no'] === '' ? null : $data['payer_registration_no'],
    ':payer_vat_no' => $data['payer_vat_no'] === '' ? null : $data['payer_vat_no'],
    ':payer_address' => $data['payer_address'] === '' ? null : $data['payer_address'],
    ':payer_email' => $data['payer_email'] === '' ? null : $data['payer_email'],
    ':payer_phone' => $data['payer_phone'] === '' ? null : $data['payer_phone'],
    ':service_receiver_name' => $data['service_receiver_name'] === '' ? null : $data['service_receiver_name'],
    ':service_receiver_address' => $data['service_receiver_address'] === '' ? null : $data['service_receiver_address'],
    ':service_receiver_notes' => $data['service_receiver_notes'] === '' ? null : $data['service_receiver_notes'],
    ':service_description' => $data['service_description'],
    ':quantity' => round((float)$data['quantity'], 2),
    ':unit' => $data['unit'] === '' ? 'gab.' : $data['unit'],
    ':unit_price' => round((float)$data['unit_price'], 2),
    ':vat_rate' => round((float)$data['vat_rate'], 2),
    ':currency' => $data['currency'],
    ':notes' => $data['notes'] === '' ? null : $data['notes'],
  ];

  if ($templateId > 0) {
    $params[':id'] = $templateId;
  }

  $stmt->execute($params);

  return $templateId > 0 ? $templateId : (int)db()->lastInsertId();
}

function manual_invoice_default_form(?array $template, ?array $selectedProfile): array
{
  $today = date('Y-m-d');

  return [
    'invoice_profile_id' => $template ? (int)($template['invoice_profile_id'] ?? 0) : ($selectedProfile ? (int)$selectedProfile['id'] : 0),
    'invoice_number' => '',
    'payer_type' => $template['payer_type'] ?? 'private_person',
    'payer_name' => $template['payer_name'] ?? '',
    'payer_registration_no' => $template['payer_registration_no'] ?? '',
    'payer_vat_no' => $template['payer_vat_no'] ?? '',
    'payer_address' => $template['payer_address'] ?? '',
    'payer_email' => $template['payer_email'] ?? '',
    'payer_phone' => $template['payer_phone'] ?? '',
    'service_receiver_name' => $template['service_receiver_name'] ?? '',
    'service_receiver_address' => $template['service_receiver_address'] ?? '',
    'service_receiver_notes' => $template['service_receiver_notes'] ?? '',
    'invoice_date' => $today,
    'due_date' => date('Y-m-d', strtotime($today . ' +14 days')),
    'service_description' => $template['service_description'] ?? '',
    'quantity' => isset($template['quantity']) ? (string)$template['quantity'] : '1',
    'unit' => $template['unit'] ?? 'gab.',
    'unit_price' => isset($template['unit_price']) ? (string)$template['unit_price'] : '0.00',
    'vat_rate' => isset($template['vat_rate']) ? (string)$template['vat_rate'] : '0',
    'currency' => $template['currency'] ?? 'EUR',
    'notes' => $template['notes'] ?? (($selectedProfile && (int)$selectedProfile['show_invoice_note'] === 1) ? ($selectedProfile['invoice_note'] ?? '') : ''),
    'template_id' => $template ? (int)($template['id'] ?? 0) : 0,
    'template_name' => $template['template_name'] ?? '',
  ];
}

function manual_invoice_post_data(): array
{
  return [
    'invoice_profile_id' => (int)($_POST['invoice_profile_id'] ?? 0),
    'invoice_number' => trim($_POST['invoice_number'] ?? ''),
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
    'action' => trim($_POST['action'] ?? 'generate_invoice'),
    'template_id' => (int)($_POST['template_id'] ?? 0),
    'template_name' => trim($_POST['template_name'] ?? ''),
  ];
}

function manual_invoice_build_preview(array $data, array $profile, string $invoiceNumber): array
{
  $quantity = round((float)$data['quantity'], 2);
  $unitPrice = round((float)$data['unit_price'], 2);
  $vatRate = round((float)$data['vat_rate'], 2);
  $subtotal = round($quantity * $unitPrice, 2);
  $vatAmount = round($subtotal * $vatRate / 100, 2);
  $total = round($subtotal + $vatAmount, 2);

  $sellerVatNo = (int)$profile['show_seller_vat_no'] === 1 ? ($profile['seller_vat_no'] ?? null) : null;
  $sellerEmail = (int)$profile['show_seller_email'] === 1 ? ($profile['seller_email'] ?? null) : null;
  $sellerPhone = (int)$profile['show_seller_phone'] === 1 ? ($profile['seller_phone'] ?? null) : null;
  $sellerBankName = (int)$profile['show_seller_bank_name'] === 1 ? ($profile['seller_bank_name'] ?? null) : null;
  $sellerIban = (int)$profile['show_seller_iban'] === 1 ? ($profile['seller_iban'] ?? null) : null;
  $sellerSwift = (int)$profile['show_seller_swift'] === 1 ? ($profile['seller_swift'] ?? null) : null;
  $sellerLogoPath = (int)$profile['show_logo'] === 1 ? ($profile['logo_path'] ?? null) : null;

  return [
    'invoice_number' => $invoiceNumber,
    'invoice_date' => $data['invoice_date'],
    'due_date' => ($data['due_date'] === '' ? null : $data['due_date']),
    'seller_name' => $profile['seller_name'],
    'seller_registration_no' => $profile['seller_registration_no'],
    'seller_vat_no' => ($sellerVatNo === '' ? null : $sellerVatNo),
    'seller_address' => $profile['seller_address'],
    'seller_email' => ($sellerEmail === '' ? null : $sellerEmail),
    'seller_phone' => ($sellerPhone === '' ? null : $sellerPhone),
    'seller_bank_name' => ($sellerBankName === '' ? null : $sellerBankName),
    'seller_iban' => ($sellerIban === '' ? null : $sellerIban),
    'seller_swift' => ($sellerSwift === '' ? null : $sellerSwift),
    'seller_logo_path' => ($sellerLogoPath === '' ? null : $sellerLogoPath),
    'payer_type' => $data['payer_type'],
    'payer_name' => $data['payer_name'],
    'payer_registration_no' => ($data['payer_registration_no'] === '' ? null : $data['payer_registration_no']),
    'payer_vat_no' => ($data['payer_vat_no'] === '' ? null : $data['payer_vat_no']),
    'payer_address' => ($data['payer_address'] === '' ? null : $data['payer_address']),
    'payer_email' => ($data['payer_email'] === '' ? null : $data['payer_email']),
    'payer_phone' => ($data['payer_phone'] === '' ? null : $data['payer_phone']),
    'service_receiver_name' => ($data['service_receiver_name'] === '' ? null : $data['service_receiver_name']),
    'service_receiver_address' => ($data['service_receiver_address'] === '' ? null : $data['service_receiver_address']),
    'service_receiver_notes' => ($data['service_receiver_notes'] === '' ? null : $data['service_receiver_notes']),
    'service_description' => $data['service_description'],
    'quantity' => $quantity,
    'unit' => ($data['unit'] === '' ? 'gab.' : $data['unit']),
    'unit_price' => $unitPrice,
    'vat_rate' => $vatRate,
    'subtotal' => $subtotal,
    'vat_amount' => $vatAmount,
    'total' => $total,
    'currency' => $data['currency'],
    'notes' => ($data['notes'] === '' ? null : $data['notes']),
  ];
}

function manual_invoice_form(?string $error = null, ?array $form = null, ?array $previewInvoice = null): void
{
  $profiles = invoice_profiles_fetch_all();
  $templates = invoice_templates_fetch_all();
  $templateId = $form ? (int)($form['template_id'] ?? 0) : (int)($_GET['template_id'] ?? 0);
  $selectedTemplate = $templateId > 0 ? invoice_template_fetch($templateId) : null;
  $requestedProfileId = $form ? (int)($form['invoice_profile_id'] ?? 0) : (int)($selectedTemplate['invoice_profile_id'] ?? 0);
  $selectedProfile = invoice_select_profile($profiles, $requestedProfileId);

  if (!$form) {
    $form = manual_invoice_default_form($selectedTemplate, $selectedProfile);
    if ($selectedProfile) {
      $form['invoice_profile_id'] = (int)$selectedProfile['id'];
    }
  }

  $generatedNumber = '';
  if ($selectedProfile && invoice_numbering_type($selectedProfile) !== 'manual' && !empty($form['invoice_date'])) {
    $generatedNumber = invoice_generate_next_number($selectedProfile, $form['invoice_date']);
  }

  render('manual_invoice_form.php', [
    'profiles' => $profiles,
    'templates' => $templates,
    'selectedTemplate' => $selectedTemplate,
    'selectedProfile' => $selectedProfile,
    'form' => $form,
    'generatedNumber' => $generatedNumber,
    'previewInvoice' => $previewInvoice,
    'templateSaved' => (int)($_GET['template_saved'] ?? 0) === 1,
    'error' => $error,
  ], 'Manuāls rēķins');
}

function manual_invoice_store(): void
{
  $profiles = invoice_profiles_fetch_all();
  $data = manual_invoice_post_data();
  $profile = invoice_select_profile($profiles, (int)$data['invoice_profile_id']);
  if ($profile) {
    $data['invoice_profile_id'] = (int)$profile['id'];
  }

  if (!$profile) {
    manual_invoice_form('Vispirms izveido rēķina sūtītāja profilu.', $data);
    return;
  }

  if (trim($profile['seller_name'] ?? '') === '' || trim($profile['seller_registration_no'] ?? '') === '' || trim($profile['seller_address'] ?? '') === '') {
    manual_invoice_form('Izvēlētais rēķina sūtītāja profils nav pilnīgi aizpildīts.', $data);
    return;
  }

  $error = invoice_validate_form($data);
  if ($error) {
    manual_invoice_form($error, $data);
    return;
  }

  if ($data['action'] === 'save_template') {
    $templateId = (int)$data['template_id'];
    if ($templateId <= 0 && $data['template_name'] === '') {
      manual_invoice_form('Sagataves nosaukums ir obligāts.', $data);
      return;
    }

    $existingTemplate = $templateId > 0 ? invoice_template_fetch($templateId) : null;
    if ($existingTemplate && $data['template_name'] === '') {
      $data['template_name'] = $existingTemplate['template_name'];
    }

    try {
      $savedTemplateId = invoice_template_store($data, $existingTemplate ? $templateId : 0);
      redirect(url('/manual-invoice?template_id=' . $savedTemplateId . '&template_saved=1'));
    } catch (PDOException $e) {
      manual_invoice_form('Sagatavi neizdevās saglabāt.', $data);
      return;
    }
  }

  if ($data['action'] === 'preview_invoice') {
    $previewNumber = $data['invoice_number'] !== '' ? $data['invoice_number'] : invoice_generate_next_number($profile, $data['invoice_date']);
    if ($previewNumber === '') {
      $previewNumber = 'Priekšskatījums';
    }
    manual_invoice_form(null, $data, manual_invoice_build_preview($data, $profile, $previewNumber));
    return;
  }

  $invoiceNumber = $data['invoice_number'];
  if ($invoiceNumber === '') {
    if (invoice_numbering_type($profile) === 'manual') {
      manual_invoice_form('Ievadi rēķina numuru.', $data);
      return;
    }

    $invoiceNumber = invoice_generate_next_number($profile, $data['invoice_date']);
    $data['invoice_number'] = $invoiceNumber;
  }

  if (invoice_number_exists($invoiceNumber)) {
    manual_invoice_form('Šāds rēķina numurs jau eksistē.', $data);
    return;
  }

  $quantity = round((float)$data['quantity'], 2);
  $unitPrice = round((float)$data['unit_price'], 2);
  $vatRate = round((float)$data['vat_rate'], 2);
  $subtotal = round($quantity * $unitPrice, 2);
  $vatAmount = round($subtotal * $vatRate / 100, 2);
  $total = round($subtotal + $vatAmount, 2);

  $sellerVatNo = (int)$profile['show_seller_vat_no'] === 1 ? ($profile['seller_vat_no'] ?? null) : null;
  $sellerEmail = (int)$profile['show_seller_email'] === 1 ? ($profile['seller_email'] ?? null) : null;
  $sellerPhone = (int)$profile['show_seller_phone'] === 1 ? ($profile['seller_phone'] ?? null) : null;
  $sellerBankName = (int)$profile['show_seller_bank_name'] === 1 ? ($profile['seller_bank_name'] ?? null) : null;
  $sellerIban = (int)$profile['show_seller_iban'] === 1 ? ($profile['seller_iban'] ?? null) : null;
  $sellerSwift = (int)$profile['show_seller_swift'] === 1 ? ($profile['seller_swift'] ?? null) : null;
  $sellerLogoPath = (int)$profile['show_logo'] === 1 ? ($profile['logo_path'] ?? null) : null;

  try {
    $stmt = db()->prepare("
      INSERT INTO invoices (
        invoice_number, job_id, invoice_profile_id, invoice_date, due_date,
        seller_name, seller_registration_no, seller_vat_no, seller_address,
        seller_email, seller_phone, seller_bank_name, seller_iban, seller_swift,
        seller_logo_path,
        payer_type, payer_name, payer_registration_no, payer_vat_no, payer_address,
        payer_email, payer_phone,
        service_receiver_name, service_receiver_address, service_receiver_notes,
        service_description, quantity, unit, unit_price, vat_rate,
        subtotal, vat_amount, total, currency, notes
      ) VALUES (
        :invoice_number, :job_id, :invoice_profile_id, :invoice_date, :due_date,
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
      ':job_id' => null,
      ':invoice_profile_id' => (int)$profile['id'],
      ':invoice_date' => $data['invoice_date'],
      ':due_date' => ($data['due_date'] === '' ? null : $data['due_date']),
      ':seller_name' => $profile['seller_name'],
      ':seller_registration_no' => $profile['seller_registration_no'],
      ':seller_vat_no' => ($sellerVatNo === '' ? null : $sellerVatNo),
      ':seller_address' => $profile['seller_address'],
      ':seller_email' => ($sellerEmail === '' ? null : $sellerEmail),
      ':seller_phone' => ($sellerPhone === '' ? null : $sellerPhone),
      ':seller_bank_name' => ($sellerBankName === '' ? null : $sellerBankName),
      ':seller_iban' => ($sellerIban === '' ? null : $sellerIban),
      ':seller_swift' => ($sellerSwift === '' ? null : $sellerSwift),
      ':seller_logo_path' => ($sellerLogoPath === '' ? null : $sellerLogoPath),
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

    redirect(url('/invoice?id=' . (int)db()->lastInsertId()));
  } catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate')) {
      manual_invoice_form('Šāds rēķina numurs jau eksistē.', $data);
      return;
    }

    throw $e;
  }
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

  $profiles = invoice_profiles_fetch_all();
  $selectedProfile = invoice_select_profile($profiles);
  $today = date('Y-m-d');
  $invoiceNumber = $selectedProfile ? invoice_generate_next_number($selectedProfile, $today) : '';

  $form = [
    'job_id' => $jobId,
    'invoice_profile_id' => $selectedProfile ? (int)$selectedProfile['id'] : 0,
    'invoice_number' => $invoiceNumber,
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
    'notes' => ($selectedProfile && (int)$selectedProfile['show_invoice_note'] === 1) ? ($selectedProfile['invoice_note'] ?? '') : '',
  ];

  render('invoice_form.php', [
    'job' => $job,
    'profiles' => $profiles,
    'selectedProfile' => $selectedProfile,
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

  $profiles = invoice_profiles_fetch_all();
  $profile = invoice_select_profile($profiles);
  $profileId = $profile ? (int)$profile['id'] : 0;

  $data = [
    'job_id' => $jobId,
    'invoice_profile_id' => $profileId,
    'invoice_number' => trim($_POST['invoice_number'] ?? ''),
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

  if (!$profile) {
    render('invoice_form.php', [
      'job' => $job,
      'profiles' => $profiles,
      'selectedProfile' => null,
      'form' => $data,
      'error' => 'Vispirms izveido rēķina sūtītāja profilu.',
    ], 'Izveidot rēķinu');
    return;
  }

  if (trim($profile['seller_name'] ?? '') === '' || trim($profile['seller_registration_no'] ?? '') === '' || trim($profile['seller_address'] ?? '') === '') {
    render('invoice_form.php', [
      'job' => $job,
      'profiles' => $profiles,
      'selectedProfile' => $profile,
      'form' => $data,
      'error' => 'Izvēlētais rēķina sūtītāja profils nav pilnīgi aizpildīts.',
    ], 'Izveidot rēķinu');
    return;
  }

  $error = invoice_validate_form($data);
  if ($error) {
    render('invoice_form.php', [
      'job' => $job,
      'profiles' => $profiles,
      'selectedProfile' => $profile,
      'form' => $data,
      'error' => $error,
    ], 'Izveidot rēķinu');
    return;
  }

  $invoiceNumber = $data['invoice_number'];
  if ($invoiceNumber === '') {
    if (invoice_numbering_type($profile) === 'manual') {
      render('invoice_form.php', [
        'job' => $job,
        'profiles' => $profiles,
        'selectedProfile' => $profile,
        'form' => $data,
        'error' => 'Ievadi rēķina numuru.',
      ], 'Izveidot rēķinu');
      return;
    }

    $invoiceNumber = invoice_generate_next_number($profile, $data['invoice_date']);
    $data['invoice_number'] = $invoiceNumber;
  }

  if (invoice_number_exists($invoiceNumber)) {
    render('invoice_form.php', [
      'job' => $job,
      'profiles' => $profiles,
      'selectedProfile' => $profile,
      'form' => $data,
      'error' => 'Šāds rēķina numurs jau eksistē.',
    ], 'Izveidot rēķinu');
    return;
  }

  $quantity = round((float)$data['quantity'], 2);
  $unitPrice = round((float)$data['unit_price'], 2);
  $vatRate = round((float)$data['vat_rate'], 2);
  $subtotal = round($quantity * $unitPrice, 2);
  $vatAmount = round($subtotal * $vatRate / 100, 2);
  $total = round($subtotal + $vatAmount, 2);

  $sellerVatNo = (int)$profile['show_seller_vat_no'] === 1 ? ($profile['seller_vat_no'] ?? null) : null;
  $sellerEmail = (int)$profile['show_seller_email'] === 1 ? ($profile['seller_email'] ?? null) : null;
  $sellerPhone = (int)$profile['show_seller_phone'] === 1 ? ($profile['seller_phone'] ?? null) : null;
  $sellerBankName = (int)$profile['show_seller_bank_name'] === 1 ? ($profile['seller_bank_name'] ?? null) : null;
  $sellerIban = (int)$profile['show_seller_iban'] === 1 ? ($profile['seller_iban'] ?? null) : null;
  $sellerSwift = (int)$profile['show_seller_swift'] === 1 ? ($profile['seller_swift'] ?? null) : null;
  $sellerLogoPath = (int)$profile['show_logo'] === 1 ? ($profile['logo_path'] ?? null) : null;

  $pdo = db();
  for ($attempt = 0; $attempt < 3; $attempt++) {
    try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare("
        INSERT INTO invoices (
          invoice_number, job_id, invoice_profile_id, invoice_date, due_date,
          seller_name, seller_registration_no, seller_vat_no, seller_address,
          seller_email, seller_phone, seller_bank_name, seller_iban, seller_swift,
          seller_logo_path,
          payer_type, payer_name, payer_registration_no, payer_vat_no, payer_address,
          payer_email, payer_phone,
          service_receiver_name, service_receiver_address, service_receiver_notes,
          service_description, quantity, unit, unit_price, vat_rate,
          subtotal, vat_amount, total, currency, notes
        ) VALUES (
          :invoice_number, :job_id, :invoice_profile_id, :invoice_date, :due_date,
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
        ':invoice_profile_id' => $profileId,
        ':invoice_date' => $data['invoice_date'],
        ':due_date' => ($data['due_date'] === '' ? null : $data['due_date']),
        ':seller_name' => $profile['seller_name'],
        ':seller_registration_no' => $profile['seller_registration_no'],
        ':seller_vat_no' => ($sellerVatNo === '' ? null : $sellerVatNo),
        ':seller_address' => $profile['seller_address'],
        ':seller_email' => ($sellerEmail === '' ? null : $sellerEmail),
        ':seller_phone' => ($sellerPhone === '' ? null : $sellerPhone),
        ':seller_bank_name' => ($sellerBankName === '' ? null : $sellerBankName),
        ':seller_iban' => ($sellerIban === '' ? null : $sellerIban),
        ':seller_swift' => ($sellerSwift === '' ? null : $sellerSwift),
        ':seller_logo_path' => ($sellerLogoPath === '' ? null : $sellerLogoPath),
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

      if (str_contains($e->getMessage(), 'Duplicate')) {
        $existingAfterDuplicate = invoice_find_by_job($jobId);
        if ($existingAfterDuplicate) {
          redirect(url('/invoice?id=' . (int)$existingAfterDuplicate['id']));
        }

        render('invoice_form.php', [
          'job' => $job,
          'profiles' => $profiles,
          'selectedProfile' => $profile,
          'form' => $data,
          'error' => 'Šāds rēķina numurs jau eksistē.',
        ], 'Izveidot rēķinu');
        return;
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

function invoice_delete(): void
{
  $id = (int)($_POST['invoice_id'] ?? 0);
  if ($id <= 0) {
    redirect(url('/invoices'));
  }

  $invoice = invoice_fetch($id);
  if (!$invoice) {
    redirect(url('/invoices'));
  }

  $stmt = db()->prepare("DELETE FROM invoices WHERE id = :id");
  $stmt->execute([':id' => $id]);

  redirect(url('/invoices?deleted=1'));
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
