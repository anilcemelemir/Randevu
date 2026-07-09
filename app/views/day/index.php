<?php
$dateTs = strtotime($date);
$weekdayName = weekdays()[(int) date('N', $dateTs)] ?? '';
$dateTitle = (int) date('j', $dateTs) . ' ' . tr_month((int) date('n', $dateTs)) . ' ' . date('Y', $dateTs);
$specialistQ = $specialistId > 0 ? '&specialist_id=' . (int) $specialistId : '';
$backUrl = '/dashboard?month=' . rawurlencode($backMonth) . $specialistQ;

$currentSpecialist = null;
foreach ($specialists as $specialist) {
    if ((int) $specialist['id'] === (int) $specialistId) {
        $currentSpecialist = $specialist;
        break;
    }
}

$open = !empty($schedule['open']);
$rows = $schedule['rows'] ?? [];
$freeCount = 0;
foreach ($rows as $row) {
    if ($row['type'] === 'free') {
        $freeCount++;
    }
}
?>

<header class="page-header day-header">
    <div>
        <span class="eyebrow"><a class="back-link" href="<?= e($backUrl) ?>">‹ Takvime dön</a></span>
        <h1><?= e($dateTitle) ?></h1>
        <p class="day-subtitle"><?= e($weekdayName) ?><?= $currentSpecialist ? ' · ' . e($currentSpecialist['name']) : '' ?></p>
    </div>
</header>

<section class="panel">
    <?php if ($user['role'] === 'admin'): ?>
        <form class="specialist-filter day-specialist" method="get" action="/day">
            <input type="hidden" name="date" value="<?= e($date) ?>">
            <label>
                Uzman
                <select name="specialist_id" onchange="this.form.submit()">
                    <?php foreach ($specialists as $specialist): ?>
                        <option value="<?= (int) $specialist['id'] ?>" <?= (int) $specialistId === (int) $specialist['id'] ? 'selected' : '' ?>>
                            <?= e($specialist['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    <?php endif; ?>

    <?php if (!$specialistId): ?>
        <div class="empty-state">
            <strong>&Ouml;nce bir uzman ekleyin.</strong>
            <p>Randevu planı i&ccedil;in en az bir uzman gerekir.</p>
        </div>
    <?php elseif (!$open): ?>
        <div class="empty-state">
            <strong>Bu g&uuml;n &ccedil;alışma saati tanımlı değil.</strong>
            <p><?= e($currentSpecialist['name'] ?? 'Uzman') ?> i&ccedil;in <?= e($weekdayName) ?> g&uuml;n&uuml;ne mesai eklenmemiş.</p>
        </div>
    <?php else: ?>
        <p class="day-legend"><span class="dot dot-free"></span> Boş <span class="dot dot-busy"></span> Dolu <span class="dot dot-blocked"></span> Kapalı &nbsp;·&nbsp; <?= (int) $freeCount ?> boş saat</p>
        <ul class="day-slots">
            <?php foreach ($rows as $row): ?>
                <?php if ($row['type'] === 'busy'): ?>
                    <?php
                    $appointment = $row['appointment'];
                    $status = $appointment['status'];
                    $canCancel = $status === 'completed'
                        || ($status === 'booked' && strtotime($appointment['slot_start']) > time() + 3600);
                    $phone = trim((string) $appointment['customer_phone']);
                    ?>
                    <li class="day-slot is-busy status-<?= e($status) ?>">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong><?= e($appointment['customer_name'] ?: 'İsimsiz') ?></strong>
                            <span class="day-slot-service"><?= e($appointment['service_name'] ?: '—') ?></span>
                            <div class="day-slot-meta">
                                <?php if ($phone !== ''): ?>
                                    <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
                                <?php endif; ?>
                                <?php if ((float) $appointment['service_price'] > 0): ?>
                                    <span><?= e(number_format((float) $appointment['service_price'], 2, ',', '.')) ?> TL</span>
                                <?php endif; ?>
                                <?php if (!empty($appointment['note'])): ?>
                                    <span class="agenda-note"><?= e($appointment['note']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="day-slot-side">
                            <span class="badge status-badge-<?= e($status) ?>"><?= e(status_label($status)) ?></span>
                            <?php if ($canBook && in_array($status, ['booked', 'completed'], true)): ?>
                                <div class="agenda-actions">
                                    <?php if ($status === 'booked'): ?>
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
                        </div>
                    </li>
                <?php elseif ($row['type'] === 'blocked'): ?>
                    <li class="day-slot is-blocked">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong>Kapalı</strong>
                            <?php if (!empty($row['reason'])): ?>
                                <span class="day-slot-service"><?= e($row['reason']) ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php elseif ($row['type'] === 'past'): ?>
                    <li class="day-slot is-past">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body"><span class="day-slot-service">Geçti</span></div>
                    </li>
                <?php else: /* free */ ?>
                    <li class="day-slot is-free<?= $canBook ? ' has-add' : '' ?>">
                        <?php if ($canBook): ?>
                            <details class="slot-add">
                                <summary>
                                    <span class="day-slot-time"><?= e($row['label']) ?></span>
                                    <span class="slot-add-cta">Boş · Randevu ekle</span>
                                </summary>
                                <form class="slot-add-form" method="post" action="/appointments/create">
                                    <input type="hidden" name="date" value="<?= e($date) ?>">
                                    <input type="hidden" name="slot_start" value="<?= e(date('Y-m-d H:i:s', $row['start'])) ?>">
                                    <input type="hidden" name="specialist_id" value="<?= (int) $specialistId ?>">
                                    <label>
                                        M&uuml;şteri adı
                                        <input type="text" name="customer_name" required>
                                    </label>
                                    <label>
                                        M&uuml;şteri telefonu
                                        <input type="text" name="customer_phone" placeholder="+90 5xx xxx xx xx">
                                    </label>
                                    <label>
                                        S&uuml;re
                                        <select name="duration">
                                            <option value="1">1 saat</option>
                                            <option value="2">2 saat</option>
                                            <option value="3">3 saat</option>
                                        </select>
                                    </label>
                                    <fieldset class="service-options">
                                        <legend>İşlemler</legend>
                                        <?php foreach ($services as $service): ?>
                                            <label class="service-option">
                                                <input type="checkbox" name="service_ids[]" value="<?= (int) $service['id'] ?>">
                                                <span>
                                                    <strong><?= e($service['name']) ?></strong>
                                                    <small><?= e(number_format((float) $service['price'], 2, ',', '.')) ?> TL</small>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                    <label>
                                        Not
                                        <textarea name="note" rows="2" placeholder="İşlem detayı (opsiyonel)"></textarea>
                                    </label>
                                    <button type="submit">Randevuyu kaydet</button>
                                </form>
                            </details>
                        <?php else: ?>
                            <div class="day-slot-time"><?= e($row['label']) ?></div>
                            <div class="day-slot-body"><span class="day-slot-service">Boş</span></div>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
