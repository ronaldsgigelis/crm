<?php /** @var array $settings */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var bool $saved */ ?>

<div class="card">
  <?php if (!empty($saved)): ?>
    <p class="muted">Rēķinu iestatījumi saglabāti.</p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
  <?php endif; ?>

  <form method="post" action="<?= url('/invoice-settings') ?>" enctype="multipart/form-data">
    <div class="row">
      <div>
        <label>Uzņēmuma nosaukums*</label>
        <input name="seller_name" required maxlength="160" value="<?= h($settings['seller_name'] ?? '') ?>">

        <label>Reģistrācijas numurs*</label>
        <input name="seller_registration_no" required maxlength="80" value="<?= h($settings['seller_registration_no'] ?? '') ?>">

        <label>PVN numurs</label>
        <input name="seller_vat_no" maxlength="80" value="<?= h($settings['seller_vat_no'] ?? '') ?>">

        <label>Juridiskā adrese*</label>
        <textarea name="seller_address" required rows="3"><?= h($settings['seller_address'] ?? '') ?></textarea>

        <label>E-pasts</label>
        <input name="seller_email" type="email" maxlength="160" value="<?= h($settings['seller_email'] ?? '') ?>">

        <label>Telefons</label>
        <input name="seller_phone" maxlength="80" value="<?= h($settings['seller_phone'] ?? '') ?>">
      </div>

      <div>
        <label>Banka</label>
        <input name="seller_bank_name" maxlength="160" value="<?= h($settings['seller_bank_name'] ?? '') ?>">

        <label>IBAN</label>
        <input name="seller_iban" maxlength="80" value="<?= h($settings['seller_iban'] ?? '') ?>">

        <label>SWIFT/BIC</label>
        <input name="seller_swift" maxlength="40" value="<?= h($settings['seller_swift'] ?? '') ?>">

        <label>Rēķina piezīme</label>
        <textarea name="invoice_note" rows="3" placeholder="Piemēram, PVN netiek piemērots"><?= h($settings['invoice_note'] ?? '') ?></textarea>

        <label>Logo (png, jpg, jpeg, webp)</label>
        <input name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">

        <?php if (!empty($settings['logo_path'])): ?>
          <p class="muted">Esošais logo:</p>
          <img src="<?= h(invoice_public_logo_url($settings['logo_path'])) ?>" alt="Logo" style="max-width:160px;max-height:90px;">
          <p class="muted">Lai nomainītu logo, izvēlies jaunu failu un saglabā.</p>
        <?php endif; ?>
      </div>
    </div>

    <div style="margin-top:10px;">
      <button type="submit">Saglabāt</button>
    </div>
  </form>
</div>
