<header class="page-header">
    <div>
        <span class="eyebrow">Uzman profili</span>
        <h1>Profilim</h1>
    </div>
</header>

<section class="grid balanced">
    <div class="panel">
        <h2>Profil bilgileri</h2>
        <form class="stack-form" method="post" action="/profile/update" enctype="multipart/form-data">
            <div class="profile-editor-head">
                <img class="specialist-photo large" src="<?= e($profile['avatar'] ?: '/assets/images/default-avatar.svg') ?>" alt="<?= e($profile['name']) ?>">
                <div>
                    <strong><?= e($profile['name']) ?></strong>
                    <small><?= e($profile['email']) ?></small>
                </div>
            </div>

            <label>
                Ad soyad
                <input type="text" name="name" value="<?= e($profile['name']) ?>" required>
            </label>

            <label>
                Uzmanlık
                <input type="text" name="specialty" value="<?= e($profile['specialty'] ?? '') ?>" placeholder="Cilt bakımı, kaş-kirpik, manikür">
            </label>

            <label>
                Telefon
                <input type="text" name="phone" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+90 5xx xxx xx xx">
            </label>

            <label>
                Profil açıklaması
                <textarea name="bio" rows="5" placeholder="Müşterilerin göreceği kısa uzman profili"><?= e($profile['bio'] ?? '') ?></textarea>
            </label>

            <label>
                Profil fotoğrafı
                <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
            </label>

            <button type="submit">Profili kaydet</button>
        </form>
    </div>

    <aside class="panel tone">
        <h2>Profil önizleme</h2>
        <div class="profile-hero preview-card">
            <img class="specialist-photo large" src="<?= e($profile['avatar'] ?: '/assets/images/default-avatar.svg') ?>" alt="<?= e($profile['name']) ?>">
            <div>
                <h2><?= e($profile['specialty'] ?: 'Genel güzellik uygulamaları') ?></h2>
                <p><?= e($profile['bio'] ?: 'Profil açıklaması henüz eklenmedi.') ?></p>
            </div>
        </div>
    </aside>
</section>
