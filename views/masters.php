<?php

/** @var array $masters */
?>
<?php /** @var string|null $error */ ?>

<div class="row">

    <div class="card section">
        <div class="section-header">
            <h2 class="section-title">Pievienot meistaru</h2>
        </div>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form class="form" method="post" action="<?= url('/masters') ?>">

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

    <div class="card section">
        <div class="section-header">
            <h2 class="section-title">Meistari</h2>
        </div>
        <p class="muted">Kopā: <?= count($masters) ?></p>

        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="td-num">ID</th>
                    <th>Vārds</th>
                    <th>Telefons</th>
                    <th>E-pasts</th>
                    <th class="td-nowrap">Links meistaram</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $m): ?>
                    <tr>
                        <td class="td-num"><?= (int)$m['id'] ?></td>
                        <td><?= h($m['name']) ?></td>
                        <td><?= h($m['phone'] ?? '') ?></td>
                        <td><?= h($m['email'] ?? '') ?></td>
                        <td class="td-nowrap">
                            <!-- Atver meistara publisko darba lapu jaunā cilnē -->
                            <a class="btn btn-secondary btn-small" href="<?= url('/my-jobs?token=' . h($m['token'])) ?>" target="_blank">
                                Atvērt "my-jobs"
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
