<?php

/** @var array $masters */ ?>
<?php /** @var string|null $error */ ?>

<div class="row">

    <div class="card">
        <h2>Pievienot meistaru</h2>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= url('/masters') ?>">
            <label>Vārds*</label>
            <input name="name" required>

            <label>Telefons</label>
            <input name="phone">

            <label>Email</label>
            <input name="email">

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
                    <th>Email</th>
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