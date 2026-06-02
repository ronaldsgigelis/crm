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

<div class="row">

  <!-- Darba pievienošanas forma -->
  <div class="card">
    <h2>Pievienot darbu</h2>

    <!-- Kļūdas ziņa -->
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- POST forma uz /jobs -->
    <form class="form" method="post" action="<?= url('/jobs') ?>">

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
  <div class="card">
    <h2>Darbu saraksts</h2>

    <!-- Darbu skaits -->
    <p class="muted">Kopā: <?= count($jobs) ?></p>

    <!-- Filtri -->
    <form class="form form-section" method="get" action="<?= url('/jobs') ?>">

      <!-- Filtrs pēc meistara -->
      <div class="form-grid">
        <div class="field">
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
        <div class="field">
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
        <div class="field">
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
      <div class="form-actions">
        <label style="display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
          <span>Tikai bez meistara</span>
          <input
            type="checkbox"
            name="without_master"
            value="1"
            <?= !empty($withoutMaster) ? 'checked' : '' ?>>
        </label>

        <label style="display:inline-flex; align-items:center; gap:8px; white-space:nowrap; margin-left:10px;">
          <span>Bez rēķina</span>
          <input
            type="checkbox"
            name="without_invoice"
            value="1"
            <?= !empty($withoutInvoice) ? 'checked' : '' ?>>
        </label>

        <button class="btn btn-secondary btn-small" type="submit">Filtrēt</button>
        <a class="btn btn-secondary btn-small" href="<?= url('/jobs') ?>">Notīrīt</a>
      </div>
    </form>

    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th class="td-num">ID</th>
          <th>Klients</th>
          <th>Virsraksts</th>
          <th class="td-nowrap">Statuss</th>
          <th class="td-num">Cena</th>
          <th>Meistars</th>
          <th class="td-nowrap">Rēķins</th>
          <th class="td-nowrap">Atvērt</th>
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
            <td class="td-num"><?= (int)$j['id'] ?></td>
            <td><?= h($j['client_name']) ?></td>
            <td><?= h($j['title']) ?></td>

            <!-- Statusu lietotājam rādam latviski -->
            <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

            <td class="td-num"><?= h($j['price'] ?? '') ?></td>

            <!-- Meistars var būt tukšs -->
            <td><?= h($j['master_name'] ?? '') ?></td>

            <td class="td-nowrap">
              <?php if (!empty($j['invoice_id'])): ?>
                <div>Ir rēķins</div>
                <?php if (!empty($j['invoice_number'])): ?>
                  <div><?= h($j['invoice_number']) ?></div>
                <?php endif; ?>
                <div>
                  Apmaksa:
                  <?= h(invoice_payment_status_label([
                    'payment_status' => $j['invoice_payment_status'] ?? 'unpaid',
                    'paid_at' => $j['invoice_paid_at'] ?? null,
                    'due_date' => $j['invoice_due_date'] ?? null,
                  ])) ?>
                </div>
                <a class="btn btn-secondary btn-small" href="<?= url('/invoice?id=' . (int)$j['invoice_id']) ?>">Skatīt</a>
                <a class="btn btn-secondary btn-small" href="<?= url('/invoice-pdf?id=' . (int)$j['invoice_id']) ?>">PDF</a>
              <?php else: ?>
                <div>Nav rēķina</div>
                <a class="btn btn-primary btn-small" href="<?= url('/invoice-create?job_id=' . (int)$j['id']) ?>">Sagatavot rēķinu</a>
              <?php endif; ?>
            </td>

            <!-- Saite uz darba kartīti -->
            <td class="td-nowrap">
              <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

  </div>

</div>
