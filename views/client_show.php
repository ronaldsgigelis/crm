<?php

/** @var array|null $client */ ?>
<?php /** @var array $jobs */ ?>
<?php /** @var int $jobsCount */ ?>
<?php /** @var float $totalAmount */ ?>

<!-- View: viena klienta kartīte + viņa darbu vēsture -->

<?php if (!$client): ?>
    <!-- Ja klients nav atrasts -->
    <div class="card section">
        <p class="error">Klients nav atrasts.</p>
    </div>

<?php else: ?>

    <div class="client-detail-page">
    <div class="card section client-detail-panel">
        <!-- Klienta pamatinformācija -->
        <div class="section-header">
            <div>
                <h2 class="section-title">Klienta kartīte - <?= h($client['name']) ?></h2>
                <p class="metadata">Klienta profils</p>
            </div>
        </div>

        <div class="detail-grid client-detail-grid">
            <div class="detail-item">
                <div class="detail-label">ID</div>
                <div class="detail-value"><?= (int)$client['id'] ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Vārds</div>
                <div class="detail-value"><?= h($client['name']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tālrunis</div>
                <div class="detail-value"><?= h($client['phone'] ?? '') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">E-pasts</div>
                <div class="detail-value"><?= h($client['email'] ?? '') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Izveidots</div>
                <div class="detail-value"><?= h($client['created_at']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Darbu skaits</div>
                <div class="detail-value"><?= (int)$jobsCount ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Kopējā darbu summa</div>
                <div class="detail-value"><?= number_format($totalAmount, 2, '.', '') ?></div>
            </div>
        </div>

        <div class="section-actions mt-3">
            <a class="btn btn-secondary" href="<?= url('/clients') ?>">← Atpakaļ uz klientiem</a>
        </div>
    </div>

    <div class="card section client-jobs-panel">
        <div class="section-header">
            <h2 class="section-title">Klienta darbu vēsture</h2>
        </div>

        <?php if (!$jobs): ?>
            <p class="empty-state">Šim klientam vēl nav neviena darba.</p>
        <?php else: ?>
            <div class="table-wrap client-jobs-table-wrap">
            <table class="table table-compact client-jobs-table">
                <thead>
                    <tr>
                        <th>Darbs</th>
                        <th class="td-nowrap">Statuss</th>
                        <th class="td-num">Cena</th>
                        <th class="td-nowrap">Plānots / meistars</th>
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
                            <td class="client-job-title-cell">
                                <div class="client-cell-stack">
                                    <div class="client-cell-main"><?= h($j['title']) ?></div>
                                    <div class="client-cell-meta">ID: <?= (int)$j['id'] ?></div>
                                </div>
                            </td>

                            <!-- Statusu rādam latviski ar helperi -->
                            <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

                            <td class="td-num"><?= h($j['price'] ?? '') ?></td>
                            <td class="td-nowrap client-job-meta-cell">
                                <div class="client-cell-stack">
                                    <div class="client-cell-meta">Plānots: <?= h($j['scheduled_at'] ?? '') ?></div>
                                    <div class="client-cell-meta">Meistars: <?= h($j['master_name'] ?? '') ?></div>
                                </div>
                            </td>

                            <td class="td-nowrap client-actions-cell">
                                <div class="table-actions client-actions">
                                <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a>
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

<?php endif; ?>
