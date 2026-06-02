<?php /** @var array $summary */ ?>
<?php /** @var array $todayJobs */ ?>
<?php /** @var array $upcomingJobs */ ?>
<?php /** @var array $recentInvoices */ ?>

<div class="card section">
  <div class="section-header">
    <h2 class="section-title">CRM pārskats</h2>
  </div>

  <div class="summary-grid">
    <div class="summary-card">
      <div class="summary-card-label">Kopā klienti</div>
      <div class="summary-card-value"><?= (int)($summary['clients_count'] ?? 0) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Kopā darbi</div>
      <div class="summary-card-value"><?= (int)($summary['jobs_count'] ?? 0) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Darbi bez rēķina</div>
      <div class="summary-card-value"><?= (int)($summary['jobs_without_invoice'] ?? 0) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Neapmaksāti rēķini</div>
      <div class="summary-card-value"><?= (int)($summary['unpaid_invoices'] ?? 0) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Kavēti rēķini</div>
      <div class="summary-card-value"><?= (int)($summary['overdue_invoices'] ?? 0) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Izrakstīts šomēnes</div>
      <div class="summary-card-value"><?= number_format((float)($summary['issued_this_month'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
    <div class="summary-card">
      <div class="summary-card-label">Apmaksāts šomēnes</div>
      <div class="summary-card-value"><?= number_format((float)($summary['paid_this_month'] ?? 0), 2, '.', '') ?> EUR</div>
    </div>
  </div>
</div>

<div class="card section">
  <div class="section-header">
    <h2 class="section-title">Ātrās saites</h2>
  </div>
  <p class="section-actions"><a class="btn btn-secondary btn-small" href="<?= url('/jobs?without_invoice=1') ?>">Darbi bez rēķina</a></p>
  <p class="section-actions"><a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=all&payment=unpaid') ?>">Neapmaksāti rēķini</a></p>
  <p class="section-actions"><a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=all&payment=overdue') ?>">Kavēti rēķini</a></p>
  <p class="section-actions"><a class="btn btn-secondary btn-small" href="<?= url('/invoices') ?>">Visi rēķini</a></p>
  <p class="section-actions"><a class="btn btn-secondary btn-small" href="<?= url('/manual-invoice') ?>">Manuāls rēķins</a></p>
</div>

<div class="card section">
  <div class="section-header">
    <h2 class="section-title">Šodienas darbi</h2>
  </div>
  <?php if (!$todayJobs): ?>
    <p class="muted">Šodien nav ieplānotu darbu.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th class="td-nowrap">Datums/laiks</th>
          <th>Klients</th>
          <th>Darbs</th>
          <th class="td-nowrap">Statuss</th>
          <th class="td-nowrap">Saite</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todayJobs as $job): ?>
          <?php
          $jobStatus = $job['status'] ?? '';
          $jobStatusBadgeClass = [
            'new' => 'badge-neutral badge-status-new',
            'in_progress' => 'badge-warning badge-status-in-progress',
            'done' => 'badge-success badge-status-done',
            'canceled' => 'badge-danger badge-status-canceled',
          ][$jobStatus] ?? 'badge-neutral badge-status-new';
          ?>
          <tr>
            <td class="td-nowrap"><?= h($job['scheduled_at'] ?? '') ?></td>
            <td><?= h($job['client_name'] ?? '') ?></td>
            <td><?= h($job['title'] ?? '') ?></td>
            <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(dashboard_job_status_label($job['status'] ?? '')) ?></span></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$job['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<div class="card section">
  <div class="section-header">
    <h2 class="section-title">Tuvākie darbi</h2>
  </div>
  <?php if (!$upcomingJobs): ?>
    <p class="muted">Nav tuvāko ieplānoto darbu.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th class="td-nowrap">Datums/laiks</th>
          <th>Klients</th>
          <th>Darbs</th>
          <th class="td-nowrap">Statuss</th>
          <th class="td-nowrap">Saite</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($upcomingJobs as $job): ?>
          <?php
          $jobStatus = $job['status'] ?? '';
          $jobStatusBadgeClass = [
            'new' => 'badge-neutral badge-status-new',
            'in_progress' => 'badge-warning badge-status-in-progress',
            'done' => 'badge-success badge-status-done',
            'canceled' => 'badge-danger badge-status-canceled',
          ][$jobStatus] ?? 'badge-neutral badge-status-new';
          ?>
          <tr>
            <td class="td-nowrap"><?= h($job['scheduled_at'] ?? '') ?></td>
            <td><?= h($job['client_name'] ?? '') ?></td>
            <td><?= h($job['title'] ?? '') ?></td>
            <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(dashboard_job_status_label($job['status'] ?? '')) ?></span></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$job['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<div class="card section">
  <div class="section-header">
    <h2 class="section-title">Pēdējie rēķini</h2>
  </div>
  <?php if (!$recentInvoices): ?>
    <p class="muted">Rēķinu vēl nav.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th class="td-nowrap">Rēķins</th>
          <th>Maksātājs</th>
          <th class="td-num">Summa</th>
          <th class="td-nowrap">Apmaksa</th>
          <th class="td-nowrap">Saite</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentInvoices as $invoice): ?>
          <?php
          $invoicePaymentLabel = dashboard_invoice_payment_status_label($invoice);
          $invoicePaymentBadgeClass = 'badge-warning badge-payment-unpaid';
          if (strpos($invoicePaymentLabel, 'Apmaksāts') === 0) {
            $invoicePaymentBadgeClass = 'badge-success badge-payment-paid';
          } elseif ($invoicePaymentLabel === 'Kavēts') {
            $invoicePaymentBadgeClass = 'badge-danger badge-payment-overdue';
          }
          ?>
          <tr>
            <td class="td-nowrap"><?= h($invoice['invoice_number'] ?? '') ?></td>
            <td><?= h($invoice['payer_name'] ?? '') ?></td>
            <td class="td-num"><?= number_format((float)($invoice['total'] ?? 0), 2, '.', '') ?> <?= h($invoice['currency'] ?? 'EUR') ?></td>
            <td class="td-nowrap"><span class="badge <?= h($invoicePaymentBadgeClass) ?>"><?= h($invoicePaymentLabel) ?></span></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>
