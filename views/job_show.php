<?php

/** @var array|null $job */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var array $masters */ ?>
<?php /** @var array|null $invoice */ ?>

<!-- View: viena darba kartīte + rediģēšana + statusa maiņa -->

<?php if (!$job): ?>
  <p class="error">Darbs nav atrasts.</p>

<?php else: ?>
  <?php
  $jobStatus = $job['status'] ?? '';
  $jobStatusBadgeClass = [
    'new' => 'badge-neutral badge-status-new',
    'in_progress' => 'badge-warning badge-status-in-progress',
    'done' => 'badge-success badge-status-done',
    'canceled' => 'badge-danger badge-status-canceled',
  ][$jobStatus] ?? 'badge-neutral badge-status-new';
  ?>

  <div class="job-detail-page">
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <div class="job-detail-grid">
      <div class="card section job-detail-main">
        <div class="section-header">
          <div>
            <h2 class="section-title">#<?= (int)$job['id'] ?> - <?= h($job['title']) ?></h2>
            <p class="metadata">Darba kartīte</p>
          </div>
          <span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($job['status'])) ?></span>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-label">Klients</div>
            <div class="detail-value"><?= h($job['client_name']) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Meistars</div>
            <div class="detail-value"><?= h($job['master_name'] ?? '') ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Cena</div>
            <div class="detail-value"><?= h((string)($job['price'] ?? '')) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Plānots</div>
            <div class="detail-value"><?= h($job['scheduled_at'] ?? '') ?></div>
          </div>
          <div class="detail-item detail-item-wide">
            <div class="detail-label">Apraksts</div>
            <div class="detail-value job-description">
              <?= ($job['description'] ?? '') !== '' ? nl2br(h($job['description'] ?? '')) : '<span class="muted">Apraksts nav norādīts.</span>' ?>
            </div>
          </div>
        </div>
      </div>

      <div class="job-detail-side">
        <div class="card section job-invoice-panel">
          <div class="section-header">
            <h3 class="section-title">Rēķins</h3>
          </div>

          <?php if (!empty($invoice)): ?>
            <div class="detail-item">
              <div class="detail-label">Rēķins jau izveidots</div>
              <div class="detail-value"><?= h($invoice['invoice_number']) ?></div>
            </div>
            <div class="section-actions job-detail-actions">
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>">Skatīt rēķinu</a>
              <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>">Lejupielādēt PDF</a>
            </div>
          <?php else: ?>
            <p class="muted">Rēķins šim darbam vēl nav izveidots.</p>
            <div class="section-actions job-detail-actions">
              <a class="btn btn-primary" href="<?= url('/invoice-create?job_id=' . (int)$job['id']) ?>">Ģenerēt rēķinu</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="card section job-status-panel">
          <div class="section-header">
            <h3 class="section-title">Mainīt tikai statusu</h3>
          </div>

          <form class="form form-section job-status-form" method="post" action="<?= url('/job') ?>">
            <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">

            <div class="field">
            <select name="status">
              <?php foreach (['new', 'in_progress', 'done', 'canceled'] as $s): ?>
                <option value="<?= h($s) ?>" <?= ($job['status'] ?? '') === $s ? 'selected' : '' ?>>
                  <?= h(job_status_label($s)) ?>
                </option>
              <?php endforeach; ?>
            </select>
            </div>

            <div class="form-actions">
              <button class="btn btn-primary" type="submit">Saglabāt statusu</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="card section job-edit-panel">
      <div class="section-header">
        <h3 class="section-title">Rediģēt darbu</h3>
      </div>

      <form class="form job-edit-form" method="post" action="<?= url('/job-update') ?>">
        <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">

        <div class="form-grid">
          <div class="field">
            <label>Virsraksts*</label>
          <input
            name="title"
            required
            maxlength="160"
            value="<?= h($job['title'] ?? '') ?>"
            placeholder="Piemēram, klavieru skaņošana">

          </div>

          <div class="field">
            <label>Statuss</label>
          <select name="status">
            <?php foreach (['new', 'in_progress', 'done', 'canceled'] as $s): ?>
              <option value="<?= h($s) ?>" <?= ($job['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= h(job_status_label($s)) ?>
              </option>
            <?php endforeach; ?>
          </select>

          </div>

          <div class="field">
            <label>Cena</label>
          <input
            name="price"
            type="number"
            step="0.01"
            min="0"
            value="<?= h(($job['price'] !== null && $job['price'] !== '') ? (string)$job['price'] : '') ?>"
            placeholder="0.00">
          </div>

          <div class="field">

          <label>Plānotais darba datums un laiks</label>
          <input
            name="scheduled_at"
            type="datetime-local"
            value="<?= !empty($job['scheduled_at']) ? h(date('Y-m-d\TH:i', strtotime($job['scheduled_at']))) : '' ?>">
          </div>

          <div class="field">

          <label>Meistars</label>
          <select name="master_id">
            <option value="0">-- nav piešķirts --</option>

            <?php foreach (($masters ?? []) as $m): ?>
              <option
                value="<?= (int)$m['id'] ?>"
                <?= ((int)($job['master_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                <?= h($m['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>

          </div>
        </div>

        <div class="field">
          <label>Apraksts</label>
        <textarea
          name="description"
          rows="4"
          placeholder="Papildu informācija par darbu"><?= h($job['description'] ?? '') ?></textarea>

        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Saglabāt izmaiņas</button>
        </div>
      </form>
    </div>
  </div>

<?php endif; ?>
