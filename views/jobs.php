<?php

/** @var array $jobs */ ?>
<?php /** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>

<?php /** @var array $masters */ ?>
<?php /** @var int $masterId */ ?>
<?php /** @var string $statusFilter */ ?>
<?php /** @var int $clientId */ ?>
<?php /** @var bool $withoutMaster */ ?>
<?php /** @var bool $withoutInvoice */ ?>

<!-- View: darbu pievienošana + darbu saraksts -->

<div class="row jobs-page">

  <!-- Darba pievienošanas forma -->
  <div class="card section jobs-create-panel">
    <div class="section-header">
      <h2 class="section-title">Pievienot darbu</h2>
    </div>

    <!-- Kļūdas ziņa -->
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- POST forma uz /jobs -->
    <form class="form jobs-create-form" method="post" action="<?= url('/jobs') ?>">

      <!-- Klienta izvēle -->
      <div class="field">
        <label>Klients*</label>
        <select name="client_id" required>
          <option value="">-- izvēlies --</option>
          <?php foreach ($clients as $c): ?>
            <option value="<?= (int)$c['id'] ?>">
              <?= h($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Darba virsraksts -->
      <div class="field">
        <label>Virsraksts*</label>
        <input
          name="title"
          required
          maxlength="160"
          placeholder="Piemēram, klavieru skaņošana">
      </div>

      <!-- Darba apraksts -->
      <div class="field">
        <label>Apraksts</label>
        <textarea
          name="description"
          rows="4"
          placeholder="Papildu informācija par darbu"></textarea>
      </div>

      <!-- Darba statuss -->
      <div class="field">
        <label>Statuss</label>
        <select name="status">
          <option value="new">Jauns</option>
          <option value="in_progress">Procesā</option>
          <option value="done">Pabeigts</option>
          <option value="canceled">Atcelts</option>
        </select>
      </div>

      <!-- Cena -->
      <div class="field">
        <label>Cena</label>
        <input
          name="price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00">
      </div>

      <!-- Plānotais datums/laiks -->
      <div class="field">
        <label>Plānotais darba datums un laiks</label>
        <input name="scheduled_at" type="datetime-local">
      </div>

      <!-- Meistaram piešķirt darbu -->
      <div class="field">
        <label>Meistars</label>
        <select name="master_id">
          <option value="0">-- nav piešķirts --</option>
          <?php foreach (($masters ?? []) as $m): ?>
            <option value="<?= (int)$m['id'] ?>"><?= h($m['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <!-- Darbu saraksts -->
  <div class="card section jobs-panel">
    <div class="section-header">
      <h2 class="section-title">Darbu saraksts</h2>
    </div>

    <!-- Darbu skaits -->
    <p class="muted jobs-count">Kopā: <?= count($jobs) ?></p>

    <!-- Filtri -->
    <form class="form form-section filter-bar jobs-filter-bar" method="get" action="<?= url('/jobs') ?>">

      <!-- Filtrs pēc meistara -->
      <div class="form-grid filter-group">
        <div class="field filter-group">
          <label>Meistars:</label>
          <select name="master_id">
            <option value="0">Visi</option>
            <?php foreach (($masters ?? []) as $m): ?>
              <option
                value="<?= (int)$m['id'] ?>"
                <?= ((int)($masterId ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                <?= h($m['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filtrs pēc statusa -->
        <div class="field filter-group">
          <label>Statuss:</label>
          <select name="status">
            <option value="">Visi</option>
            <option value="new" <?= ($statusFilter ?? '') === 'new' ? 'selected' : '' ?>>Jauns</option>
            <option value="in_progress" <?= ($statusFilter ?? '') === 'in_progress' ? 'selected' : '' ?>>Procesā</option>
            <option value="done" <?= ($statusFilter ?? '') === 'done' ? 'selected' : '' ?>>Pabeigts</option>
            <option value="canceled" <?= ($statusFilter ?? '') === 'canceled' ? 'selected' : '' ?>>Atcelts</option>
          </select>
        </div>

        <!-- Filtrs pēc klienta -->
        <div class="field filter-group">
          <label>Klients:</label>
          <select name="client_id">
            <option value="0">Visi</option>
            <?php foreach (($clients ?? []) as $c): ?>
              <option
                value="<?= (int)$c['id'] ?>"
                <?= ((int)($clientId ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                <?= h($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Checkbox filtrs: tikai darbi bez piešķirta meistara -->
      <div class="form-actions filter-group">
        <label class="checkbox-inline">
          <span>Tikai bez meistara</span>
          <input
            type="checkbox"
            name="without_master"
            value="1"
            <?= !empty($withoutMaster) ? 'checked' : '' ?>>
        </label>

        <label class="checkbox-inline">
          <span>Bez rēķina</span>
          <input
            type="checkbox"
            name="without_invoice"
            value="1"
            <?= !empty($withoutInvoice) ? 'checked' : '' ?>>
        </label>

        <button class="btn btn-secondary btn-small" type="submit">Filtrēt</button>
        <a class="btn btn-secondary btn-small filter-link" href="<?= url('/jobs') ?>">Notīrīt</a>
      </div>
    </form>

    <?php if (!$jobs): ?>
      <p class="empty-state">Darbi šiem filtriem nav atrasti.</p>
    <?php else: ?>
    <div class="table-wrap jobs-table-wrap">
    <table class="table table-compact jobs-table">
      <thead>
        <tr>
          <th>Darbs</th>
          <th>Klients / meistars</th>
          <th class="td-nowrap">Statuss</th>
          <th class="td-num">Cena</th>
          <th class="td-nowrap">Rēķins</th>
          <th class="td-nowrap">Darbības</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $j): ?>
          <?php
          $jobStatus = $j['status'] ?? '';
          $jobStatusBadgeClass = [
            'new' => 'badge-neutral badge-status-new',
            'in_progress' => 'badge-warning badge-status-in-progress',
            'done' => 'badge-success badge-status-done',
            'canceled' => 'badge-danger badge-status-canceled',
          ][$jobStatus] ?? 'badge-neutral badge-status-new';
          ?>
          <tr>
            <td class="job-title-cell">
              <div class="job-cell-stack">
                <div class="job-cell-main"><?= h($j['title']) ?></div>
                <div class="job-cell-meta">ID: <?= (int)$j['id'] ?></div>
                <div class="job-cell-meta">Plānots: <?= h($j['scheduled_at'] ?? '') !== '' ? h($j['scheduled_at']) : 'nav norādīts' ?></div>
              </div>
            </td>
            <td class="job-party-cell">
              <div class="job-cell-stack">
                <div class="job-cell-main"><?= h($j['client_name']) ?></div>
                <div class="job-cell-meta">Meistars: <?= h($j['master_name'] ?? '') ?></div>
              </div>
            </td>

            <!-- Statusu lietotājam rādam latviski -->
            <td class="td-nowrap job-status-cell"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

            <td class="td-num"><?= h($j['price'] ?? '') ?></td>

            <td class="td-nowrap job-invoice-cell">
              <?php if (!empty($j['invoice_id'])): ?>
                <div class="job-cell-stack">
                <div class="job-cell-main">Ir rēķins</div>
                <?php if (!empty($j['invoice_number'])): ?>
                  <div class="job-cell-meta"><?= h($j['invoice_number']) ?></div>
                <?php endif; ?>
                <div class="job-cell-meta">
                  Apmaksa:
                  <?= h(invoice_payment_status_label([
                    'payment_status' => $j['invoice_payment_status'] ?? 'unpaid',
                    'paid_at' => $j['invoice_paid_at'] ?? null,
                    'due_date' => $j['invoice_due_date'] ?? null,
                  ])) ?>
                </div>
                <div class="table-actions job-invoice-actions">
                <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$j['invoice_id']) ?>">Skatīt</a>
                <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$j['invoice_id']) ?>">PDF</a>
                </div>
                </div>
              <?php else: ?>
                <div class="job-cell-stack">
                <div class="job-cell-main">Nav rēķina</div>
                <div class="table-actions job-invoice-actions">
                <a class="btn btn-primary btn-small" href="<?= url('/invoice-create?job_id=' . (int)$j['id']) ?>">Sagatavot rēķinu</a>
                </div>
                </div>
              <?php endif; ?>
            </td>

            <!-- Saite uz darba kartīti -->
            <td class="td-nowrap job-actions-cell">
              <div class="table-actions job-actions">
              <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>

  </div>

</div>
