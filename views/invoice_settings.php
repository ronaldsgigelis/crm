<?php /** @var array $profiles */ ?>
<?php /** @var array $profile */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var bool $saved */ ?>

<div class="invoice-settings-page">
  <section class="card section invoice-settings-panel invoice-settings-context">
    <div class="section-header">
      <div>
        <p class="metadata">Rēķinu profili</p>
        <h2 class="section-title">Rēķinu iestatījumi</h2>
        <p class="muted">Pārvaldi pārdevēja datus, logo, rēķina attēlojumu un numerāciju.</p>
      </div>
      <div class="section-actions invoice-settings-toolbar">
        <form class="form invoice-settings-selector" method="get" action="<?= url('/invoice-settings') ?>">
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

        <a class="btn btn-secondary" href="<?= url('/invoice-settings') ?>">Jauns profils</a>
      </div>
    </div>

    <?php if (!empty($saved)): ?>
      <p class="muted invoice-settings-notice">Rēķinu iestatījumi saglabāti.</p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>
  </section>

  <section class="card section invoice-settings-panel invoice-settings-profile-panel">
    <div class="section-header">
      <div>
        <h2 class="section-title">Saglabātie profili</h2>
        <p class="muted">Kopā: <?= count($profiles) ?></p>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table table-compact invoice-settings-table">
        <thead>
          <tr>
            <th>Profila nosaukums</th>
            <th>Pārdevējs</th>
            <th class="td-nowrap">Reģ. Nr.</th>
            <th class="td-nowrap">Noklusētais</th>
            <th class="td-nowrap">Darbības</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($profiles as $p): ?>
            <tr class="<?= (int)($profile['id'] ?? 0) === (int)$p['id'] ? 'selected-row' : '' ?>">
              <td><?= h($p['profile_name']) ?></td>
              <td><?= h($p['seller_name']) ?></td>
              <td class="td-nowrap"><?= h($p['seller_registration_no']) ?></td>
              <td class="td-nowrap"><?= (int)$p['is_default'] === 1 ? 'Jā' : '' ?></td>
              <td class="td-nowrap">
                <div class="table-actions">
                  <a class="btn btn-secondary btn-small" href="<?= url('/invoice-settings?id=' . (int)$p['id']) ?>">Labot</a>
                  <?php if ((int)$p['is_default'] !== 1): ?>
                    <form class="table-actions-form" method="post" action="<?= url('/invoice-settings/default') ?>">
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                      <button class="btn btn-secondary btn-small" type="submit">Iestatīt kā noklusēto</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card section invoice-settings-panel">
    <div class="section-header">
      <div>
        <h2 class="section-title"><?= !empty($profile['id']) ? 'Labot profilu' : 'Jauns profils' ?></h2>
        <p class="muted">
          <?= !empty($profile['id']) ? 'Rediģē izvēlēto rēķina sūtītāja profilu.' : 'Izveido jaunu rēķina sūtītāja profilu.' ?>
        </p>
      </div>
    </div>

    <form class="form invoice-settings-form" method="post" action="<?= url('/invoice-settings') ?>" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= (int)($profile['id'] ?? 0) ?>">

      <div class="invoice-settings-grid">
        <div class="invoice-settings-main">
          <section class="invoice-settings-section">
            <div class="section-header">
              <h3 class="section-title">Profils un pārdevējs</h3>
            </div>
            <div class="invoice-settings-fields">
              <div class="profile-field field">
                <label>Profila nosaukums*</label>
                <input name="profile_name" required maxlength="255" value="<?= h($profile['profile_name'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>Uzņēmuma / sūtītāja nosaukums*</label>
                <input name="seller_name" required maxlength="255" value="<?= h($profile['seller_name'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>Reģistrācijas numurs*</label>
                <input name="seller_registration_no" required maxlength="100" value="<?= h($profile['seller_registration_no'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>PVN numurs</label>
                <input name="seller_vat_no" maxlength="100" value="<?= h($profile['seller_vat_no'] ?? '') ?>">
                <p class="muted">Aizpildi tikai tad, ja profils ir PVN maksātājs.</p>
              </div>
            </div>

            <div class="profile-field field">
              <label>Juridiskā adrese*</label>
              <textarea name="seller_address" required rows="4"><?= h($profile['seller_address'] ?? '') ?></textarea>
            </div>
          </section>

          <section class="invoice-settings-section">
            <div class="section-header">
              <h3 class="section-title">Kontakti</h3>
            </div>
            <div class="invoice-settings-fields">
              <div class="profile-field field">
                <label>E-pasts</label>
                <input name="seller_email" type="email" maxlength="255" value="<?= h($profile['seller_email'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>Telefons</label>
                <input name="seller_phone" maxlength="100" value="<?= h($profile['seller_phone'] ?? '') ?>">
              </div>
            </div>
          </section>

          <section class="invoice-settings-section">
            <div class="section-header">
              <h3 class="section-title">Banka un maksājumi</h3>
            </div>
            <div class="invoice-settings-fields">
              <div class="profile-field field">
                <label>Banka</label>
                <input name="seller_bank_name" maxlength="255" value="<?= h($profile['seller_bank_name'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>IBAN</label>
                <input name="seller_iban" maxlength="100" value="<?= h($profile['seller_iban'] ?? '') ?>">
              </div>

              <div class="profile-field field">
                <label>SWIFT/BIC</label>
                <input name="seller_swift" maxlength="100" value="<?= h($profile['seller_swift'] ?? '') ?>">
              </div>
            </div>
          </section>

          <section class="invoice-settings-section">
            <div class="section-header">
              <h3 class="section-title">Piezīme un logo</h3>
            </div>

            <div class="profile-field field">
              <label>Rēķina piezīme</label>
              <textarea name="invoice_note" rows="4" placeholder="Piemēram, PVN netiek piemērots"><?= h($profile['invoice_note'] ?? '') ?></textarea>
            </div>

            <div class="invoice-settings-logo-panel">
              <div class="profile-field field">
                <label>Logo (png, jpg, jpeg, webp)</label>
                <input name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
              </div>

              <?php if (!empty($profile['logo_path'])): ?>
                <div class="invoice-settings-logo-preview">
                  <p class="muted">Esošais logo:</p>
                  <img class="profile-logo-preview" src="<?= h(invoice_public_logo_url($profile['logo_path'])) ?>" alt="Logo">
                  <p class="muted">Lai nomainītu logo, izvēlies jaunu failu un saglabā.</p>
                </div>
              <?php endif; ?>
            </div>

            <label class="invoice-settings-check-row">
              <input type="checkbox" name="is_default" value="1" <?= !empty($profile['is_default']) ? 'checked' : '' ?>>
              <span>Noklusētais profils</span>
            </label>
          </section>
        </div>

        <aside class="invoice-settings-side">
          <section class="card section profile-options invoice-settings-options-panel">
            <div class="section-header">
              <h2 class="section-title">Rādīšana rēķinā</h2>
            </div>
            <p class="muted">Pamata pārdevēja dati rēķinā tiek rādīti vienmēr: nosaukums, reģistrācijas numurs un juridiskā adrese.</p>
            <p class="muted">Šeit vari izvēlēties tikai papildu laukus, ko rādīt rēķinā.</p>

            <div class="invoice-settings-option-list">
              <label><span>Rādīt logo rēķinā</span><input type="checkbox" name="show_logo" value="1" <?= !empty($profile['show_logo']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt PVN numuru</span><input type="checkbox" name="show_seller_vat_no" value="1" <?= !empty($profile['show_seller_vat_no']) ? 'checked' : '' ?>></label>
              <p class="muted">Atzīmē tikai tad, ja profils ir PVN maksātājs.</p>
              <label><span>Rādīt e-pastu</span><input type="checkbox" name="show_seller_email" value="1" <?= !empty($profile['show_seller_email']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt telefonu</span><input type="checkbox" name="show_seller_phone" value="1" <?= !empty($profile['show_seller_phone']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt banku</span><input type="checkbox" name="show_seller_bank_name" value="1" <?= !empty($profile['show_seller_bank_name']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt IBAN</span><input type="checkbox" name="show_seller_iban" value="1" <?= !empty($profile['show_seller_iban']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt SWIFT/BIC</span><input type="checkbox" name="show_seller_swift" value="1" <?= !empty($profile['show_seller_swift']) ? 'checked' : '' ?>></label>
              <label><span>Rādīt rēķina piezīmi</span><input type="checkbox" name="show_invoice_note" value="1" <?= !empty($profile['show_invoice_note']) ? 'checked' : '' ?>></label>
            </div>
          </section>

          <section class="card section profile-options invoice-settings-numbering-panel">
            <div class="section-header">
              <h2 class="section-title">Rēķina numura iestatījumi</h2>
            </div>

            <div class="profile-field field">
              <label>Numerācijas tips</label>
              <select name="numbering_type">
                <?php foreach ([
                  'yearly_sequence' => 'Gada secība',
                  'daily_sequence' => 'Datums + dienas secība',
                  'daily_short_year' => 'Datums + īsais gads',
                  'continuous_sequence' => 'Tikai kopējā secība',
                  'manual' => 'Manuāls numurs',
                ] as $value => $label): ?>
                  <option value="<?= h($value) ?>" <?= ($profile['numbering_type'] ?? 'yearly_sequence') === $value ? 'selected' : '' ?>>
                    <?= h($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="invoice-settings-number-fields">
              <div class="profile-field field">
                <label>Prefikss</label>
                <input name="numbering_prefix" maxlength="50" value="<?= h($profile['numbering_prefix'] ?? 'R') ?>">
              </div>

              <div class="profile-field field">
                <label>Atdalītājs</label>
                <input name="numbering_separator" maxlength="10" value="<?= h($profile['numbering_separator'] ?? '-') ?>">
              </div>

              <div class="profile-field field">
                <label>Ciparu skaits secībai</label>
                <input name="numbering_padding" type="number" min="1" max="10" value="<?= (int)($profile['numbering_padding'] ?? 4) ?>">
              </div>
            </div>

            <p class="muted invoice-settings-preview">
              <strong>Priekšskatījums:</strong>
              <span data-invoice-number-preview><?= h(invoice_number_preview($profile)) ?></span>
            </p>
          </section>

          <section class="card section invoice-settings-actions">
            <div class="section-header">
              <h2 class="section-title">Saglabāšana</h2>
            </div>
            <div class="profile-actions form-actions">
              <button class="btn btn-primary" type="submit">Saglabāt profilu</button>
              <?php if (!empty($profile['id'])): ?>
                <a class="btn btn-secondary" href="<?= url('/invoice-settings') ?>">Jauns profils</a>
              <?php endif; ?>
            </div>
          </section>
        </aside>
      </div>
    </form>
  </section>
</div>
