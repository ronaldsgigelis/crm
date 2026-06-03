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

  <div class="invoice-show-page">
    <?php if (!empty($error)): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <div class="invoice-show-grid">
      <div class="card section invoice-show-main invoice-meta-panel">
        <div class="section-header">
          <div>
            <h2 class="section-title">Rēķins <?= h($invoice['invoice_number'] ?? '') ?></h2>
            <p class="metadata">Rēķina informācija</p>
          </div>
          <span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span>
        </div>

        <div class="detail-grid invoice-show-detail-grid">
          <div class="detail-item">
            <div class="detail-label">Rēķina Nr.</div>
            <div class="detail-value"><?= h($invoice['invoice_number'] ?? '') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Datums</div>
            <div class="detail-value"><?= h($invoice['invoice_date'] ?? '') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Apmaksas termiņš</div>
            <div class="detail-value"><?= h($invoice['due_date'] ?? '') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Maksātājs</div>
            <div class="detail-value"><?= h($invoice['payer_name'] ?? '') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Summa</div>
            <div class="detail-value"><?= number_format((float)($invoice['total'] ?? 0), 2, '.', '') ?> <?= h($invoice['currency'] ?? '') ?></div>
          </div>
        </div>
      </div>

      <div class="invoice-show-side">
        <div class="card section invoice-payment-panel">
          <div class="section-header">
            <h3 class="section-title">Apmaksa</h3>
          </div>
          <p class="invoice-payment-status"><span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span></p>

          <form class="table-actions-form invoice-payment-form" method="post" action="<?= url('/invoice-payment-status') ?>">
            <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
            <input type="hidden" name="source" value="show">
            <?php if (($invoice['payment_status'] ?? 'unpaid') === 'paid'): ?>
              <button class="btn btn-warning" type="submit" name="action" value="mark_unpaid">Atcelt apmaksu</button>
            <?php else: ?>
              <button class="btn btn-success" type="submit" name="action" value="mark_paid">Atzīmēt kā apmaksātu</button>
            <?php endif; ?>
          </form>
        </div>

        <div class="card section invoice-actions-panel">
          <div class="section-header">
            <h3 class="section-title">Darbības</h3>
          </div>
          <div class="section-actions invoice-show-actions">
            <?php if (!empty($invoice['job_id'])): ?>
              <a class="btn btn-secondary" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">Atvērt saistīto darbu</a>
            <?php endif; ?>

            <a class="btn btn-secondary" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">Lejupielādēt PDF</a>
          </div>
        </div>

        <div class="card section invoice-danger-panel">
          <div class="section-header">
            <h3 class="section-title">Bīstamā darbība</h3>
          </div>
          <p class="muted">Dzēšana ir atsevišķi nodalīta no ikdienas darbībām.</p>
          <div class="invoice-danger-actions">
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
      </div>
    </div>

    <div class="card section invoice-preview-wrap invoice-preview-panel">
      <div class="section-header">
        <div>
          <h3 class="section-title">Rēķina priekšskatījums</h3>
          <p class="metadata">Priekšskatījums saglabā rēķina dokumenta izolēto izkārtojumu.</p>
        </div>
      </div>
      <div class="invoice-preview-page">
        <?= $invoiceHtml ?>
      </div>
    </div>
  </div>
<?php endif; ?>
