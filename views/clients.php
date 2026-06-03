<?php

/** @var array $clients */ ?>
<?php /** @var string|null $error */ ?>
<!-- View: klientu pievienošana + saraksts -->

<div class="row clients-page">

  <!-- Klienta pievienošanas forma -->
  <div class="card section clients-create-panel">
    <div class="section-header">
      <h2 class="section-title">Pievienot klientu</h2>
    </div>

    <!-- Kļūdas ziņa (ja validācija neizgāja) -->
    <?php if ($error): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <!-- POST forma uz /clients -->
    <form class="form clients-create-form" method="post" action="<?= url('/clients') ?>">
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
  <div class="card section clients-panel">
    <div class="section-header">
      <h2 class="section-title">Klientu saraksts</h2>
    </div>

    <!-- Klientu skaits -->
    <p class="muted clients-count">Kopā: <?= count($clients) ?></p>

    <?php if (!$clients): ?>
      <p class="empty-state">Klienti vēl nav pievienoti.</p>
    <?php else: ?>
    <div class="table-wrap clients-table-wrap">
    <table class="table table-compact clients-table">
      <thead>
        <tr>
          <th>Klients</th>
          <th>Kontaktinformācija</th>
          <th class="td-nowrap">Izveidots</th>
          <th class="td-nowrap">Darbības</th>
        </tr>
      </thead>
      <tbody>

        <!-- Klientu izdruka -->
        <?php foreach ($clients as $c): ?>
          <tr>
            <td class="client-name-cell">
              <div class="client-cell-stack">
                <div class="client-cell-main"><?= h($c['name']) ?></div>
                <div class="client-cell-meta">ID: <?= (int)$c['id'] ?></div>
              </div>
            </td>
            <td class="client-contact-cell">
              <div class="client-cell-stack">
                <div class="client-cell-meta">Tālrunis: <?= h($c['phone'] ?? '') ?></div>
                <div class="client-cell-meta">E-pasts: <?= h($c['email'] ?? '') ?></div>
              </div>
            </td>
            <td class="td-date"><?= h($c['created_at']) ?></td>

            <!-- Saite uz klienta kartīti -->
            <td class="td-nowrap client-actions-cell">
              <div class="table-actions client-actions">
              <a class="btn btn-secondary btn-small" href="<?= url('/client?id=' . (int)$c['id']) ?>">Klienta kartīte</a>
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
