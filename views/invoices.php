<?php /** @var array $invoices */ ?>
<?php /** @var array $summary */ ?>
<?php /** @var string $typeFilter */ ?>
<?php /** @var string $paymentFilter */ ?>
<?php /** @var bool $deleted */ ?>

<div class="invoices-page">
<div class="card section invoices-panel">
  <?php if (!empty($deleted)): ?>
    <p class="muted">Rēķins dzēsts.</p>
  <?php endif; ?>

  <div class="invoice-filter-stack">
  <div class="filter-bar invoice-filter-bar">
    <strong class="invoice-filter-label">Tips:</strong>
    <div class="filter-group invoice-filter-options">
      <a class="btn btn-secondary btn-small filter-link <?= ($typeFilter ?? 'all') === 'all' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=all&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'all' ? '<strong>Visi</strong>' : 'Visi' ?></a>
      <a class="btn btn-secondary btn-small filter-link <?= ($typeFilter ?? 'all') === 'crm' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=crm&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'crm' ? '<strong>CRM rēķini</strong>' : 'CRM rēķini' ?></a>
      <a class="btn btn-secondary btn-small filter-link <?= ($typeFilter ?? 'all') === 'manual' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=manual&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'manual' ? '<strong>Manuālie rēķini</strong>' : 'Manuālie rēķini' ?></a>
    </div>
  </div>

  <div class="filter-bar invoice-filter-bar">
    <strong class="invoice-filter-label">Apmaksa:</strong>
    <div class="filter-group invoice-filter-options">
      <a class="btn btn-secondary btn-small filter-link <?= ($paymentFilter ?? 'all') === 'all' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=all') ?>"><?= ($paymentFilter ?? 'all') === 'all' ? '<strong>Visi</strong>' : 'Visi' ?></a>
      <a class="btn btn-secondary btn-small filter-link <?= ($paymentFilter ?? 'all') === 'paid' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=paid') ?>"><?= ($paymentFilter ?? 'all') === 'paid' ? '<strong>Apmaksātie</strong>' : 'Apmaksātie' ?></a>
      <a class="btn btn-secondary btn-small filter-link <?= ($paymentFilter ?? 'all') === 'unpaid' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=unpaid') ?>"><?= ($paymentFilter ?? 'all') === 'unpaid' ? '<strong>Neapmaksātie</strong>' : 'Neapmaksātie' ?></a>
      <a class="btn btn-secondary btn-small filter-link <?= ($paymentFilter ?? 'all') === 'overdue' ? 'filter-link-active' : '' ?>" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=overdue') ?>"><?= ($paymentFilter ?? 'all') === 'overdue' ? '<strong>Kavētie</strong>' : 'Kavētie' ?></a>
    </div>
  </div>
  </div>

  <div class="summary-grid invoice-summary-grid">
    <div class="summary-card invoice-summary-card invoice-summary-card-primary">
      <div class="summary-card-label">Rēķinu skaits</div>
      <div class="summary-card-value"><?= (int)($summary['invoice_count'] ?? count($invoices)) ?></div>
    </div>
    <div class="summary-card invoice-summary-card invoice-summary-card-money">
      <div class="summary-card-label">Kopā</div>
      <div class="summary-card-value"><?= number_format((float)($summary['total_amount'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
    <div class="summary-card invoice-summary-card invoice-summary-card-paid">
      <div class="summary-card-label">Apmaksāts</div>
      <div class="summary-card-value"><?= number_format((float)($summary['paid_amount'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
    <div class="summary-card invoice-summary-card">
      <div class="summary-card-label">Nav apmaksāts</div>
      <div class="summary-card-value"><?= number_format((float)($summary['unpaid_amount'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
    <div class="summary-card invoice-summary-card invoice-summary-card-alert">
      <div class="summary-card-label">Kavēts</div>
      <div class="summary-card-value"><?= number_format((float)($summary['overdue_amount'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
  </div>

  <?php if (!$invoices): ?>
    <p class="empty-state">Rēķini šim filtram nav atrasti.</p>
  <?php else: ?>
  <div class="table-wrap invoice-table-wrap">
  <table class="table table-compact invoice-table">
    <thead>
      <tr>
        <th class="td-nowrap">Rēķins</th>
        <th class="td-nowrap">Datumi</th>
        <th>Maksātājs / darbs</th>
        <th class="td-num">Summa</th>
        <th class="td-nowrap">Apmaksa</th>
        <th class="td-nowrap">Darbības</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $invoice): ?>
        <?php
        $invoicePaymentLabel = invoice_payment_status_label($invoice);
        $invoicePaymentBadgeClass = 'badge-warning badge-payment-unpaid';
        if (strpos($invoicePaymentLabel, 'Apmaksāts') === 0) {
          $invoicePaymentBadgeClass = 'badge-success badge-payment-paid';
        } elseif ($invoicePaymentLabel === 'Kavēts') {
          $invoicePaymentBadgeClass = 'badge-danger badge-payment-overdue';
        }
        ?>
        <tr>
          <td class="td-nowrap invoice-number-cell">
            <div class="invoice-cell-stack">
              <div class="invoice-cell-main"><?= h($invoice['invoice_number']) ?></div>
              <div class="invoice-cell-meta"><span class="badge <?= empty($invoice['job_id']) ? 'badge-neutral badge-invoice-manual' : 'badge-info badge-invoice-crm' ?>"><?= empty($invoice['job_id']) ? 'Manuāls rēķins' : 'CRM rēķins' ?></span></div>
            </div>
          </td>
          <td class="td-date invoice-date-cell">
            <div class="invoice-cell-stack">
              <div class="invoice-cell-main"><?= h($invoice['invoice_date']) ?></div>
              <div class="invoice-cell-meta">Termiņš: <?= h($invoice['due_date'] ?? '') ?></div>
            </div>
          </td>
          <td class="invoice-party-cell">
            <div class="invoice-cell-stack">
              <div class="invoice-cell-main"><?= h($invoice['payer_name']) ?></div>
              <div class="invoice-cell-meta invoice-related-meta">
            <?php if (!empty($invoice['job_id'])): ?>
              <a class="btn btn-secondary btn-small invoice-related-link" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">
                #<?= (int)$invoice['job_id'] ?> <?= h($invoice['job_title'] ?? '') ?>
              </a>
            <?php else: ?>
              <span class="badge badge-neutral badge-invoice-manual">Manuāls rēķins</span>
            <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="td-num"><?= number_format((float)$invoice['total'], 2, '.', '') ?> <?= h($invoice['currency']) ?></td>
          <td class="td-nowrap invoice-payment-cell">
            <div class="invoice-payment-status"><span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span></div>
            <form class="table-actions-form invoice-payment-action" method="post" action="<?= url('/invoice-payment-status') ?>">
              <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
              <input type="hidden" name="source" value="list">
              <input type="hidden" name="type" value="<?= h($typeFilter ?? 'all') ?>">
              <input type="hidden" name="payment" value="<?= h($paymentFilter ?? 'all') ?>">
              <?php if (($invoice['payment_status'] ?? 'unpaid') === 'paid'): ?>
                <button class="btn btn-warning btn-small" type="submit" name="action" value="mark_unpaid">Atcelt apmaksu</button>
              <?php else: ?>
                <button class="btn btn-success btn-small" type="submit" name="action" value="mark_paid">Atzīmēt kā apmaksātu</button>
              <?php endif; ?>
            </form>
          </td>
          <td class="td-nowrap invoice-actions-cell">
            <div class="table-actions invoice-actions">
              <div class="invoice-routine-actions">
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Skatīt</a>
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">PDF</a>
              <?php if (!empty($invoice['job_id'])): ?>
                <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">Darbs</a>
              <?php endif; ?>
              </div>
              <div class="invoice-danger-actions">
              <form
                class="table-actions-form"
                method="post"
                action="<?= url('/invoice-delete') ?>"
                onsubmit="return confirm('Vai tiešām dzēst šo rēķinu? Ja rēķins jau nosūtīts klientam vai izmantots grāmatvedībā, dzēšana nav ieteicama.');">
                <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
                <button class="btn btn-danger btn-small" type="submit">Dzēst</button>
              </form>
              </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
</div>
