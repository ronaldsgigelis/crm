<?php

/** @var array $masters */
?>
<?php /** @var string|null $error */ ?>
<?php /** @var string|null $notice */ ?>
<?php /** @var array|null $editingMaster */ ?>
<?php $notice = $notice ?? null; ?>
<?php $editingMaster = $editingMaster ?? null; ?>

<div class="row masters-page">

    <div class="card section masters-create-panel">
        <div class="section-header">
            <h2 class="section-title"><?= $editingMaster ? 'Labot meistaru' : 'Pievienot meistaru' ?></h2>
        </div>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <?php if ($notice): ?>
            <p class="muted"><?= h($notice) ?></p>
        <?php endif; ?>

        <form class="form masters-create-form" method="post" action="<?= url($editingMaster ? '/master-update' : '/masters') ?>">
            <?php if ($editingMaster): ?>
                <input type="hidden" name="id" value="<?= (int)$editingMaster['id'] ?>">
            <?php endif; ?>

            <!-- Vārds ir obligāts -->
            <div class="field">
                <label>Vārds*</label>
                <input
                    name="name"
                    required
                    maxlength="120"
                    value="<?= h($editingMaster['name'] ?? '') ?>"
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
                    value="<?= h($editingMaster['phone'] ?? '') ?>"
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
                    value="<?= h($editingMaster['email'] ?? '') ?>"
                    placeholder="meistars@example.com">
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= $editingMaster ? 'Saglabāt izmaiņas' : 'Saglabāt' ?></button>
                <?php if ($editingMaster): ?>
                    <a class="btn btn-secondary" href="<?= url('/masters') ?>">Atcelt</a>
                <?php endif; ?>
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
                    <th class="td-nowrap">Darbības</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $m): ?>
                    <tr class="<?= (int)($editingMaster['id'] ?? 0) === (int)$m['id'] ? 'selected-row' : '' ?>">
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
                            <a class="btn btn-secondary btn-small" href="<?= url('/masters?edit_id=' . (int)$m['id']) ?>">Labot</a>
                            <form
                                class="table-actions-form"
                                method="post"
                                action="<?= url('/master-delete') ?>"
                                onsubmit="return confirm('Vai tiešām dzēst šo meistaru? Meistaru ar piesaistītiem darbiem dzēst nevar.');">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button class="btn btn-danger btn-small" type="submit">Dzēst</button>
                            </form>
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
