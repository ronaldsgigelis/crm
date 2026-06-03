<?php

/** @var array $masters */
?>
<?php /** @var string|null $error */ ?>

<div class="row masters-page">

    <div class="card section masters-create-panel">
        <div class="section-header">
            <h2 class="section-title">Pievienot meistaru</h2>
        </div>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form class="form masters-create-form" method="post" action="<?= url('/masters') ?>">

            <!-- Vārds ir obligāts -->
            <div class="field">
                <label>Vārds*</label>
                <input
                    name="name"
                    required
                    maxlength="120"
                    placeholder="Piemēram, Jānis Bērziņš">
            </div>

            <!-- Telefona lauks -->
            <div class="field">
                <label>Telefons</label>
                <input
                    name="phone"
                    type="tel"
                    inputmode="tel"
                    autocomplete="tel"
                    maxlength="40"
                    placeholder="+371 20000000">
            </div>

            <!-- E-pasta lauks -->
            <div class="field">
                <label>E-pasts</label>
                <input
                    name="email"
                    type="email"
                    autocomplete="email"
                    maxlength="120"
                    placeholder="meistars@example.com">
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Saglabāt</button>
            </div>
        </form>
    </div>

    <div class="card section masters-panel">
        <div class="section-header">
            <h2 class="section-title">Meistari</h2>
        </div>
        <p class="muted masters-count">Kopā: <?= count($masters) ?></p>

        <?php if (!$masters): ?>
            <p class="empty-state">Meistari vēl nav pievienoti.</p>
        <?php else: ?>
        <div class="table-wrap masters-table-wrap">
        <table class="table table-compact masters-table">
            <thead>
                <tr>
                    <th>Meistars</th>
                    <th>Kontaktinformācija</th>
                    <th class="td-nowrap">Links meistaram</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $m): ?>
                    <tr>
                        <td class="master-name-cell">
                            <div class="master-cell-stack">
                                <div class="master-cell-main"><?= h($m['name']) ?></div>
                                <div class="master-cell-meta">ID: <?= (int)$m['id'] ?></div>
                            </div>
                        </td>
                        <td class="master-contact-cell">
                            <div class="master-cell-stack">
                                <div class="master-cell-meta">Telefons: <?= h($m['phone'] ?? '') ?></div>
                                <div class="master-cell-meta">E-pasts: <?= h($m['email'] ?? '') ?></div>
                            </div>
                        </td>
                        <td class="td-nowrap master-actions-cell">
                            <div class="table-actions master-actions">
                            <!-- Atver meistara publisko darba lapu jaunā cilnē -->
                            <a class="btn btn-secondary btn-small master-token-link" href="<?= url('/my-jobs?token=' . h($m['token'])) ?>" target="_blank">
                                Atvērt "my-jobs"
                            </a>
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
