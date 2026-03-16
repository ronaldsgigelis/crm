<?php

/** @var array $masters */
?>
<?php /** @var string|null $error */ ?>

<div class="row">

    <div class="card">
        <h2>Pievienot meistaru</h2>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= url('/masters') ?>">

            <!-- Vārds ir obligāts -->
            <label>Vārds*</label>
            <input
                name="name"
                required
                maxlength="120"
                placeholder="Piemēram, Jānis Bērziņš">

            <!-- Telefona lauks -->
            <label>Telefons</label>
            <input
                name="phone"
                type="tel"
                inputmode="tel"
                autocomplete="tel"
                maxlength="40"
                placeholder="+371 20000000">

            <!-- E-pasta lauks -->
            <label>E-pasts</label>
            <input
                name="email"
                type="email"
                autocomplete="email"
                maxlength="120"
                placeholder="meistars@example.com">

            <div style="margin-top:10px;">
                <button type="submit">Saglabāt</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Meistari</h2>
        <p class="muted">Kopā: <?= count($masters) ?></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vārds</th>
                    <th>Telefons</th>
                    <th>E-pasts</th>
                    <th>Links meistaram</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $m): ?>
                    <tr>
                        <td><?= (int)$m['id'] ?></td>
                        <td><?= h($m['name']) ?></td>
                        <td><?= h($m['phone'] ?? '') ?></td>
                        <td><?= h($m['email'] ?? '') ?></td>
                        <td>
                            <!-- Atver meistara publisko darba lapu jaunā cilnē -->
                            <a href="<?= url('/my-jobs?token=' . h($m['token'])) ?>" target="_blank">
                                Atvērt "my-jobs"
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>