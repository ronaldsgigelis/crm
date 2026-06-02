<?php /** @var array $invoices */ ?>
<?php /** @var bool $deleted */ ?>

<div class="card">
  <?php if (!empty($deleted)): ?>
    <p class="muted">Rēķins dzēsts.</p>
  <?php endif; ?>

  <p class="muted">Kopā: <?= count($invoices) ?></p>

  <table>
    <thead>
      <tr>
        <th>Rēķina Nr.</th>
        <th>Datums</th>
        <th>Apmaksas termiņš</th>
        <th>Maksātājs</th>
        <th>Saistītais darbs</th>
        <th>Summa</th>
        <th>Darbības</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $invoice): ?>
        <tr>
          <td><?= h($invoice['invoice_number']) ?></td>
          <td><?= h($invoice['invoice_date']) ?></td>
          <td><?= h($invoice['due_date'] ?? '') ?></td>
          <td><?= h($invoice['payer_name']) ?></td>
          <td>
            <?php if (!empty($invoice['job_id'])): ?>
              <a href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">
                #<?= (int)$invoice['job_id'] ?> <?= h($invoice['job_title'] ?? '') ?>
              </a>
            <?php else: ?>
              Manuāls rēķins
            <?php endif; ?>
          </td>
          <td><?= number_format((float)$invoice['total'], 2, '.', '') ?> <?= h($invoice['currency']) ?></td>
          <td>
            <a href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Skatīt</a>
            <a href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>" style="margin-left:8px;">PDF</a>
            <?php if (!empty($invoice['job_id'])): ?>
              <a href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>" style="margin-left:8px;">Darbs</a>
            <?php endif; ?>
            <form
              method="post"
              action="<?= url('/invoice-delete') ?>"
              style="display:inline;"
              onsubmit="return confirm('Vai tiešām dzēst šo rēķinu? Ja rēķins jau nosūtīts klientam vai izmantots grāmatvedībā, dzēšana nav ieteicama.');">
              <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
              <button type="submit">Dzēst</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
