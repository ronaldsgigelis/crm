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

    <div class="card section">
        <!-- Klienta pamatinformācija -->
        <div class="section-header">
            <h2 class="section-title">Klienta kartīte - <?= h($client['name']) ?></h2>
        </div>

        <p><strong>ID:</strong> <?= (int)$client['id'] ?></p>
        <p><strong>Vārds:</strong> <?= h($client['name']) ?></p>
        <p><strong>Tālrunis:</strong> <?= h($client['phone'] ?? '') ?></p>
        <p><strong>E-pasts:</strong> <?= h($client['email'] ?? '') ?></p>
        <p><strong>Izveidots:</strong> <?= h($client['created_at']) ?></p>

        <hr>

        <!-- Īsa statistika par klientu -->
        <p><strong>Darbu skaits:</strong> <?= (int)$jobsCount ?></p>
        <p><strong>Kopējā darbu summa:</strong> <?= number_format($totalAmount, 2, '.', '') ?></p>

        <div class="section-actions mt-3">
            <a class="btn btn-secondary" href="<?= url('/clients') ?>">← Atpakaļ uz klientiem</a>
        </div>
    </div>

    <div class="card section">
        <div class="section-header">
            <h2 class="section-title">Klienta darbu vēsture</h2>
        </div>

        <?php if (!$jobs): ?>
            <p class="muted">Šim klientam vēl nav neviena darba.</p>
        <?php else: ?>
            <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="td-num">ID</th>
                        <th>Virsraksts</th>
                        <th class="td-nowrap">Statuss</th>
                        <th class="td-num">Cena</th>
                        <th class="td-nowrap">Plānots</th>
                        <th>Meistars</th>
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
                            <td><?= h($j['title']) ?></td>

                            <!-- Statusu rādam latviski ar helperi -->
                            <td class="td-nowrap"><span class="badge <?= h($jobStatusBadgeClass) ?>"><?= h(job_status_label($j['status'])) ?></span></td>

                            <td class="td-num"><?= h($j['price'] ?? '') ?></td>
                            <td class="td-nowrap"><?= h($j['scheduled_at'] ?? '') ?></td>
                            <td><?= h($j['master_name'] ?? '') ?></td>

                            <td class="td-nowrap">
                                <a class="btn btn-secondary btn-small" href="<?= url('/job?id=' . (int)$j['id']) ?>">Darba kartīte</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>

<?php endif; ?>
