<?php /** @var array $profiles */ ?>
<?php /** @var array $profile */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var bool $saved */ ?>

<style>
  .profile-toolbar {
    display: flex;
    gap: 12px;
    align-items: end;
    flex-wrap: wrap;
  }

  .profile-toolbar form {
    min-width: 280px;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(340px, 1fr);
    gap: 18px;
    align-items: start;
  }

  .profile-field {
    margin-bottom: 12px;
  }

  .profile-field label {
    display: block;
    margin-bottom: 4px;
    font-weight: bold;
  }

  .profile-field input,
  .profile-field textarea,
  .profile-field select {
    box-sizing: border-box;
    max-width: 640px;
  }

  .profile-options label {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    column-gap: 12px;
    margin: 10px 0;
    max-width: 360px;
  }

  .profile-options label span {
    min-width: 0;
    white-space: normal;
  }

  .profile-options input[type="checkbox"] {
    justify-self: end;
  }

  .profile-actions {
    margin-top: 14px;
  }

  .selected-row {
    background: #f8fbff;
  }

  @media (max-width: 800px) {
    .profile-form-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="card">
  <?php if (!empty($saved)): ?>
    <p class="muted">Rēķinu iestatījumi saglabāti.</p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
  <?php endif; ?>

  <div class="profile-toolbar">
    <form method="get" action="<?= url('/invoice-settings') ?>">
      <label>Izvēlēties profilu</label>
      <select name="id" onchange="this.form.submit()">
        <option value="">-- jauns profils --</option>
        <?php foreach ($profiles as $p): ?>
          <option value="<?= (int)$p['id'] ?>" <?= (int)($profile['id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
            <?= h($p['profile_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <p>
      <a href="<?= url('/invoice-settings') ?>">Jauns profils</a>
    </p>
  </div>
</div>

<div class="card">
  <h2>Saglabātie profili</h2>
  <p class="muted">Kopā: <?= count($profiles) ?></p>

  <table>
    <thead>
      <tr>
        <th>Profila nosaukums</th>
        <th>Pārdevējs</th>
        <th>Reģ. Nr.</th>
        <th>Noklusētais</th>
        <th>Darbības</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($profiles as $p): ?>
        <tr class="<?= (int)($profile['id'] ?? 0) === (int)$p['id'] ? 'selected-row' : '' ?>">
          <td><?= h($p['profile_name']) ?></td>
          <td><?= h($p['seller_name']) ?></td>
          <td><?= h($p['seller_registration_no']) ?></td>
          <td><?= (int)$p['is_default'] === 1 ? 'Jā' : '' ?></td>
          <td>
            <a href="<?= url('/invoice-settings?id=' . (int)$p['id']) ?>">Labot</a>
            <?php if ((int)$p['is_default'] !== 1): ?>
              <form method="post" action="<?= url('/invoice-settings/default') ?>" style="display:inline;">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit">Iestatīt kā noklusēto</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2><?= !empty($profile['id']) ? 'Labot profilu' : 'Jauns profils' ?></h2>
  <p class="muted">
    <?= !empty($profile['id']) ? 'Rediģē izvēlēto rēķina sūtītāja profilu.' : 'Izveido jaunu rēķina sūtītāja profilu.' ?>
  </p>

  <form method="post" action="<?= url('/invoice-settings') ?>" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int)($profile['id'] ?? 0) ?>">

    <div class="profile-form-grid">
      <div>
        <div class="profile-field">
          <label>Profila nosaukums*</label>
          <input name="profile_name" required maxlength="255" value="<?= h($profile['profile_name'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>Uzņēmuma / sūtītāja nosaukums*</label>
          <input name="seller_name" required maxlength="255" value="<?= h($profile['seller_name'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>Reģistrācijas numurs*</label>
          <input name="seller_registration_no" required maxlength="100" value="<?= h($profile['seller_registration_no'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>PVN numurs</label>
          <input name="seller_vat_no" maxlength="100" value="<?= h($profile['seller_vat_no'] ?? '') ?>">
          <p class="muted">Aizpildi tikai tad, ja profils ir PVN maksātājs.</p>
        </div>

        <div class="profile-field">
          <label>Juridiskā adrese*</label>
          <textarea name="seller_address" required rows="4"><?= h($profile['seller_address'] ?? '') ?></textarea>
        </div>

        <div class="profile-field">
          <label>E-pasts</label>
          <input name="seller_email" type="email" maxlength="255" value="<?= h($profile['seller_email'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>Telefons</label>
          <input name="seller_phone" maxlength="100" value="<?= h($profile['seller_phone'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>Banka</label>
          <input name="seller_bank_name" maxlength="255" value="<?= h($profile['seller_bank_name'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>IBAN</label>
          <input name="seller_iban" maxlength="100" value="<?= h($profile['seller_iban'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>SWIFT/BIC</label>
          <input name="seller_swift" maxlength="100" value="<?= h($profile['seller_swift'] ?? '') ?>">
        </div>

        <div class="profile-field">
          <label>Rēķina piezīme</label>
          <textarea name="invoice_note" rows="4" placeholder="Piemēram, PVN netiek piemērots"><?= h($profile['invoice_note'] ?? '') ?></textarea>
        </div>

        <div class="profile-field">
          <label>Logo (png, jpg, jpeg, webp)</label>
          <input name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">

          <?php if (!empty($profile['logo_path'])): ?>
            <p class="muted">Esošais logo:</p>
            <img src="<?= h(invoice_public_logo_url($profile['logo_path'])) ?>" alt="Logo" style="max-width:160px;max-height:90px;">
            <p class="muted">Lai nomainītu logo, izvēlies jaunu failu un saglabā.</p>
          <?php endif; ?>
        </div>

        <div class="profile-field">
          <label>
            <input type="checkbox" name="is_default" value="1" <?= !empty($profile['is_default']) ? 'checked' : '' ?>>
            Noklusētais profils
          </label>
        </div>
      </div>

      <div class="card profile-options">
        <h2>Rādīšana rēķinā</h2>
        <p class="muted">Pamata pārdevēja dati rēķinā tiek rādīti vienmēr: nosaukums, reģistrācijas numurs un juridiskā adrese.</p>
        <p class="muted">Šeit vari izvēlēties tikai papildu laukus, ko rādīt rēķinā.</p>
        <label><span>Rādīt logo rēķinā</span><input type="checkbox" name="show_logo" value="1" <?= !empty($profile['show_logo']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt PVN numuru</span><input type="checkbox" name="show_seller_vat_no" value="1" <?= !empty($profile['show_seller_vat_no']) ? 'checked' : '' ?>></label>
        <p class="muted">Atzīmē tikai tad, ja profils ir PVN maksātājs.</p>
        <label><span>Rādīt e-pastu</span><input type="checkbox" name="show_seller_email" value="1" <?= !empty($profile['show_seller_email']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt telefonu</span><input type="checkbox" name="show_seller_phone" value="1" <?= !empty($profile['show_seller_phone']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt banku</span><input type="checkbox" name="show_seller_bank_name" value="1" <?= !empty($profile['show_seller_bank_name']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt IBAN</span><input type="checkbox" name="show_seller_iban" value="1" <?= !empty($profile['show_seller_iban']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt SWIFT/BIC</span><input type="checkbox" name="show_seller_swift" value="1" <?= !empty($profile['show_seller_swift']) ? 'checked' : '' ?>></label>
        <label><span>Rādīt rēķina piezīmi</span><input type="checkbox" name="show_invoice_note" value="1" <?= !empty($profile['show_invoice_note']) ? 'checked' : '' ?>></label>

        <hr>

        <h2>Rēķina numura iestatījumi</h2>

        <div class="profile-field">
          <label>Numerācijas tips</label>
          <select name="numbering_type">
            <?php foreach ([
              'yearly_sequence' => 'Gada secība',
              'daily_sequence' => 'Datums + dienas secība',
              'continuous_sequence' => 'Tikai kopējā secība',
              'manual' => 'Manuāls numurs',
            ] as $value => $label): ?>
              <option value="<?= h($value) ?>" <?= ($profile['numbering_type'] ?? 'yearly_sequence') === $value ? 'selected' : '' ?>>
                <?= h($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="profile-field">
          <label>Prefikss</label>
          <input name="numbering_prefix" maxlength="50" value="<?= h($profile['numbering_prefix'] ?? 'R') ?>">
        </div>

        <div class="profile-field">
          <label>Atdalītājs</label>
          <input name="numbering_separator" maxlength="10" value="<?= h($profile['numbering_separator'] ?? '-') ?>">
        </div>

        <div class="profile-field">
          <label>Ciparu skaits secībai</label>
          <input name="numbering_padding" type="number" min="1" max="10" value="<?= (int)($profile['numbering_padding'] ?? 4) ?>">
        </div>

        <p class="muted"><strong>Priekšskatījums:</strong> <?= h(invoice_number_preview($profile)) ?></p>
      </div>
    </div>

    <div class="profile-actions">
      <button type="submit">Saglabāt profilu</button>
      <?php if (!empty($profile['id'])): ?>
        <a href="<?= url('/invoice-settings') ?>" style="margin-left:8px;">Jauns profils</a>
      <?php endif; ?>
    </div>
  </form>
</div>
