<?php

/** @var array $master */ ?>
<?php /** @var array $jobs */ ?>

<div class="card">
    <h2><?= h($master['name']) ?> - mani darbi</h2>
    <p class="muted">Kopā: <?= count($jobs) ?></p>

    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="td-num">ID</th>
                <th>Klients</th>
                <th>Virsraksts</th>
                <th class="td-nowrap">Statuss</th>
                <th class="td-nowrap">Plānots</th>
                <th class="td-nowrap">Atvērt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $j): ?>
                <tr>
                    <td class="td-num"><?= (int)$j['id'] ?></td>
                    <td><?= h($j['client_name']) ?></td>
                    <td><?= h($j['title']) ?></td>

                    <!-- Statusu lietotājam rādam latviski -->
                    <td class="td-nowrap"><?= h(job_status_label($j['status'])) ?></td>

                    <td class="td-nowrap"><?= h($j['scheduled_at'] ?? '') ?></td>
                    <td class="td-nowrap">
                        <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>" target="_blank">Darba kartīte</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
