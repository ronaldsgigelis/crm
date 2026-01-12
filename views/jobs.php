<?php /** @var array $jobs */ ?>
<?php /** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>

<div class="row">
  <div class="card">
    <h2>Pievienot darbu</h2>
    <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>

    <form method="post" action="<?= url('/jobs') ?>">
      <label>Klients*</label>
      <select name="client_id" required>
        <option value="">-- izvēlies --</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Virsraksts*</label>
      <input name="title" required>

      <label>Apraksts</label>
      <textarea name="description" rows="4"></textarea>

      <label>Statuss</label>
      <select name="status">
        <option value="new">new</option>
        <option value="in_progress">in_progress</option>
        <option value="done">done</option>
        <option value="canceled">canceled</option>
      </select>

      <label>Cena</label>
      <input name="price" type="number" step="0.01">

      <label>Plānots datums/laiks</label>
      <input name="scheduled_at" placeholder="YYYY-MM-DD HH:MM">

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h2>Darbu saraksts</h2>
    <p class="muted">Kopā: <?= count($jobs) ?></p>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>Klients</th><th>Virsraksts</th><th>Statuss</th><th>Cena</th><th>Atvērt</th>
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
            <td><a href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
