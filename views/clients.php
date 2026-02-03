<?php

/** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>
<!-- View: klientu pievienošana + saraksts -->

<div class="row">

  <!-- Klienta pievienošanas forma -->
  <div class="card">
    <h2>Pievienot klientu</h2>

    <!-- Kļūdas ziņa (ja validācija neizgāja) -->
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- POST forma uz /clients -->
    <form method="post" action="<?= url('/clients') ?>">
      <label>Vārds*</label>
      <input name="name" required>

      <label>Tālrunis</label>
      <input name="phone" autocomplete="tel" inputmode="tel" maxlength="32" placeholder="+371 0000000">

      <label>E-pasts</label>
      <input name="email" type="email" autocomplete="email" maxlength="120" placeholder="jūsu@epasts.com">

      <div style="margin-top:10px;">
        <button type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <!-- Klientu saraksts -->
  <div class="card">
    <h2>Klientu saraksts</h2>

    <!-- Klientu skaits -->
    <p class="muted">Kopā: <?= count($clients) ?></p>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Vārds</th>
          <th>Tālrunis</th>
          <th>E-pasts</th>
          <th>Izveidots</th>
        </tr>
      </thead>
      <tbody>

        <!-- Klientu izdruka -->
        <?php foreach ($clients as $c): ?>
          <tr>
            <!-- ID kā integer -->
            <td><?= (int)$c['id'] ?></td>

            <!-- Droša izvade ar h() -->
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