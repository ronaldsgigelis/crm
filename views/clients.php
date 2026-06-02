<?php

/** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>
<!-- View: klientu pievienošana + saraksts -->

<div class="row">

  <!-- Klienta pievienošanas forma -->
  <div class="card section">
    <div class="section-header">
      <h2 class="section-title">Pievienot klientu</h2>
    </div>

    <!-- Kļūdas ziņa (ja validācija neizgāja) -->
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- POST forma uz /clients -->
    <form class="form" method="post" action="<?= url('/clients') ?>">
      <div class="field">
        <label>Vārds*</label>
        <input
          name="name"
          required
          maxlength="120"
          placeholder="Piemēram, Jānis Bērziņš">
      </div>

      <div class="field">
        <label>Tālrunis</label>
        <input
          name="phone"
          type="tel"
          autocomplete="tel"
          inputmode="tel"
          maxlength="40"
          placeholder="+371 20000000">
      </div>

      <div class="field">
        <label>E-pasts</label>
        <input
          name="email"
          type="email"
          autocomplete="email"
          maxlength="120"
          placeholder="jusu@epasts.com">
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Saglabāt</button>
      </div>
    </form>
  </div>

  <!-- Klientu saraksts -->
  <div class="card section">
    <div class="section-header">
      <h2 class="section-title">Klientu saraksts</h2>
    </div>

    <!-- Klientu skaits -->
    <p class="muted">Kopā: <?= count($clients) ?></p>

    <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th class="td-num">ID</th>
          <th>Vārds</th>
          <th>Tālrunis</th>
          <th>E-pasts</th>
          <th class="td-nowrap">Izveidots</th>
          <th class="td-nowrap">Atvērt</th>
        </tr>
      </thead>
      <tbody>

        <!-- Klientu izdruka -->
        <?php foreach ($clients as $c): ?>
          <tr>
            <!-- ID kā integer -->
            <td class="td-num"><?= (int)$c['id'] ?></td>

            <!-- Droša izvade ar h() -->
            <td><?= h($c['name']) ?></td>
            <td><?= h($c['phone'] ?? '') ?></td>
            <td><?= h($c['email'] ?? '') ?></td>
            <td class="td-nowrap"><?= h($c['created_at']) ?></td>

            <!-- Saite uz klienta kartīti -->
            <td class="td-nowrap">
              <a class="btn btn-secondary btn-small" href="<?= url('/client?id=' . (int)$c['id']) ?>">Klienta kartīte</a>
            </td>
          </tr>
        <?php endforeach; ?>

      </tbody>
    </table>
    </div>
  </div>

</div>
