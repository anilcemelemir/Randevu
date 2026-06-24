<header class="page-header">
    <div>
        <span class="eyebrow">Uzman profili</span>
        <h1><?= e($specialist['name']) ?></h1>
    </div>
    <a class="button ghost" href="/specialists">Uzmanlara dön</a>
</header>

<section class="grid balanced">
    <div class="panel profile-panel">
        <div class="profile-hero">
            <img class="specialist-photo large" src="<?= e($specialist['avatar'] ?: '/assets/images/default-avatar.svg') ?>" alt="<?= e($specialist['name']) ?>">
            <div>
                <h2><?= e($specialist['specialty'] ?: 'Genel güzellik uygulamaları') ?></h2>
                <p><?= e($specialist['bio'] ?: 'Bu uzman için henüz profil notu eklenmedi.') ?></p>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <span>E-posta</span>
                <strong><?= e($specialist['email']) ?></strong>
            </div>
            <div>
                <span>Telefon</span>
                <strong><?= e($specialist['phone'] ?: '-') ?></strong>
            </div>
        </div>

        <h2 class="section-gap">Haftalık mesai</h2>
        <div class="timeline">
            <?php foreach ($hours as $hour): ?>
                <div class="timeline-item">
                    <strong><?= e(weekdays()[(int) $hour['weekday']]) ?></strong>
                    <span><?= e(substr($hour['start_time'], 0, 5)) ?> - <?= e(substr($hour['end_time'], 0, 5)) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (!$hours): ?>
                <p class="empty">Mesai tanımı yok.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <h2>Son randevular</h2>
        <div class="timeline">
            <?php foreach ($appointments as $appointment): ?>
                <div class="timeline-item">
                    <strong><?= e($appointment['customer_name']) ?></strong>
                    <span><?= e(date('d.m.Y H:i', strtotime($appointment['slot_start']))) ?> · <?= e(status_label($appointment['status'])) ?></span>
                    <small><?= e($appointment['note'] ?: 'İşlem notu yok') ?></small>
                </div>
            <?php endforeach; ?>
            <?php if (!$appointments): ?>
                <p class="empty">Bu uzman için randevu bulunmuyor.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
