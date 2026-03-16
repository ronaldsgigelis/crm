<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

/**
 * Paņem visus meistarus no DB.
 * Rādām ID, vārdu, telefonu, e-pastu, token un created_at.
 */
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
 * Parāda meistaru sarakstu un formu jauna meistara pievienošanai.
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
 * Saglabā jaunu meistaru.
 */
function masters_store(): void
{
    // Ievades datu normalizācija:
    // trim() noņem liekās atstarpes
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // -----------------------------------------
    // Validācija: vārds obligāts
    // -----------------------------------------
    if ($name === '') {
        render('masters.php', [
            'masters' => masters_fetch_all(),
            'error' => 'Vārds ir obligāts.'
        ], 'Meistari');
        return;
    }

    // -----------------------------------------
    // Validācija: telefons
    // Atļaujam +, ciparus, atstarpes, iekavas un domuzīmes
    // Piemēri, kas der:
    // +37120000000
    // +371 20000000
    // (371) 20000000
    // -----------------------------------------
    if ($phone !== '') {
        if (!preg_match('/^[0-9+\s()-]+$/', $phone)) {
            render('masters.php', [
                'masters' => masters_fetch_all(),
                'error' => 'Tālrunī drīkst būt tikai cipari, +, atstarpes, iekavas un domuzīmes.'
            ], 'Meistari');
            return;
        }

        // Plus zīme drīkst būt tikai sākumā
        if (substr_count($phone, '+') > 1 || (strpos($phone, '+') !== false && strpos($phone, '+') !== 0)) {
            render('masters.php', [
                'masters' => masters_fetch_all(),
                'error' => 'Plus zīme telefonā drīkst būt tikai sākumā.'
            ], 'Meistari');
            return;
        }

        // Pārbaudām ciparu skaitu, ignorējot simbolus
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) < 5 || strlen($digits) > 25) {
            render('masters.php', [
                'masters' => masters_fetch_all(),
                'error' => 'Tālruņa numurs ir pārāk īss vai pārāk garš.'
            ], 'Meistari');
            return;
        }
    }

    // -----------------------------------------
    // Validācija: e-pasts
    // Ja e-pasts nav tukšs, tam jābūt korektā formātā
    // -----------------------------------------
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        render('masters.php', [
            'masters' => masters_fetch_all(),
            'error' => 'E-pasts nav korekts.'
        ], 'Meistari');
        return;
    }

    // Izveidojam unikālu token, lai meistars var atvērt savu "my-jobs" lapu
    $token = bin2hex(random_bytes(32));

    // Prepared statement:
    // drošāk pret SQL injection
    // tukšās vērtības pārveidojam uz NULL
    $stmt = db()->prepare("
        INSERT INTO masters (name, phone, email, token)
        VALUES (:name, :phone, :email, :token)
    ");

    $stmt->execute([
        ':name'  => $name,
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

    // Ja nav token, uzreiz 404
    if ($token === '') {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    // Atrodam meistaru pēc token
    $stmt = db()->prepare("SELECT id, name FROM masters WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $master = $stmt->fetch();

    // Ja tāds token neeksistē, 404
    if (!$master) {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    // Paņemam visus šim meistaram piešķirtos darbus
    $stmt = db()->prepare("
        SELECT j.*, c.name AS client_name
        FROM jobs j
        JOIN clients c ON c.id = j.client_id
        WHERE j.master_id = :mid
        ORDER BY (j.scheduled_at IS NULL) ASC, j.scheduled_at ASC, j.id DESC
    ");
    $stmt->execute([':mid' => (int)$master['id']]);
    $jobs = $stmt->fetchAll();

    // Renderējam skatu
    render('my_jobs.php', [
        'master' => $master,
        'jobs' => $jobs
    ], 'Mani darbi');
}
