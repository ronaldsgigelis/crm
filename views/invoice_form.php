<?php /** @var array $job */ ?>
<?php /** @var array $profiles */ ?>
<?php /** @var array|null $selectedProfile */ ?>
<?php /** @var array $form */ ?>
<?php /** @var string|null $error */ ?>

<div class="card">
  <?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
  <?php endif; ?>

  <?php if (!$profiles): ?>
    <p class="error">Vispirms izveido rēķina sūtītāja profilu.</p>
    <p><a href="<?= url('/invoice-settings') ?>">Atvērt rēķinu iestatījumus</a></p>
  <?php else: ?>
    <p class="muted">Darbs #<?= (int)$job['id'] ?>: <?= h($job['title']) ?></p>

    <form method="post" action="<?= url('/invoice-create') ?>">
      <input type="hidden" name="job_id" value="<?= (int)$form['job_id'] ?>">

      <input type="hidden" name="invoice_profile_id" value="<?= (int)($form['invoice_profile_id'] ?? 0) ?>">

      <p>
        <strong>Rēķina sūtītājs:</strong>
        <?= h(($selectedProfile['profile_name'] ?? '') . ' - ' . ($selectedProfile['seller_name'] ?? '')) ?>
      </p>

      <label>Rēķina numurs</label>
      <input
        name="invoice_number"
        required
        maxlength="30"
        placeholder="<?= ($selectedProfile && ($selectedProfile['numbering_type'] ?? '') === 'manual') ? 'Ievadi rēķina numuru' : '' ?>"
        value="<?= h($form['invoice_number'] ?? '') ?>">

      <h2>Maksātājs</h2>
      <div class="row">
        <div>
          <label>Tips</label>
          <select name="payer_type">
            <?php foreach ([
              'private_person' => invoice_payer_type_label('private_person'),
              'company' => invoice_payer_type_label('company'),
              'self_employed' => invoice_payer_type_label('self_employed'),
              'association' => invoice_payer_type_label('association'),
              'public_institution' => invoice_payer_type_label('public_institution'),
              'other' => invoice_payer_type_label('other'),
            ] as $value => $label): ?>
              <option value="<?= h($value) ?>" <?= ($form['payer_type'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Nosaukums / vārds*</label>
          <input name="payer_name" required maxlength="160" value="<?= h($form['payer_name'] ?? '') ?>">

          <label>Reģistrācijas numurs</label>
          <input name="payer_registration_no" maxlength="80" value="<?= h($form['payer_registration_no'] ?? '') ?>">

          <label>PVN numurs</label>
          <input name="payer_vat_no" maxlength="80" value="<?= h($form['payer_vat_no'] ?? '') ?>">
        </div>

        <div>
          <label>Adrese</label>
          <textarea name="payer_address" rows="3"><?= h($form['payer_address'] ?? '') ?></textarea>

          <label>E-pasts</label>
          <input name="payer_email" type="email" maxlength="160" value="<?= h($form['payer_email'] ?? '') ?>">

          <label>Telefons</label>
          <input name="payer_phone" maxlength="80" value="<?= h($form['payer_phone'] ?? '') ?>">
        </div>
      </div>

      <h2>Pakalpojuma saņēmējs</h2>
      <div class="row">
        <div>
          <label>Nosaukums / vārds</label>
          <input name="service_receiver_name" maxlength="160" value="<?= h($form['service_receiver_name'] ?? '') ?>">

          <label>Adrese</label>
          <textarea name="service_receiver_address" rows="3"><?= h($form['service_receiver_address'] ?? '') ?></textarea>
        </div>
        <div>
          <label>Piezīmes</label>
          <textarea name="service_receiver_notes" rows="5"><?= h($form['service_receiver_notes'] ?? '') ?></textarea>
        </div>
      </div>

      <h2>Rēķins</h2>
      <div class="row">
        <div>
          <label>Rēķina datums*</label>
          <input name="invoice_date" type="date" required value="<?= h($form['invoice_date'] ?? '') ?>">

          <label>Apmaksas termiņš</label>
          <input name="due_date" type="date" value="<?= h($form['due_date'] ?? '') ?>">

          <label>Pakalpojuma apraksts*</label>
          <textarea name="service_description" required rows="4"><?= h($form['service_description'] ?? '') ?></textarea>
        </div>
        <div>
          <label>Daudzums*</label>
          <input name="quantity" type="number" step="0.01" min="0.01" required value="<?= h((string)($form['quantity'] ?? '1')) ?>">

          <label>Mērvienība</label>
          <input name="unit" maxlength="30" value="<?= h($form['unit'] ?? 'gab.') ?>">

          <label>Cena par vienību*</label>
          <input name="unit_price" type="number" step="0.01" min="0" required value="<?= h((string)($form['unit_price'] ?? '0.00')) ?>">

          <label>PVN likme %</label>
          <input name="vat_rate" type="number" step="0.01" min="0" max="100" value="<?= h((string)($form['vat_rate'] ?? '0')) ?>">

          <label>Valūta</label>
          <input name="currency" maxlength="3" value="<?= h($form['currency'] ?? 'EUR') ?>">
        </div>
      </div>

      <label>Piezīmes</label>
      <textarea name="notes" rows="3"><?= h($form['notes'] ?? '') ?></textarea>

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt rēķinu</button>
        <a href="<?= url('/job?id=' . (int)$job['id']) ?>" style="margin-left:8px;">Atpakaļ uz darbu</a>
      </div>
    </form>
  <?php endif; ?>
</div>
