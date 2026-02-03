<?php

/** @var array $master */ ?>
<?php /** @var array $jobs */ ?>

<div class="card">
    <h2><?= h($master['name']) ?> - mani darbi</h2>
    <p class="muted">Kopā: <?= count($jobs) ?></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Klients</th>
                <th>Virsraksts</th>
                <th>Statuss</th>
                <th>Plānots</th>
                <th>Atvērt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $j): ?>
                <tr>
                    <td><?= (int)$j['id'] ?></td>
                    <td><?= h($j['client_name']) ?></td>
                    <td><?= h($j['title']) ?></td>
                    <td><?= h($j['status']) ?></td>
                    <td><?= h($j['scheduled_at'] ?? '') ?></td>
                    <td>
                        <a href="<?= url('/job?id=' . (int)$j['id']) ?>" target="_blank">Darba kartīte</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>