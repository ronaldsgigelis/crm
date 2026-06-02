<?php /** @var array|null $invoice */ ?>
<?php /** @var string|null $error */ ?>

<?php if (!$invoice): ?>
  <p class="error">Rēķins nav atrasts.</p>
<?php else: ?>
  <?php
    $invoiceHtml = invoice_render_document_html($invoice, [
      'full_document' => false,
      'logo_src' => invoice_public_logo_url($invoice['seller_logo_path'] ?? null),
    ]);
  ?>

  <div class="card">
    <h1>Rēķins</h1>

    <?php if (!empty($error)): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($invoice['job_id'])): ?>
      <p><a href="<?= url('/job?id=' . (int)$invoice['job_id']) ?>">Atvērt saistīto darbu</a></p>
    <?php endif; ?>

    <p><a href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">Lejupielādēt PDF</a></p>

    <form
      method="post"
      action="<?= url('/invoice-delete') ?>"
      onsubmit="return confirm('Vai tiešām dzēst šo rēķinu? Ja rēķins jau nosūtīts klientam vai izmantots grāmatvedībā, dzēšana nav ieteicama.');">
      <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
      <button type="submit">Dzēst rēķinu</button>
    </form>
  </div>

  <div class="card">
    <div style="background:#fff; max-width:800px; min-height:1000px; margin:0 auto; padding:32px; border:1px solid #ddd;">
      <?= $invoiceHtml ?>
    </div>
  </div>
<?php endif; ?>
