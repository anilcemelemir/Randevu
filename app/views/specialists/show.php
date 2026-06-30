<?php $user = Auth::user(); ?>

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

    <?php if ($user['role'] === 'admin'): ?>
        <div class="panel">
            <h2>Uzman bilgilerini d&uuml;zenle</h2>
            <form class="stack-form" method="post" action="/specialists/update" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int) $specialist['id'] ?>">
                <label>
                    Ad soyad
                    <input type="text" name="name" value="<?= e($specialist['name']) ?>" required>
                </label>
                <label>
                    E-posta
                    <input type="text" name="email" inputmode="email" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" value="<?= e($specialist['email']) ?>" required>
                </label>
                <label>
                    Yeni şifre
                    <input type="password" name="password" minlength="6" placeholder="Değişmeyecekse boş bırakın">
                </label>
                <label>
                    Uzmanlık
                    <input type="text" name="specialty" value="<?= e($specialist['specialty'] ?: '') ?>">
                </label>
                <label>
                    Telefon
                    <input type="text" name="phone" value="<?= e($specialist['phone'] ?: '') ?>">
                </label>
                <label>
                    Profil notu
                    <textarea name="bio" rows="4"><?= e($specialist['bio'] ?: '') ?></textarea>
                </label>
                <label>
                    Profil fotoğrafı
                    <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
                </label>
                <button type="submit">Bilgileri g&uuml;ncelle</button>
            </form>
        </div>

        <div class="panel tone">
            <h2>Uzmanı sil</h2>
            <p>Bu işlem uzman hesabını, mesai kayıtlarını ve bu uzmana bağlı randevu kayıtlarını siler.</p>
            <form method="post" action="/specialists/delete" onsubmit="return confirm('Bu uzman ve ilişkili kayıtlar silinsin mi?');">
                <input type="hidden" name="id" value="<?= (int) $specialist['id'] ?>">
                <button class="danger-button" type="submit">Uzmanı sil</button>
            </form>
        </div>
    <?php endif; ?>
</section>
