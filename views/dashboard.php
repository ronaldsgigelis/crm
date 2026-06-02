<?php /** @var array $summary */ ?>
<?php /** @var array $todayJobs */ ?>
<?php /** @var array $upcomingJobs */ ?>
<?php /** @var array $recentInvoices */ ?>

<div class="card">
  <h2>CRM pārskats</h2>

  <div class="table-wrap">
    <table class="table">
      <tbody>
        <tr>
          <th>Kopā klienti</th>
          <td class="td-num"><?= (int)($summary['clients_count'] ?? 0) ?></td>
        </tr>
        <tr>
          <th>Kopā darbi</th>
          <td class="td-num"><?= (int)($summary['jobs_count'] ?? 0) ?></td>
        </tr>
        <tr>
          <th>Darbi bez rēķina</th>
          <td class="td-num"><?= (int)($summary['jobs_without_invoice'] ?? 0) ?></td>
        </tr>
        <tr>
          <th>Neapmaksāti rēķini</th>
          <td class="td-num"><?= (int)($summary['unpaid_invoices'] ?? 0) ?></td>
        </tr>
        <tr>
          <th>Kavēti rēķini</th>
          <td class="td-num"><?= (int)($summary['overdue_invoices'] ?? 0) ?></td>
        </tr>
        <tr>
          <th>Izrakstīts šomēnes</th>
          <td class="td-num"><?= number_format((float)($summary['issued_this_month'] ?? 0), 2, '.', '') ?> EUR</td>
        </tr>
        <tr>
          <th>Apmaksāts šomēnes</th>
          <td class="td-num"><?= number_format((float)($summary['paid_this_month'] ?? 0), 2, '.', '') ?> EUR</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h2>Ātrās saites</h2>
  <p><a class="btn btn-secondary btn-small" href="<?= url('/jobs?without_invoice=1') ?>">Darbi bez rēķina</a></p>
  <p><a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=all&payment=unpaid') ?>">Neapmaksāti rēķini</a></p>
  <p><a class="btn btn-secondary btn-small" href="<?= url('/invoices?type=all&payment=overdue') ?>">Kavēti rēķini</a></p>
  <p><a class="btn btn-secondary btn-small" href="<?= url('/invoices') ?>">Visi rēķini</a></p>
  <p><a class="btn btn-secondary btn-small" href="<?= url('/manual-invoice') ?>">Manuāls rēķins</a></p>
</div>

<div class="card">
  <h2>Šodienas darbi</h2>
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
          <tr>
            <td class="td-nowrap"><?= h($job['scheduled_at'] ?? '') ?></td>
            <td><?= h($job['client_name'] ?? '') ?></td>
            <td><?= h($job['title'] ?? '') ?></td>
            <td class="td-nowrap"><?= h(dashboard_job_status_label($job['status'] ?? '')) ?></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$job['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Tuvākie darbi</h2>
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
          <tr>
            <td class="td-nowrap"><?= h($job['scheduled_at'] ?? '') ?></td>
            <td><?= h($job['client_name'] ?? '') ?></td>
            <td><?= h($job['title'] ?? '') ?></td>
            <td class="td-nowrap"><?= h(dashboard_job_status_label($job['status'] ?? '')) ?></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$job['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Pēdējie rēķini</h2>
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
          <tr>
            <td class="td-nowrap"><?= h($invoice['invoice_number'] ?? '') ?></td>
            <td><?= h($invoice['payer_name'] ?? '') ?></td>
            <td class="td-num"><?= number_format((float)($invoice['total'] ?? 0), 2, '.', '') ?> <?= h($invoice['currency'] ?? 'EUR') ?></td>
            <td class="td-nowrap"><?= h(dashboard_invoice_payment_status_label($invoice)) ?></td>
            <td class="td-nowrap"><a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Atvērt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>
