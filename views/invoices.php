<?php /** @var array $invoices */ ?>
<?php /** @var array $summary */ ?>
<?php /** @var string $typeFilter */ ?>
<?php /** @var string $paymentFilter */ ?>
<?php /** @var bool $deleted */ ?>

<div class="card">
  <?php if (!empty($deleted)): ?>
    <p class="muted">Rēķins dzēsts.</p>
  <?php endif; ?>

  <p>
    <strong>Tips:</strong>
    <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=all&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'all' ? '<strong>Visi</strong>' : 'Visi' ?></a>
    <span style="margin-left:8px;">
      <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=crm&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'crm' ? '<strong>CRM rēķini</strong>' : 'CRM rēķini' ?></a>
    </span>
    <span style="margin-left:8px;">
      <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=manual&payment=' . ($paymentFilter ?? 'all')) ?>"><?= ($typeFilter ?? 'all') === 'manual' ? '<strong>Manuālie rēķini</strong>' : 'Manuālie rēķini' ?></a>
    </span>
  </p>

  <p>
    <strong>Apmaksa:</strong>
    <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=all') ?>"><?= ($paymentFilter ?? 'all') === 'all' ? '<strong>Visi</strong>' : 'Visi' ?></a>
    <span style="margin-left:8px;">
      <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=paid') ?>"><?= ($paymentFilter ?? 'all') === 'paid' ? '<strong>Apmaksātie</strong>' : 'Apmaksātie' ?></a>
    </span>
    <span style="margin-left:8px;">
      <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=unpaid') ?>"><?= ($paymentFilter ?? 'all') === 'unpaid' ? '<strong>Neapmaksātie</strong>' : 'Neapmaksātie' ?></a>
    </span>
    <span style="margin-left:8px;">
      <a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=' . ($typeFilter ?? 'all') . '&payment=overdue') ?>"><?= ($paymentFilter ?? 'all') === 'overdue' ? '<strong>Kavētie</strong>' : 'Kavētie' ?></a>
    </span>
  </p>

  <div>
    <p><strong>Rēķinu skaits:</strong> <?= (int)($summary['invoice_count'] ?? count($invoices)) ?></p>
    <p><strong>Kopā:</strong> <?= number_format((float)($summary['total_amount'] ?? 0), 2, '.', '') ?> EUR</p>
    <p><strong>Apmaksāts:</strong> <?= number_format((float)($summary['paid_amount'] ?? 0), 2, '.', '') ?> EUR</p>
    <p><strong>Nav apmaksāts:</strong> <?= number_format((float)($summary['unpaid_amount'] ?? 0), 2, '.', '') ?> EUR</p>
    <p><strong>Kavēts:</strong> <?= number_format((float)($summary['overdue_amount'] ?? 0), 2, '.', '') ?> EUR</p>
  </div>

  <div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th class="td-nowrap">Rēķina Nr.</th>
        <th class="td-nowrap">Tips</th>
        <th class="td-nowrap">Datums</th>
        <th class="td-nowrap">Apmaksas termiņš</th>
        <th>Maksātājs</th>
        <th>Saistītais darbs</th>
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
          <td class="td-nowrap"><?= h($invoice['invoice_number']) ?></td>
          <td class="td-nowrap"><span class="badge <?= empty($invoice['job_id']) ? 'badge-neutral badge-invoice-manual' : 'badge-info badge-invoice-crm' ?>"><?= empty($invoice['job_id']) ? 'Manuāls rēķins' : 'CRM rēķins' ?></span></td>
          <td class="td-nowrap"><?= h($invoice['invoice_date']) ?></td>
          <td class="td-nowrap"><?= h($invoice['due_date'] ?? '') ?></td>
          <td><?= h($invoice['payer_name']) ?></td>
          <td>
            <?php if (!empty($invoice['job_id'])): ?>
              <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">
                #<?= (int)$invoice['job_id'] ?> <?= h($invoice['job_title'] ?? '') ?>
              </a>
            <?php else: ?>
              <span class="badge badge-neutral badge-invoice-manual">Manuāls rēķins</span>
            <?php endif; ?>
          </td>
          <td class="td-num"><?= number_format((float)$invoice['total'], 2, '.', '') ?> <?= h($invoice['currency']) ?></td>
          <td class="td-nowrap">
            <div><span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span></div>
            <form method="post" action="<?= url('/invoice-payment-status') ?>" style="display:inline;">
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
          <td class="td-nowrap">
            <div class="table-actions">
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Skatīt</a>
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>" style="margin-left:8px;">PDF</a>
              <?php if (!empty($invoice['job_id'])): ?>
                <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>" style="margin-left:8px;">Darbs</a>
              <?php endif; ?>
              <form
                method="post"
                action="<?= url('/invoice-delete') ?>"
                style="display:inline;"
                onsubmit="return confirm('Vai tiešām dzēst šo rēķinu? Ja rēķins jau nosūtīts klientam vai izmantots grāmatvedībā, dzēšana nav ieteicama.');">
                <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
                <button class="btn btn-danger btn-small" type="submit">Dzēst</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
