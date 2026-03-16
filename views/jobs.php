<?php

/** @var array $jobs */ ?>
<?php /** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>

<?php /** @var array $masters */ ?>
<?php /** @var int $masterId */ ?>
<?php /** @var string $statusFilter */ ?>
<?php /** @var int $clientId */ ?>
<?php /** @var bool $withoutMaster */ ?>

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
    <form method="post" action="<?= url('/jobs') ?>">

      <!-- Klienta izvēle -->
      <label>Klients*</label>
      <select name="client_id" required>
        <option value="">-- izvēlies --</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int)$c['id'] ?>">
            <?= h($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Darba virsraksts -->
      <label>Virsraksts*</label>
      <input
        name="title"
        required
        maxlength="160"
        placeholder="Piemēram, klavieru skaņošana">

      <!-- Darba apraksts -->
      <label>Apraksts</label>
      <textarea
        name="description"
        rows="4"
        placeholder="Papildu informācija par darbu"></textarea>

      <!-- Darba statuss -->
      <label>Statuss</label>
      <select name="status">
        <option value="new">Jauns</option>
        <option value="in_progress">Procesā</option>
        <option value="done">Pabeigts</option>
        <option value="canceled">Atcelts</option>
      </select>

      <!-- Cena -->
      <label>Cena</label>
      <input
        name="price"
        type="number"
        step="0.01"
        min="0"
        placeholder="0.00">

      <!-- Plānotais datums/laiks -->
      <label>Plānotais darba datums un laiks</label>
      <input name="scheduled_at" type="datetime-local">

      <!-- Meistaram piešķirt darbu -->
      <label>Meistars</label>
      <select name="master_id">
        <option value="0">-- nav piešķirts --</option>
        <?php foreach (($masters ?? []) as $m): ?>
          <option value="<?= (int)$m['id'] ?>"><?= h($m['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <!-- Darbu saraksts -->
  <div class="card">
    <h2>Darbu saraksts</h2>

    <!-- Darbu skaits -->
    <p class="muted">Kopā: <?= count($jobs) ?></p>

    <!-- Filtri -->
    <form method="get" action="<?= url('/jobs') ?>" style="margin:10px 0;">

      <!-- Filtrs pēc meistara -->
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

      <!-- Filtrs pēc statusa -->
      <label style="margin-left:10px;">Statuss:</label>
      <select name="status">
        <option value="">Visi</option>
        <option value="new" <?= ($statusFilter ?? '') === 'new' ? 'selected' : '' ?>>Jauns</option>
        <option value="in_progress" <?= ($statusFilter ?? '') === 'in_progress' ? 'selected' : '' ?>>Procesā</option>
        <option value="done" <?= ($statusFilter ?? '') === 'done' ? 'selected' : '' ?>>Pabeigts</option>
        <option value="canceled" <?= ($statusFilter ?? '') === 'canceled' ? 'selected' : '' ?>>Atcelts</option>
      </select>

      <!-- Filtrs pēc klienta -->
      <label style="margin-left:10px;">Klients:</label>
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

      <!-- Checkbox filtrs: tikai darbi bez piešķirta meistara -->
      <div style="margin-top:10px;">
        <label style="display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
          <span>Tikai bez meistara</span>
          <input
            type="checkbox"
            name="without_master"
            value="1"
            <?= !empty($withoutMaster) ? 'checked' : '' ?>>
        </label>

        <button type="submit" style="margin-left:10px;">Filtrēt</button>
        <a href="<?= url('/jobs') ?>" style="margin-left:8px;">Notīrīt</a>
      </div>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Klients</th>
          <th>Virsraksts</th>
          <th>Statuss</th>
          <th>Cena</th>
          <th>Meistars</th>
          <th>Atvērt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $j): ?>
          <tr>
            <td><?= (int)$j['id'] ?></td>
            <td><?= h($j['client_name']) ?></td>
            <td><?= h($j['title']) ?></td>

            <!-- Statusu lietotājam rādam latviski -->
            <td><?= h(job_status_label($j['status'])) ?></td>

            <td><?= h($j['price'] ?? '') ?></td>

            <!-- Meistars var būt tukšs -->
            <td><?= h($j['master_name'] ?? '') ?></td>

            <!-- Saite uz darba kartīti -->
            <td>
              <a href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>

</div>