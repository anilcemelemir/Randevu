<header class="page-header">
    <div>
        <span class="eyebrow">Admin</span>
        <h1>Marka ve giriş ekranı</h1>
    </div>
</header>

<section class="grid balanced">
    <div class="panel">
        <h2>Görsel kimlik</h2>
        <form class="stack-form" method="post" action="/settings/update" enctype="multipart/form-data">
            <label>
                Marka adı
                <input type="text" name="brand_name" value="<?= e($settings['brand_name']) ?>" required>
            </label>

            <div class="upload-grid">
                <label>
                    Panel logosu
                    <input type="file" name="brand_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                </label>
                <label>
                    Favicon
                    <input type="file" name="favicon" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon">
                </label>
            </div>

            <label>
                Giriş ekranı arka plan fotoğrafı
                <input type="file" name="auth_background" accept="image/png,image/jpeg,image/webp">
            </label>

            <h2 class="section-gap">Giriş ekranı metinleri</h2>

            <label>
                Üst etiket
                <input type="text" name="auth_eyebrow" value="<?= e($settings['auth_eyebrow']) ?>">
            </label>

            <label>
                Ana başlık
                <textarea name="auth_title" rows="2" required><?= e($settings['auth_title']) ?></textarea>
            </label>

            <label>
                Açıklama
                <textarea name="auth_body" rows="4" required><?= e($settings['auth_body']) ?></textarea>
            </label>

            <button type="submit">Ayarları kaydet</button>
        </form>
    </div>

    <div class="panel">
        <h2>Mevcut görünüm</h2>
        <div class="brand-preview">
            <?php if (!empty($settings['brand_logo'])): ?>
                <img src="<?= e($settings['brand_logo']) ?>" alt="<?= e($settings['brand_name']) ?>">
            <?php else: ?>
                <span class="brand-mark"><?= e(initial($settings['brand_name'] ?: 'Randevu')) ?></span>
            <?php endif; ?>
            <div>
                <strong><?= e($settings['brand_name']) ?></strong>
            </div>
        </div>

        <div class="image-preview">
            <img src="<?= e($settings['auth_background'] ?: '/assets/images/auth-hero.jpg') ?>" alt="Giriş ekranı arka planı">
        </div>

        <div class="copy-preview">
            <span class="eyebrow"><?= e($settings['auth_eyebrow']) ?></span>
            <h3><?= e($settings['auth_title']) ?></h3>
            <p><?= e($settings['auth_body']) ?></p>
        </div>
    </div>
</section>
