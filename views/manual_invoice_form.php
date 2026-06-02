<?php /** @var array $profiles */ ?>
<?php /** @var array $templates */ ?>
<?php /** @var array|null $selectedTemplate */ ?>
<?php /** @var array|null $selectedProfile */ ?>
<?php /** @var array $form */ ?>
<?php /** @var string $generatedNumber */ ?>
<?php /** @var array|null $previewInvoice */ ?>
<?php /** @var string $previewHtml */ ?>
<?php /** @var bool $templateSaved */ ?>
<?php /** @var string|null $error */ ?>

<div class="card">
  <p><a class="btn btn-secondary" href="<?= url('/invoices') ?>">Atpakaļ uz rēķiniem</a></p>

  <?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
  <?php endif; ?>

  <?php if (!$profiles): ?>
    <p class="error">Vispirms izveido rēķina sūtītāja profilu.</p>
    <p><a class="btn btn-secondary" href="<?= url('/invoice-settings') ?>">Atvērt rēķinu iestatījumus</a></p>
  <?php else: ?>
    <?php if (!empty($templateSaved)): ?>
      <p class="muted">Sagatave saglabāta.</p>
    <?php endif; ?>

    <?php if ($selectedTemplate): ?>
      <p class="muted">Atvērta sagatave: <?= h($selectedTemplate['template_name']) ?></p>
    <?php endif; ?>

    <?php if (!empty($previewInvoice)): ?>
      <h2>Rēķina priekšskatījums</h2>
      <p class="muted">Priekšskatījums nav saglabāts rēķinu vēsturē.</p>
      <div style="background:#fff; max-width:800px; min-height:1000px; margin:12px auto 18px; padding:32px; border:1px solid #ddd;">
        <?= invoice_render_document_html($previewInvoice, [
          'full_document' => false,
          'logo_src' => invoice_public_logo_url($previewInvoice['seller_logo_path'] ?? null),
        ]) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/manual-invoice') ?>">
      <input type="hidden" name="template_id" value="<?= (int)($form['template_id'] ?? 0) ?>">

      <h2>Rēķina dati</h2>

      <label>Pārdevēja profils</label>
      <select name="invoice_profile_id">
        <?php foreach ($profiles as $profile): ?>
          <option value="<?= (int)$profile['id'] ?>" <?= (int)($form['invoice_profile_id'] ?? 0) === (int)$profile['id'] ? 'selected' : '' ?>>
            <?= h($profile['profile_name'] . ' - ' . $profile['seller_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="muted">Profils, no kura tiks izrakstīts rēķins.</p>

      <p class="muted">
        <?php if ($selectedProfile && invoice_numbering_type($selectedProfile) === 'manual'): ?>
          Šim profilam rēķina numurs jāievada manuāli.
        <?php elseif ($generatedNumber !== ''): ?>
          Rēķina numurs tiks ģenerēts: <?= h($generatedNumber) ?>
        <?php endif; ?>
      </p>

      <label>Rēķina numurs</label>
      <input
        name="invoice_number"
        maxlength="30"
        placeholder="Atstāj tukšu automātiskai numerācijai"
        value="<?= h($form['invoice_number'] ?? '') ?>">

      <div class="row">
        <div>
          <label>Rēķina datums*</label>
          <input name="invoice_date" type="date" required value="<?= h($form['invoice_date'] ?? '') ?>">
        </div>
        <div>
          <label>Apmaksas termiņš</label>
          <input name="due_date" type="date" value="<?= h($form['due_date'] ?? '') ?>">
        </div>
      </div>

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

          <label>Reģistrācijas numurs / personas kods</label>
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

      <h2>Pakalpojums</h2>
      <div class="row">
        <div>
          <label>Pakalpojuma apraksts*</label>
          <textarea name="service_description" required rows="4"><?= h($form['service_description'] ?? '') ?></textarea>

          <label>Piezīmes rēķinā</label>
          <textarea name="notes" rows="3"><?= h($form['notes'] ?? '') ?></textarea>
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

      <h2>Sagatave</h2>
      <label>Sagataves nosaukums</label>
      <input name="template_name" maxlength="160" value="<?= h($form['template_name'] ?? '') ?>" placeholder="Piemēram, Rēķins RTU">

      <div style="margin-top:10px;">
        <button class="btn btn-secondary" type="submit" name="action" value="preview_invoice">Priekšskatīt rēķinu</button>
        <button class="btn btn-primary" type="submit" name="action" value="save_template">Saglabāt sagatavi</button>
        <button class="btn btn-primary" type="submit" name="action" value="generate_invoice">Ģenerēt rēķinu</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Saglabātās sagataves</h2>
  <p class="muted">Kopā: <?= count($templates) ?></p>

  <?php if (!$templates): ?>
    <p class="muted">Sagatavju nav.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Nosaukums</th>
          <th>Maksātājs</th>
          <th>Pakalpojums</th>
          <th class="td-num">Summa</th>
          <th class="td-nowrap">Darbības</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($templates as $template): ?>
          <tr>
            <td><?= h($template['template_name']) ?></td>
            <td><?= h($template['payer_name'] ?? '') ?></td>
            <td><?= h($template['service_description'] ?? '') ?></td>
            <td class="td-num"><?= number_format((float)$template['quantity'] * (float)$template['unit_price'], 2, '.', '') ?> <?= h($template['currency'] ?? 'EUR') ?></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/manual-invoice?template_id=' . (int)$template['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>
