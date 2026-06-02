<?php

/** @var array|null $job */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var array $masters */ ?>
<?php /** @var array|null $invoice */ ?>

<!-- View: viena darba kartīte + rediģēšana + statusa maiņa -->

<?php if (!$job): ?>
  <p class="error">Darbs nav atrasts.</p>

<?php else: ?>

  <div class="card">
    <h2>#<?= (int)$job['id'] ?> - <?= h($job['title']) ?></h2>

    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <p><strong>Klients:</strong> <?= h($job['client_name']) ?></p>
    <p><strong>Statuss:</strong> <?= h(job_status_label($job['status'])) ?></p>
    <p><strong>Cena:</strong> <?= h((string)($job['price'] ?? '')) ?></p>
    <p><strong>Plānots:</strong> <?= h($job['scheduled_at'] ?? '') ?></p>
    <p><strong>Meistars:</strong> <?= h($job['master_name'] ?? '') ?></p>

    <p><strong>Apraksts:</strong><br>
      <?= nl2br(h($job['description'] ?? '')) ?>
    </p>

    <?php if (!empty($invoice)): ?>
      <p>
        <strong>Rēķins jau izveidots:</strong> <?= h($invoice['invoice_number']) ?>
        <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$invoice['id']) ?>" style="margin-left:8px;">Skatīt rēķinu</a>
        <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$invoice['id']) ?>" style="margin-left:8px;">Lejupielādēt PDF</a>
      </p>
    <?php else: ?>
      <p>
        <a class="btn btn-primary" href="<?= url('/invoice-create?job_id=' . (int)$job['id']) ?>">Ģenerēt rēķinu</a>
      </p>
    <?php endif; ?>

    <hr>

    <h3>Rediģēt darbu</h3>

    <form class="form" method="post" action="<?= url('/job-update') ?>">
      <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">

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
        <label>Apraksts</label>
      <textarea
        name="description"
        rows="4"
        placeholder="Papildu informācija par darbu"><?= h($job['description'] ?? '') ?></textarea>

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

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Saglabāt izmaiņas</button>
      </div>
    </form>

    <hr>

    <h3>Mainīt tikai statusu</h3>

    <form class="form form-section" method="post" action="<?= url('/job') ?>">
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

<?php endif; ?>
