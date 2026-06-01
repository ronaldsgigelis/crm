<?php /** @var array $profiles */ ?>
<?php /** @var array $templates */ ?>
<?php /** @var array|null $selectedTemplate */ ?>
<?php /** @var array|null $selectedProfile */ ?>
<?php /** @var array $form */ ?>
<?php /** @var string $generatedNumber */ ?>
<?php /** @var array|null $previewInvoice */ ?>
<?php /** @var bool $templateSaved */ ?>
<?php /** @var string|null $error */ ?>

<div class="card">
  <p><a href="<?= url('/invoices') ?>">Atpakaļ uz rēķiniem</a></p>

  <?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
  <?php endif; ?>

  <?php if (!$profiles): ?>
    <p class="error">Vispirms izveido rēķina sūtītāja profilu.</p>
    <p><a href="<?= url('/invoice-settings') ?>">Atvērt rēķinu iestatījumus</a></p>
  <?php else: ?>
    <?php if (!empty($templateSaved)): ?>
      <p class="muted">Sagatave saglabāta.</p>
    <?php endif; ?>

    <?php if ($selectedTemplate): ?>
      <p class="muted">Atvērta sagatave: <?= h($selectedTemplate['template_name']) ?></p>
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
        <button type="submit" name="action" value="preview_invoice">Priekšskatīt rēķinu</button>
        <button type="submit" name="action" value="save_template">Saglabāt sagatavi</button>
        <button type="submit" name="action" value="generate_invoice">Ģenerēt rēķinu</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php if (!empty($previewInvoice)): ?>
  <div class="card">
    <h2>Rēķina priekšskatījums</h2>

    <div class="row">
      <div>
        <?php if (!empty($previewInvoice['seller_logo_path'])): ?>
          <img src="<?= h(invoice_public_logo_url($previewInvoice['seller_logo_path'])) ?>" alt="Logo" style="max-width:160px;max-height:90px;">
        <?php endif; ?>

        <h3>Pārdevējs</h3>
        <p><strong>Nosaukums:</strong> <?= h($previewInvoice['seller_name']) ?></p>
        <p><strong>Reģ. Nr.:</strong> <?= h($previewInvoice['seller_registration_no']) ?></p>
        <?php if (!empty($previewInvoice['seller_vat_no'])): ?>
          <p><strong>PVN Nr.:</strong> <?= h($previewInvoice['seller_vat_no']) ?></p>
        <?php endif; ?>
        <p><strong>Juridiskā adrese:</strong><br><?= nl2br(h($previewInvoice['seller_address'])) ?></p>
        <?php if (!empty($previewInvoice['seller_email'])): ?><p><strong>E-pasts:</strong> <?= h($previewInvoice['seller_email']) ?></p><?php endif; ?>
        <?php if (!empty($previewInvoice['seller_phone'])): ?><p><strong>Telefons:</strong> <?= h($previewInvoice['seller_phone']) ?></p><?php endif; ?>
        <?php if (!empty($previewInvoice['seller_bank_name'])): ?><p><strong>Banka:</strong> <?= h($previewInvoice['seller_bank_name']) ?></p><?php endif; ?>
        <?php if (!empty($previewInvoice['seller_iban'])): ?><p><strong>IBAN:</strong> <?= h($previewInvoice['seller_iban']) ?></p><?php endif; ?>
        <?php if (!empty($previewInvoice['seller_swift'])): ?><p><strong>SWIFT/BIC:</strong> <?= h($previewInvoice['seller_swift']) ?></p><?php endif; ?>
      </div>

      <div>
        <h3>Rēķins <?= h($previewInvoice['invoice_number']) ?></h3>
        <p><strong>Datums:</strong> <?= h($previewInvoice['invoice_date']) ?></p>
        <p><strong>Apmaksas termiņš:</strong> <?= h($previewInvoice['due_date'] ?? '') ?></p>
        <p class="muted">Priekšskatījums nav saglabāts rēķinu vēsturē.</p>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Maksātājs</h3>
    <p><strong>Tips:</strong> <?= h(invoice_payer_type_label($previewInvoice['payer_type'])) ?></p>
    <p><strong>Nosaukums / vārds:</strong> <?= h($previewInvoice['payer_name']) ?></p>
    <?php if (!empty($previewInvoice['payer_registration_no'])): ?><p><strong>Reģ. Nr.:</strong> <?= h($previewInvoice['payer_registration_no']) ?></p><?php endif; ?>
    <?php if (!empty($previewInvoice['payer_vat_no'])): ?><p><strong>PVN Nr.:</strong> <?= h($previewInvoice['payer_vat_no']) ?></p><?php endif; ?>
    <?php if (!empty($previewInvoice['payer_address'])): ?><p><strong>Adrese:</strong><br><?= nl2br(h($previewInvoice['payer_address'])) ?></p><?php endif; ?>
    <?php if (!empty($previewInvoice['payer_email'])): ?><p><strong>E-pasts:</strong> <?= h($previewInvoice['payer_email']) ?></p><?php endif; ?>
    <?php if (!empty($previewInvoice['payer_phone'])): ?><p><strong>Telefons:</strong> <?= h($previewInvoice['payer_phone']) ?></p><?php endif; ?>
  </div>

  <?php if (!empty($previewInvoice['service_receiver_name']) || !empty($previewInvoice['service_receiver_address']) || !empty($previewInvoice['service_receiver_notes'])): ?>
    <div class="card">
      <h3>Pakalpojuma saņēmējs</h3>
      <?php if (!empty($previewInvoice['service_receiver_name'])): ?><p><strong>Nosaukums / vārds:</strong> <?= h($previewInvoice['service_receiver_name']) ?></p><?php endif; ?>
      <?php if (!empty($previewInvoice['service_receiver_address'])): ?><p><strong>Adrese:</strong><br><?= nl2br(h($previewInvoice['service_receiver_address'])) ?></p><?php endif; ?>
      <?php if (!empty($previewInvoice['service_receiver_notes'])): ?><p><strong>Piezīmes:</strong><br><?= nl2br(h($previewInvoice['service_receiver_notes'])) ?></p><?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="card">
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
          <td><?= nl2br(h($previewInvoice['service_description'])) ?></td>
          <td><?= h((string)$previewInvoice['quantity']) ?></td>
          <td><?= h($previewInvoice['unit']) ?></td>
          <td><?= number_format((float)$previewInvoice['unit_price'], 2, '.', '') ?></td>
          <td><?= number_format((float)$previewInvoice['vat_rate'], 2, '.', '') ?></td>
          <td><?= number_format((float)$previewInvoice['subtotal'], 2, '.', '') ?> <?= h($previewInvoice['currency']) ?></td>
        </tr>
      </tbody>
    </table>

    <p><strong>Summa bez PVN:</strong> <?= number_format((float)$previewInvoice['subtotal'], 2, '.', '') ?> <?= h($previewInvoice['currency']) ?></p>
    <p><strong>PVN:</strong> <?= number_format((float)$previewInvoice['vat_amount'], 2, '.', '') ?> <?= h($previewInvoice['currency']) ?></p>
    <p><strong>Apmaksai:</strong> <?= number_format((float)$previewInvoice['total'], 2, '.', '') ?> <?= h($previewInvoice['currency']) ?></p>

    <?php if (!empty($previewInvoice['notes'])): ?>
      <h3>Piezīmes</h3>
      <p><?= nl2br(h($previewInvoice['notes'])) ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Saglabātās sagataves</h2>
  <p class="muted">Kopā: <?= count($templates) ?></p>

  <?php if (!$templates): ?>
    <p class="muted">Sagatavju nav.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Nosaukums</th>
          <th>Maksātājs</th>
          <th>Pakalpojums</th>
          <th>Summa</th>
          <th>Darbības</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($templates as $template): ?>
          <tr>
            <td><?= h($template['template_name']) ?></td>
            <td><?= h($template['payer_name'] ?? '') ?></td>
            <td><?= h($template['service_description'] ?? '') ?></td>
            <td><?= number_format((float)$template['quantity'] * (float)$template['unit_price'], 2, '.', '') ?> <?= h($template['currency'] ?? 'EUR') ?></td>
            <td><a href="<?= url('/manual-invoice?template_id=' . (int)$template['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
