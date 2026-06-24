<?php $user = Auth::user(); ?>
<header class="page-header">
    <div>
        <span class="eyebrow">Salon ekibi</span>
        <h1>Uzmanlar</h1>
    </div>
</header>

<section class="grid balanced">
    <div class="panel">
        <h2>Uzman profilleri</h2>
        <div class="specialist-grid">
            <?php foreach ($specialists as $specialist): ?>
                <article class="specialist-card">
                    <img class="specialist-photo" src="<?= e($specialist['avatar'] ?: '/assets/images/default-avatar.svg') ?>" alt="<?= e($specialist['name']) ?>">
                    <div>
                        <h3><?= e($specialist['name']) ?></h3>
                        <p><?= e($specialist['specialty'] ?: 'Genel güzellik uygulamaları') ?></p>
                    </div>
                    <dl>
                        <div>
                            <dt>Telefon</dt>
                            <dd><?= e($specialist['phone'] ?: '-') ?></dd>
                        </div>
                        <div>
                            <dt>Toplam randevu</dt>
                            <dd><?= (int) $specialist['appointment_count'] ?></dd>
                        </div>
                        <div>
                            <dt>Sıradaki randevu</dt>
                            <dd><?= $specialist['next_appointment'] ? e(date('d.m.Y H:i', strtotime($specialist['next_appointment']))) : '-' ?></dd>
                        </div>
                    </dl>
                    <a class="button ghost" href="/specialists?id=<?= (int) $specialist['id'] ?>">Detayları gör</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($user['role'] === 'admin'): ?>
        <div class="panel">
            <h2>Yeni uzman ekle</h2>
            <form class="stack-form" method="post" action="/specialists/create" enctype="multipart/form-data">
                <label>
                    Ad soyad
                    <input type="text" name="name" required>
                </label>
                <label>
                    E-posta
                    <input type="email" name="email" required>
                </label>
                <label>
                    Geçici şifre
                    <input type="password" name="password" minlength="6" required>
                </label>
                <label>
                    Uzmanlık
                    <input type="text" name="specialty" placeholder="Cilt bakımı, kaş-kirpik, manikür">
                </label>
                <label>
                    Telefon
                    <input type="text" name="phone" placeholder="+90 5xx xxx xx xx">
                </label>
                <label>
                    Profil notu
                    <textarea name="bio" rows="4" placeholder="Kısa uzman profili"></textarea>
                </label>
                <label>
                    Profil fotoğrafı
                    <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
                </label>
                <button type="submit">Uzman oluştur</button>
            </form>
        </div>
    <?php endif; ?>
</section>
