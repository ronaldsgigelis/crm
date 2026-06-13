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

function master_find(int $id): ?array
{
    $stmt = db()->prepare("SELECT id, name, phone, email, token, created_at FROM masters WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $master = $stmt->fetch();

    return $master ?: null;
}

function master_input_error(string $name, string $phone, string $email): ?string
{
    if ($name === '') {
        return 'Vārds ir obligāts.';
    }

    if ($phone !== '') {
        if (!preg_match('/^[0-9+\s()-]+$/', $phone)) {
            return 'Tālrunī drīkst būt tikai cipari, +, atstarpes, iekavas un domuzīmes.';
        }

        if (substr_count($phone, '+') > 1 || (strpos($phone, '+') !== false && strpos($phone, '+') !== 0)) {
            return 'Plus zīme telefonā drīkst būt tikai sākumā.';
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) < 5 || strlen($digits) > 25) {
            return 'Tālruņa numurs ir pārāk īss vai pārāk garš.';
        }
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'E-pasts nav korekts.';
    }

    return null;
}

/**
 * GET /masters
 * Parāda meistaru sarakstu un formu jauna meistara pievienošanai.
 */
function masters_index(): void
{
    $masters = masters_fetch_all();
    $editId = (int)($_GET['edit_id'] ?? 0);
    $editingMaster = $editId > 0 ? master_find($editId) : null;
    $error = null;
    $notice = null;

    if ($editId > 0 && !$editingMaster) {
        $error = 'Meistars nav atrasts.';
    } elseif (isset($_GET['delete_blocked'])) {
        $error = 'Meistaru nevar dzēst, jo tam ir piesaistīti darbi.';
    }

    if (isset($_GET['updated'])) {
        $notice = 'Meistara dati saglabāti.';
    } elseif (isset($_GET['deleted'])) {
        $notice = 'Meistars izdzēsts.';
    }

    render('masters.php', [
        'masters' => $masters,
        'editingMaster' => $editingMaster,
        'error' => $error,
        'notice' => $notice,
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

function master_update(): void
{
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $existingMaster = $id > 0 ? master_find($id) : null;
    if (!$existingMaster) {
        redirect(url('/masters'));
    }

    $error = master_input_error($name, $phone, $email);
    if ($error !== null) {
        render('masters.php', [
            'masters' => masters_fetch_all(),
            'editingMaster' => array_merge($existingMaster, [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
            ]),
            'error' => $error,
            'notice' => null,
        ], 'Meistari');
        return;
    }

    $stmt = db()->prepare("
        UPDATE masters
        SET name = :name, phone = :phone, email = :email
        WHERE id = :id
    ");
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':phone' => ($phone === '' ? null : $phone),
        ':email' => ($email === '' ? null : $email),
    ]);

    redirect(url('/masters?edit_id=' . $id . '&updated=1'));
}

function master_delete(): void
{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        redirect(url('/masters'));
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("SELECT id FROM masters WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            redirect(url('/masters'));
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE master_id = :id");
        $stmt->execute([':id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            redirect(url('/masters?edit_id=' . $id . '&delete_blocked=1'));
        }

        $stmt = $pdo->prepare("DELETE FROM masters WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    redirect(url('/masters?deleted=1'));
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
