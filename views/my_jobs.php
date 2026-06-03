<?php

/** @var array $master */ ?>
<?php /** @var array $jobs */ ?>

<div class="my-jobs-page">
<div class="card section my-jobs-hero">
    <div class="section-header">
        <div>
            <h2 class="section-title"><?= h($master['name']) ?> - mani darbi</h2>
            <p class="metadata">Publiskā meistara darba lapa</p>
        </div>
    </div>
    <p class="muted my-jobs-count">Kopā: <?= count($jobs) ?></p>
</div>

<div class="card section my-jobs-panel">
    <?php if (!$jobs): ?>
        <p class="empty-state">Jums pašlaik nav piešķirtu darbu.</p>
    <?php else: ?>
    <div class="table-wrap my-jobs-table-wrap">
    <table class="table table-compact my-jobs-table">
        <thead>
            <tr>
                <th>Darbs</th>
                <th>Klients</th>
                <th class="td-nowrap">Statuss</th>
                <th class="td-nowrap">Plānots</th>
                <th class="td-nowrap">Darbības</th>
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
                    <td class="my-job-title-cell">
                        <div class="master-cell-stack">
                            <div class="master-cell-main"><?= h($j['title']) ?></div>
                            <div class="master-cell-meta">ID: <?= (int)$j['id'] ?></div>
                        </div>
                    </td>
                    <td class="my-job-client-cell"><?= h($j['client_name']) ?></td>

                    <!-- Statusu lietotājam rādam latviski -->
                    <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

                    <td class="td-date"><?= h($j['scheduled_at'] ?? '') ?></td>
                    <td class="td-nowrap my-jobs-actions-cell">
                        <div class="table-actions my-jobs-actions">
                        <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>" target="_blank">Darba kartīte</a>
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
