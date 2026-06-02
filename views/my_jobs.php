<?php

/** @var array $master */ ?>
<?php /** @var array $jobs */ ?>

<div class="card section">
    <div class="section-header">
        <h2 class="section-title"><?= h($master['name']) ?> - mani darbi</h2>
    </div>
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
                <?php
                $jobStatus = $j['status'] ?? '';
                $jobStatusBadgeClass = [
                    'new' => 'badge-neutral badge-status-new',
                    'in_progress' => 'badge-warning badge-status-in-progress',
                    'done' => 'badge-success badge-status-done',
                    'canceled' => 'badge-danger badge-status-canceled',
                ][$jobStatus] ?? 'badge-neutral badge-status-new';
                ?>
                <tr>
                    <td class="td-num"><?= (int)$j['id'] ?></td>
                    <td><?= h($j['client_name']) ?></td>
                    <td><?= h($j['title']) ?></td>

                    <!-- Statusu lietotājam rādam latviski -->
                    <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

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
