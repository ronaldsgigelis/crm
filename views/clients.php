<?php /** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>

<div class="row">
  <div class="card">
    <h2>Pievienot klientu</h2>
    <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>

    <form method="post" action="<?= url('/clients') ?>">
      <label>Vārds*</label>
      <input name="name" required>

      <label>Tālrunis</label>
      <input name="phone">

      <label>E-pasts</label>
      <input name="email">

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h2>Klientu saraksts</h2>
    <p class="muted">Kopā: <?= count($clients) ?></p>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>Vārds</th><th>Tālrunis</th><th>E-pasts</th><th>Izveidots</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= h($c['name']) ?></td>
            <td><?= h($c['phone'] ?? '') ?></td>
            <td><?= h($c['email'] ?? '') ?></td>
            <td><?= h($c['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
