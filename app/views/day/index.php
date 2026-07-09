<?php
$dateTs = strtotime($date);
$weekdayName = weekdays()[(int) date('N', $dateTs)] ?? '';
$dateTitle = (int) date('j', $dateTs) . ' ' . tr_month((int) date('n', $dateTs)) . ' ' . date('Y', $dateTs);
$backUrl = '/dashboard?month=' . rawurlencode($backMonth);
$rows = $dayRows ?? [];
$freeCount = 0;

foreach ($rows as $row) {
    if ($row['type'] === 'free') {
        $freeCount++;
    }
}
?>

<header class="page-header day-header">
    <div>
        <span class="eyebrow"><a class="back-link" href="<?= e($backUrl) ?>">&#8249; Takvime d&ouml;n</a></span>
        <h1><?= e($dateTitle) ?></h1>
        <p class="day-subtitle"><?= e($weekdayName) ?> · T&uuml;m uzmanlar</p>
    </div>
</header>

<section class="panel">
    <?php if (!$specialists): ?>
        <div class="empty-state">
            <strong>&Ouml;nce bir uzman ekleyin.</strong>
            <p>Randevu planı i&ccedil;in en az bir uzman gerekir.</p>
        </div>
    <?php elseif (!$rows): ?>
        <div class="empty-state">
            <strong>Bu g&uuml;n &ccedil;alışma saati tanımlı değil.</strong>
            <p>Se&ccedil;ilen g&uuml;n i&ccedil;in uzman mesaisi bulunamadı.</p>
        </div>
    <?php else: ?>
        <p class="day-legend"><span class="dot dot-free"></span> Boş <span class="dot dot-busy"></span> Dolu <span class="dot dot-blocked"></span> Kapalı &nbsp;·&nbsp; <?= (int) $freeCount ?> boş saat</p>
        <ul class="day-slots">
            <?php foreach ($rows as $row): ?>
                <?php
                $specialist = $row['specialist'];
                $specialistId = (int) $specialist['id'];
                $canManage = can_manage_specialist($user, $specialistId);
                ?>

                <?php if ($row['type'] === 'busy'): ?>
                    <?php
                    $appointment = $row['appointment'];
                    $status = $appointment['status'];
                    $phone = trim((string) $appointment['customer_phone']);
                    $customerName = trim((string) ($appointment['customer_name'] ?? ''));
                    $isEditableBooked = $canManage && $status === 'booked';
                    ?>
                    <li class="day-slot is-busy status-<?= e($status) ?><?= $isEditableBooked ? ' has-inline-form' : '' ?>">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong>Uzman: <?= e($specialist['name']) ?></strong>

                            <?php if ($isEditableBooked): ?>
                                <form class="slot-inline-form" method="post" action="/appointments/approve" data-customer-form>
                                    <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                    <label>
                                        Müşteri
                                        <input data-customer-name type="text" name="customer_name" value="<?= e($customerName) ?>" placeholder="Müşteri adı" required>
                                    </label>
                                    <label>
                                        Telefon
                                        <input type="text" name="customer_phone" value="<?= e($phone) ?>" placeholder="+90 5xx xxx xx xx">
                                    </label>
                                    <label>
                                        Not
                                        <textarea name="note" rows="2" placeholder="Not"><?= e($appointment['note'] ?? '') ?></textarea>
                                    </label>
                                    <div class="customer-action-row" data-customer-actions<?= $customerName === '' ? ' hidden' : '' ?>>
                                        <button type="submit">Onayla</button>
                                        <button class="button ghost danger" type="submit" formaction="/appointments/cancel" formnovalidate>Sil</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <span class="day-slot-service">Müşteri: <?= e($customerName !== '' ? $customerName : 'İsimsiz') ?></span>
                                <div class="day-slot-meta">
                                    <span>Nail Art</span>
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
                            <?php endif; ?>
                        </div>
                        <div class="day-slot-side">
                            <span class="badge status-badge-<?= e($status) ?>"><?= e(status_label($status)) ?></span>
                            <?php if ($canManage && in_array($status, ['booked', 'completed'], true) && !$isEditableBooked): ?>
                                <form method="post" action="/appointments/cancel">
                                    <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                    <button class="link-button danger" type="submit">Sil</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php elseif ($row['type'] === 'blocked'): ?>
                    <li class="day-slot is-blocked">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong>Uzman: <?= e($specialist['name']) ?></strong>
                            <span class="day-slot-service"><?= e($row['reason'] ?: 'Kapalı') ?></span>
                        </div>
                    </li>
                <?php elseif ($row['type'] === 'past'): ?>
                    <li class="day-slot is-past">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong>Uzman: <?= e($specialist['name']) ?></strong>
                            <span class="day-slot-service">Geçti</span>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="day-slot is-free<?= $canManage ? ' has-add' : '' ?>">
                        <div class="day-slot-time"><?= e($row['label']) ?></div>
                        <div class="day-slot-body">
                            <strong>Uzman: <?= e($specialist['name']) ?></strong>
                            <?php if ($canManage): ?>
                                <form class="slot-add-form compact" method="post" action="/appointments/create" data-customer-form>
                                    <input type="hidden" name="date" value="<?= e($date) ?>">
                                    <input type="hidden" name="slot_start" value="<?= e(date('Y-m-d H:i:s', $row['start'])) ?>">
                                    <input type="hidden" name="specialist_id" value="<?= (int) $specialistId ?>">
                                    <input type="hidden" name="complete_now" value="1">
                                    <label>
                                        Müşteri
                                        <input data-customer-name type="text" name="customer_name" placeholder="Müşteri adı" required>
                                    </label>
                                    <label>
                                        Telefon
                                        <input type="text" name="customer_phone" placeholder="+90 5xx xxx xx xx">
                                    </label>
                                    <label>
                                        Not
                                        <textarea name="note" rows="2" placeholder="Not"></textarea>
                                    </label>
                                    <div class="customer-action-row" data-customer-actions hidden>
                                        <button type="submit">Onayla</button>
                                        <button class="button ghost danger" type="reset">Sil</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <span class="day-slot-service">Müşteri: -</span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<script>
    document.querySelectorAll('[data-customer-form]').forEach((form) => {
        const input = form.querySelector('[data-customer-name]');
        const actions = form.querySelector('[data-customer-actions]');
        if (!input || !actions) {
            return;
        }

        const syncActions = () => {
            actions.hidden = input.value.trim() === '';
        };

        input.addEventListener('input', syncActions);
        form.addEventListener('reset', () => window.setTimeout(syncActions, 0));
        syncActions();
    });
</script>
