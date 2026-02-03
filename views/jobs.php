<?php

/** @var array $jobs */ ?>
<?php /** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>

<?php /** @var array $masters */ ?>
<?php /** @var int $masterId */ ?>

<!-- View: darbu pievienošana + darbu saraksts 
 jobs.php ir view, kur vienā lapā ir darba pievienošanas forma un darbu saraksts. 
 Forma sūta POST uz /jobs, saraksts tiek renderēts no kontroliera datiem.-->

<div class="row">

  <!-- Darba pievienošanas forma -->
  <div class="card">
    <h2>Pievienot darbu</h2>

    <!-- Kļūdas ziņa (no kontroliera) -->
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
      <input name="title" required>

      <!-- Darba apraksts -->
      <label>Apraksts</label>
      <textarea name="description" rows="4"></textarea>

      <!-- Darba statuss -->
      <label>Statuss</label>
      <select name="status">
        <option value="new">new</option>
        <option value="in_progress">in_progress</option>
        <option value="done">done</option>
        <option value="canceled">canceled</option>
      </select>

      <!-- Cena (float) -->
      <label>Cena</label>
      <input name="price" type="number" step="0.01">

      <!-- Plānotais datums/laiks -->
      <!-- <label>Plānots datums/laiks</label> -->
      <!-- <input name="scheduled_at" placeholder="YYYY-MM-DD HH:MM"> -->
      <label>Plānotais darba datums un laiks</label>
      <input name="scheduled_at" type="datetime-local">



      <!--meistaram pieskirt darbu-->
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



    <!--“Visi darbi”,“Tikai Ivans”,utt. -->
    <form method="get" action="<?= url('/jobs') ?>" style="margin:10px 0;">
      <label>Filtrs pēc meistara:</label>
      <select name="master_id">
        <option value="0">Visi</option>
        <?php foreach (($masters ?? []) as $m): ?>
          <option value="<?= (int)$m['id'] ?>" <?= ((int)($masterId ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
            <?= h($m['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Filtrēt</button>
      <a href="<?= url('/jobs') ?>" style="margin-left:8px;">Notīrīt</a>
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
            <td><?= h($j['status']) ?></td>
            <td><?= h($j['price'] ?? '') ?></td>

            <!-- Meistars (var būt tukšs) -->
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