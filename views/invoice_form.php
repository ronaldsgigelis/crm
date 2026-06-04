<?php /** @var array $job */ ?>
<?php /** @var array $profiles */ ?>
<?php /** @var array|null $selectedProfile */ ?>
<?php /** @var array $form */ ?>
<?php /** @var string|null $error */ ?>

<div class="invoice-form-page">
  <?php if ($error): ?>
    <div class="card section invoice-form-panel">
      <p class="error"><?= h($error) ?></p>
    </div>
  <?php endif; ?>

  <?php if (!$profiles): ?>
    <div class="card section invoice-form-panel">
      <p class="error">Vispirms izveido rēķina sūtītāja profilu.</p>
      <p class="section-actions"><a class="btn btn-secondary" href="<?= url('/invoice-settings') ?>">Atvērt rēķinu iestatījumus</a></p>
    </div>
  <?php else: ?>
    <form class="form invoice-form" method="post" action="<?= url('/invoice-create') ?>">
      <input type="hidden" name="job_id" value="<?= (int)$form['job_id'] ?>">

      <input type="hidden" name="invoice_profile_id" value="<?= (int)($form['invoice_profile_id'] ?? 0) ?>">

      <div class="invoice-form-grid">
        <div class="invoice-form-main">
          <div class="card section invoice-form-panel invoice-form-section">
            <div class="section-header">
              <div>
                <h2 class="section-title">Rēķina dati</h2>
                <p class="metadata">Numurs, datumi un valūta</p>
              </div>
            </div>

            <div class="form-grid invoice-form-fields">
              <div class="field">
                <label>Rēķina numurs</label>
                <input
                  name="invoice_number"
                  required
                  maxlength="30"
                  placeholder="<?= ($selectedProfile && ($selectedProfile['numbering_type'] ?? '') === 'manual') ? 'Ievadi rēķina numuru' : '' ?>"
                  value="<?= h($form['invoice_number'] ?? '') ?>">
              </div>

              <div class="field">
                <label>Rēķina datums*</label>
                <input name="invoice_date" type="date" required value="<?= h($form['invoice_date'] ?? '') ?>">
              </div>

              <div class="field">
                <label>Apmaksas termiņš</label>
                <input name="due_date" type="date" value="<?= h($form['due_date'] ?? '') ?>">
              </div>

              <div class="field">
                <label>Valūta</label>
                <input name="currency" maxlength="3" value="<?= h($form['currency'] ?? 'EUR') ?>">
              </div>
            </div>
          </div>

          <div class="card section invoice-form-panel invoice-form-section">
            <div class="section-header">
              <div>
                <h2 class="section-title">Maksātājs</h2>
                <p class="metadata">Klienta vai uzņēmuma rekvizīti</p>
              </div>
            </div>

            <div class="form-grid invoice-form-fields">
              <div>
                <div class="field">
                  <label>Tips</label>
                  <select name="payer_type">
                    <?php foreach ([
                      'private_person' => invoice_payer_type_label('private_person'),
                      'company' => invoice_payer_type_label('company'),
                      'self_employed' => invoice_payer_type_label('self_employed'),
                      'association' => invoice_payer_type_label('association'),
                      'public_institution' => invoice_payer_type_label('public_institution'),
                      'other' => invoice_payer_type_label('other'),
                    ] as $value => $label): ?>
                      <option value="<?= h($value) ?>" <?= ($form['payer_type'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="field">
                  <label>Nosaukums / vārds*</label>
                  <input name="payer_name" required maxlength="160" value="<?= h($form['payer_name'] ?? '') ?>">
                </div>

                <div class="field">
                  <label>Reģistrācijas numurs</label>
                  <input name="payer_registration_no" maxlength="80" value="<?= h($form['payer_registration_no'] ?? '') ?>">
                </div>

                <div class="field">
                  <label>PVN numurs</label>
                  <input name="payer_vat_no" maxlength="80" value="<?= h($form['payer_vat_no'] ?? '') ?>">
                </div>
              </div>

              <div>
                <div class="field">
                  <label>Adrese</label>
                  <textarea name="payer_address" rows="3"><?= h($form['payer_address'] ?? '') ?></textarea>
                </div>

                <div class="field">
                  <label>E-pasts</label>
                  <input name="payer_email" type="email" maxlength="160" value="<?= h($form['payer_email'] ?? '') ?>">
                </div>

                <div class="field">
                  <label>Telefons</label>
                  <input name="payer_phone" maxlength="80" value="<?= h($form['payer_phone'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>

          <div class="card section invoice-form-panel invoice-form-section">
            <div class="section-header">
              <div>
                <h2 class="section-title">Pakalpojuma saņēmējs</h2>
                <p class="metadata">Aizpildi, ja saņēmējs atšķiras no maksātāja</p>
              </div>
            </div>

            <div class="form-grid invoice-form-fields">
              <div>
                <div class="field">
                  <label>Nosaukums / vārds</label>
                  <input name="service_receiver_name" maxlength="160" value="<?= h($form['service_receiver_name'] ?? '') ?>">
                </div>

                <div class="field">
                  <label>Adrese</label>
                  <textarea name="service_receiver_address" rows="3"><?= h($form['service_receiver_address'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="field">
                <label>Piezīmes</label>
                <textarea name="service_receiver_notes" rows="5"><?= h($form['service_receiver_notes'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <div class="card section invoice-form-panel invoice-form-section">
            <div class="section-header">
              <div>
                <h2 class="section-title">Pakalpojums un summa</h2>
                <p class="metadata">Viena rēķina rinda ar daudzumu, cenu un PVN</p>
              </div>
            </div>

            <div class="invoice-items-wrap">
              <div class="form-grid invoice-form-fields invoice-items-table">
                <div class="field invoice-service-field">
                  <label>Pakalpojuma apraksts*</label>
                  <textarea name="service_description" required rows="4"><?= h($form['service_description'] ?? '') ?></textarea>
                </div>

                <div class="invoice-form-summary">
                  <div class="form-grid invoice-money-grid">
                    <div class="field">
                      <label>Daudzums*</label>
                      <input name="quantity" type="number" step="0.01" min="0.01" required value="<?= h((string)($form['quantity'] ?? '1')) ?>">
                    </div>

                    <div class="field">
                      <label>Mērvienība</label>
                      <input name="unit" maxlength="30" value="<?= h($form['unit'] ?? 'gab.') ?>">
                    </div>

                    <div class="field">
                      <label>Cena par vienību*</label>
                      <input name="unit_price" type="number" step="0.01" min="0" required value="<?= h((string)($form['unit_price'] ?? '0.00')) ?>">
                    </div>

                    <div class="field">
                      <label>PVN likme %</label>
                      <input name="vat_rate" type="number" step="0.01" min="0" max="100" value="<?= h((string)($form['vat_rate'] ?? '0')) ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="field">
              <label>Piezīmes</label>
              <textarea name="notes" rows="3"><?= h($form['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <aside class="invoice-form-side">
          <div class="card section invoice-form-panel invoice-job-context">
            <div class="section-header">
              <div>
                <h2 class="section-title">Saistītais darbs</h2>
                <p class="metadata">Rēķins tiks piesaistīts šim darbam</p>
              </div>
            </div>

            <div class="detail-grid invoice-context-grid">
              <div class="detail-item">
                <div class="detail-label">Darbs</div>
                <div class="detail-value">#<?= (int)$job['id'] ?> <?= h($job['title']) ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Rēķina sūtītājs</div>
                <div class="detail-value"><?= h(($selectedProfile['profile_name'] ?? '') . ' - ' . ($selectedProfile['seller_name'] ?? '')) ?></div>
              </div>
            </div>
          </div>

          <div class="card section invoice-form-panel invoice-form-actions">
            <div class="section-header">
              <h2 class="section-title">Darbības</h2>
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit">Saglabāt rēķinu</button>
              <a class="btn btn-secondary" href="<?= url('/job?id=' . (int)$job['id']) ?>">Atpakaļ uz darbu</a>
            </div>
          </div>
        </aside>
      </div>
    </form>
  <?php endif; ?>
</div>
