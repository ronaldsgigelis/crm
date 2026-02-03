<?php

/** @var array|null $job */ ?>
<?php /** @var string|null $error */ ?>
<!-- View: viena darba kartīte + statusa maiņas forma 
 job_show.php ir viena darba detalizētā kartīte. Ja darbs nav atrasts, rāda kļūdu. 
 Ja ir atrasts, rāda laukus un dod formu statusa maiņai ar POST uz /job, nosūtot hidden id.-->

<?php if (!$job): ?>
  <!-- Ja kontrolieris neatrod darbu pēc ID -->
  <p class="error">Darbs nav atrasts.</p>

<?php else: ?>

  <div class="card">
    <!-- Darba pamatinformācija -->
    <h2>#<?= (int)$job['id'] ?> — <?= h($job['title']) ?></h2>

    <p><strong>Klients:</strong> <?= h($job['client_name']) ?></p>
    <p><strong>Statuss:</strong> <?= h($job['status']) ?></p>
    <p><strong>Cena:</strong> <?= h($job['price'] ?? '') ?></p>
    <p><strong>Plānots:</strong> <?= h($job['scheduled_at'] ?? '') ?></p>

    <!-- Apraksts: h() pret XSS + nl2br lai saglabā rindas -->
    <p><strong>Apraksts:</strong><br>
      <?= nl2br(h($job['description'] ?? '')) ?>
    </p>

    <hr>



    <h3>Piešķirt meistaru</h3>
    <form method="post" action="<?= url('/job-assign') ?>">
      <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">
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

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt</button>
      </div>
    </form>




    <!-- Statusa maiņa (POST) -->
    <h3>Mainīt statusu</h3>

    <form method="post" action="<?= url('/job') ?>">
      <input type="hidden" name="id" value="<?= (int)$job['id'] ?>">

      <select name="status">
        <?php foreach (['new', 'in_progress', 'done', 'canceled'] as $s): ?>
          <option value="<?= h($s) ?>" <?= $job['status'] === $s ? 'selected' : '' ?>>
            <?= h($s) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Saglabāt</button>
    </form>

  </div>

<?php endif; ?>