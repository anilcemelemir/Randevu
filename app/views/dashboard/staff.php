<?php
$statusOptions = [
    'booked' => 'Planlandı',
    'completed' => 'Onaylandı',
    'all' => 'Tümü',
    'cancelled' => 'İptal',
];
?>

<header class="page-header">
    <div>
        <span class="eyebrow"><?= e(role_label($user['role'])) ?></span>
        <h1>Randevu defteri</h1>
    </div>
    <a class="button" href="/appointments/new">Randevu ekle</a>
</header>

<section class="panel focus-panel">
    <div class="filter-header">
        <h2><?= $user['role'] === 'admin' ? 'Randevular' : 'Bana ait randevular' ?></h2>
        <nav class="status-tabs">
            <?php foreach ($statusOptions as $value => $label): ?>
                <a class="<?= $statusFilter === $value ? 'active' : '' ?>" href="/dashboard?status=<?= e($value) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Müşteri</th>
                    <th>Telefon</th>
                    <?php if ($user['role'] === 'admin'): ?>
                        <th>Uzman</th>
                    <?php endif; ?>
                    <th>Tarih</th>
                    <th>Saat</th>
                    <th>İşlem</th>
                    <th>Fiyat</th>
                    <th>Açıklama</th>
                    <th>Durum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $rows = $user['role'] === 'admin' ? $appointments : $myAppointments; ?>
                <?php foreach ($rows as $appointment): ?>
                    <tr>
                        <td><?= e($appointment['customer_name'] ?: '-') ?></td>
                        <td><?= e($appointment['customer_phone'] ?: '-') ?></td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <td><?= e($appointment['specialist_name']) ?></td>
                        <?php endif; ?>
                        <td><?= e(date('d.m.Y', strtotime($appointment['slot_start']))) ?></td>
                        <td><?= e(date('H:i', strtotime($appointment['slot_start']))) ?> - <?= e(date('H:i', strtotime($appointment['slot_end']))) ?></td>
                        <td><?= e($appointment['service_name'] ?: '-') ?></td>
                        <td><?= e(number_format((float) $appointment['service_price'], 2, ',', '.')) ?> TL</td>
                        <td><?= e($appointment['note'] ?: '-') ?></td>
                        <td><span class="badge"><?= e(status_label($appointment['status'])) ?></span></td>
                        <td>
                            <?php if ($appointment['status'] === 'booked'): ?>
                                <div class="table-actions">
                                    <form method="post" action="/appointments/approve">
                                        <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                        <button class="link-button" type="submit">Onayla</button>
                                    </form>
                                    <?php if (strtotime($appointment['slot_start']) > time() + 3600): ?>
                                        <form method="post" action="/appointments/cancel">
                                            <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                            <button class="link-button danger" type="submit">İptal</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="<?= $user['role'] === 'admin' ? 10 : 9 ?>" class="empty">Bu filtreye uygun randevu yok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($user['role'] === 'specialist'): ?>
    <section class="panel">
        <h2>Salon takvimi</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Müşteri</th>
                        <th>Uzman</th>
                        <th>Tarih</th>
                        <th>Saat</th>
                        <th>İşlem</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['customer_name'] ?: '-') ?></td>
                            <td><?= e($appointment['specialist_name']) ?></td>
                            <td><?= e(date('d.m.Y', strtotime($appointment['slot_start']))) ?></td>
                            <td><?= e(date('H:i', strtotime($appointment['slot_start']))) ?> - <?= e(date('H:i', strtotime($appointment['slot_end']))) ?></td>
                            <td><?= e($appointment['service_name'] ?: '-') ?></td>
                            <td><span class="badge"><?= e(status_label($appointment['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$appointments): ?>
                        <tr><td colspan="6" class="empty">Salon takviminde randevu yok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

