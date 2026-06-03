<?php /** @var array|null $invoice */ ?>
<?php /** @var string|null $error */ ?>

<?php if (!$invoice): ?>
  <p class="error">Rēķins nav atrasts.</p>
<?php else: ?>
  <?php
    $invoicePaymentLabel = invoice_payment_status_label($invoice);
    $invoicePaymentBadgeClass = 'badge-warning badge-payment-unpaid';
    if (strpos($invoicePaymentLabel, 'Apmaksāts') === 0) {
      $invoicePaymentBadgeClass = 'badge-success badge-payment-paid';
    } elseif ($invoicePaymentLabel === 'Kavēts') {
      $invoicePaymentBadgeClass = 'badge-danger badge-payment-overdue';
    }

    $invoiceHtml = invoice_render_document_html($invoice, [
      'full_document' => false,
      'logo_src' => invoice_public_logo_url($invoice['seller_logo_path'] ?? null),
    ]);
  ?>

  <div class="card section">
    <div class="section-header">
      <h2 class="section-title">Rēķins</h2>
    </div>

    <?php if (!empty($error)): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <p><strong>Apmaksa:</strong> <span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span></p>

    <div class="section-actions">
      <?php if (!empty($invoice['job_id'])): ?>
        <a class="btn btn-secondary" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">Atvērt saistīto darbu</a>
      <?php endif; ?>

      <form class="table-actions-form" method="post" action="<?= url('/invoice-payment-status') ?>">
        <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
        <input type="hidden" name="source" value="show">
        <?php if (($invoice['payment_status'] ?? 'unpaid') === 'paid'): ?>
          <button class="btn btn-warning" type="submit" name="action" value="mark_unpaid">Atcelt apmaksu</button>
        <?php else: ?>
          <button class="btn btn-success" type="submit" name="action" value="mark_paid">Atzīmēt kā apmaksātu</button>
        <?php endif; ?>
      </form>

      <a class="btn btn-secondary" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">Lejupielādēt PDF</a>

      <form
        class="table-actions-form"
        method="post"
        action="<?= url('/invoice-delete') ?>"
        onsubmit="return confirm('Vai tiešām dzēst šo rēķinu? Ja rēķins jau nosūtīts klientam vai izmantots grāmatvedībā, dzēšana nav ieteicama.');">
        <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
        <button class="btn btn-danger" type="submit">Dzēst rēķinu</button>
      </form>
    </div>
  </div>

  <div class="card section invoice-preview-wrap">
    <div class="invoice-preview-page">
      <?= $invoiceHtml ?>
    </div>
  </div>
<?php endif; ?>
