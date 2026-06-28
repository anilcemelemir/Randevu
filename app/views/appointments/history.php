<?php
$statusOptions = [
    'all' => 'Tümü',
    'booked' => 'Planlandı',
    'completed' => 'Onaylandı',
    'cancelled' => 'İptal',
];
?>

<header class="page-header">
    <div>
        <span class="eyebrow">Arşiv</span>
        <h1>Randevu ge&ccedil;mişi</h1>
    </div>
    <a class="button" href="/appointments/new">Randevu ekle</a>
</header>

<section class="panel focus-panel">
    <h2>Filtreler</h2>
    <form class="inline-form history-filter-form" method="get" action="/appointments/history">
        <label>
            M&uuml;şteri adı
            <input type="search" name="customer" value="<?= e($customerQuery) ?>" placeholder="M&uuml;şteri ara">
        </label>
        <label>
            Uzman
            <select name="specialist_id">
                <option value="0">T&uuml;m uzmanlar</option>
                <?php foreach ($specialists as $specialist): ?>
                    <option value="<?= (int) $specialist['id'] ?>" <?= (int) $specialistFilter === (int) $specialist['id'] ? 'selected' : '' ?>>
                        <?= e($specialist['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Durum
            <select name="status">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Başlangı&ccedil;
            <input type="date" name="date_from" value="<?= e($dateFrom) ?>">
        </label>
        <label>
            Bitiş
            <input type="date" name="date_to" value="<?= e($dateTo) ?>">
        </label>
        <div class="filter-actions">
            <button type="submit">Ara</button>
            <a class="button secondary" href="/appointments/history">Temizle</a>
        </div>
    </form>
</section>

<section class="panel">
    <div class="filter-header">
        <h2>T&uuml;m randevu kayıtları</h2>
        <span class="result-count"><?= count($appointments) ?> kayıt</span>
    </div>

    <div class="table-wrap appointments-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>M&uuml;şteri</th>
                    <th>Telefon</th>
                    <th>Uzman</th>
                    <th>Tarih</th>
                    <th>Saat</th>
                    <th>İşlem</th>
                    <th>Fiyat</th>
                    <th>A&ccedil;ıklama</th>
                    <th>Durum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <?php
                    $canManage = $user['role'] === 'admin' || (int) $appointment['specialist_id'] === (int) $user['id'];
                    $canCancel = $canManage && ($appointment['status'] === 'completed' || ($appointment['status'] === 'booked' && strtotime($appointment['slot_start']) > time() + 3600));
                    ?>
                    <tr>
                        <td><?= e($appointment['customer_name'] ?: '-') ?></td>
                        <td><?= e($appointment['customer_phone'] ?: '-') ?></td>
                        <td><?= e($appointment['specialist_name']) ?></td>
                        <td><?= e(date('d.m.Y', strtotime($appointment['slot_start']))) ?></td>
                        <td><?= e(date('H:i', strtotime($appointment['slot_start']))) ?> - <?= e(date('H:i', strtotime($appointment['slot_end']))) ?></td>
                        <td><?= e($appointment['service_name'] ?: '-') ?></td>
                        <td><?= e(number_format((float) $appointment['service_price'], 2, ',', '.')) ?> TL</td>
                        <td><?= e($appointment['note'] ?: '-') ?></td>
                        <td><span class="badge"><?= e(status_label($appointment['status'])) ?></span></td>
                        <td>
                            <?php if ($canManage && in_array($appointment['status'], ['booked', 'completed'], true)): ?>
                                <div class="table-actions">
                                    <?php if ($appointment['status'] === 'booked'): ?>
                                        <form method="post" action="/appointments/approve">
                                            <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                            <button class="link-button" type="submit">Onayla</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($canCancel): ?>
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
                <?php if (!$appointments): ?>
                    <tr><td colspan="10" class="empty">Bu filtrelere uygun randevu bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
