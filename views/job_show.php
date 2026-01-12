<?php /** @var array|null $job */ ?>
<?php /** @var string|null $error */ ?>

<?php if (!$job): ?>
  <p class="error">Darbs nav atrasts.</p>
<?php else: ?>
  <div class="card">
    <h2>#<?= (int)$job['id'] ?> — <?= h($job['title']) ?></h2>
    <p><strong>Klients:</strong> <?= h($job['client_name']) ?></p>
    <p><strong>Statuss:</strong> <?= h($job['status']) ?></p>
    <p><strong>Cena:</strong> <?= h($job['price'] ?? '') ?></p>
    <p><strong>Plānots:</strong> <?= h($job['scheduled_at'] ?? '') ?></p>
    <p><strong>Apraksts:</strong><br><?= nl2br(h($job['description'] ?? '')) ?></p>

    <hr>

    <h3>Mainīt statusu</h3>
    <form method="post" action="<?= url('/job') ?>">
      <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">
      <select name="status">
        <?php foreach (['new','in_progress','done','canceled'] as $s): ?>
          <option value="<?= h($s) ?>" <?= $job['status'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Saglabāt</button>
    </form>
  </div>
<?php endif; ?>
