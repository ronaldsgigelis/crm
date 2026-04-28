<?php /** @var array|null $invoice */ ?>
<?php /** @var string|null $error */ ?>

<?php if (!$invoice): ?>
  <p class="error">Rēķins nav atrasts.</p>
<?php else: ?>
  <div class="card">
    <?php if (!empty($error)): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <div class="row">
      <div>
        <?php if (!empty($invoice['seller_logo_path'])): ?>
          <img src="<?= h(invoice_public_logo_url($invoice['seller_logo_path'])) ?>" alt="Logo" style="max-width:160px;max-height:90px;">
        <?php endif; ?>

        <h2>Pārdevējs</h2>
        <p><strong>Nosaukums:</strong> <?= h($invoice['seller_name']) ?></p>
        <p><strong>Reģ. Nr.:</strong> <?= h($invoice['seller_registration_no']) ?></p>
        <?php if (!empty($invoice['seller_vat_no'])): ?>
          <p><strong>PVN Nr.:</strong> <?= h($invoice['seller_vat_no']) ?></p>
        <?php endif; ?>
        <p><strong>Juridiskā adrese:</strong><br><?= nl2br(h($invoice['seller_address'])) ?></p>
        <?php if (!empty($invoice['seller_email'])): ?>
          <p><strong>E-pasts:</strong> <?= h($invoice['seller_email']) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice['seller_phone'])): ?>
          <p><strong>Telefons:</strong> <?= h($invoice['seller_phone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice['seller_bank_name'])): ?>
          <p><strong>Banka:</strong> <?= h($invoice['seller_bank_name']) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice['seller_iban'])): ?>
          <p><strong>IBAN:</strong> <?= h($invoice['seller_iban']) ?></p>
        <?php endif; ?>
        <?php if (!empty($invoice['seller_swift'])): ?>
          <p><strong>SWIFT/BIC:</strong> <?= h($invoice['seller_swift']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <h2>Rēķins <?= h($invoice['invoice_number']) ?></h2>
        <p><strong>Datums:</strong> <?= h($invoice['invoice_date']) ?></p>
        <p><strong>Apmaksas termiņš:</strong> <?= h($invoice['due_date'] ?? '') ?></p>
        <?php if (!empty($invoice['job_id'])): ?>
          <p><a href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">Atvērt saistīto darbu</a></p>
        <?php endif; ?>
        <p><a href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">Lejupielādēt PDF</a></p>
      </div>
    </div>
  </div>

  <div class="card">
    <h2>Maksātājs</h2>
    <p><strong>Tips:</strong> <?= h(invoice_payer_type_label($invoice['payer_type'])) ?></p>
    <p><strong>Nosaukums / vārds:</strong> <?= h($invoice['payer_name']) ?></p>
    <?php if (!empty($invoice['payer_registration_no'])): ?>
      <p><strong>Reģ. Nr.:</strong> <?= h($invoice['payer_registration_no']) ?></p>
    <?php endif; ?>
    <?php if (!empty($invoice['payer_vat_no'])): ?>
      <p><strong>PVN Nr.:</strong> <?= h($invoice['payer_vat_no']) ?></p>
    <?php endif; ?>
    <?php if (!empty($invoice['payer_address'])): ?>
      <p><strong>Adrese:</strong><br><?= nl2br(h($invoice['payer_address'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($invoice['payer_email'])): ?>
      <p><strong>E-pasts:</strong> <?= h($invoice['payer_email']) ?></p>
    <?php endif; ?>
    <?php if (!empty($invoice['payer_phone'])): ?>
      <p><strong>Telefons:</strong> <?= h($invoice['payer_phone']) ?></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($invoice['service_receiver_name']) || !empty($invoice['service_receiver_address']) || !empty($invoice['service_receiver_notes'])): ?>
    <div class="card">
      <h2>Pakalpojuma saņēmējs</h2>
      <?php if (!empty($invoice['service_receiver_name'])): ?>
        <p><strong>Nosaukums / vārds:</strong> <?= h($invoice['service_receiver_name']) ?></p>
      <?php endif; ?>
      <?php if (!empty($invoice['service_receiver_address'])): ?>
        <p><strong>Adrese:</strong><br><?= nl2br(h($invoice['service_receiver_address'])) ?></p>
      <?php endif; ?>
      <?php if (!empty($invoice['service_receiver_notes'])): ?>
        <p><strong>Piezīmes:</strong><br><?= nl2br(h($invoice['service_receiver_notes'])) ?></p>
      <?php endif; ?>
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
          <td><?= nl2br(h($invoice['service_description'])) ?></td>
          <td><?= h((string)$invoice['quantity']) ?></td>
          <td><?= h($invoice['unit']) ?></td>
          <td><?= number_format((float)$invoice['unit_price'], 2, '.', '') ?></td>
          <td><?= number_format((float)$invoice['vat_rate'], 2, '.', '') ?></td>
          <td><?= number_format((float)$invoice['subtotal'], 2, '.', '') ?> <?= h($invoice['currency']) ?></td>
        </tr>
      </tbody>
    </table>

    <p><strong>Summa bez PVN:</strong> <?= number_format((float)$invoice['subtotal'], 2, '.', '') ?> <?= h($invoice['currency']) ?></p>
    <p><strong>PVN:</strong> <?= number_format((float)$invoice['vat_amount'], 2, '.', '') ?> <?= h($invoice['currency']) ?></p>
    <p><strong>Apmaksai:</strong> <?= number_format((float)$invoice['total'], 2, '.', '') ?> <?= h($invoice['currency']) ?></p>

    <?php if (!empty($invoice['notes'])): ?>
      <h2>Piezīmes</h2>
      <p><?= nl2br(h($invoice['notes'])) ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>
