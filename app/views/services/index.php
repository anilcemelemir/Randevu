<header class="page-header">
    <div>
        <span class="eyebrow">Admin</span>
        <h1>İşlem fiyat listesi</h1>
    </div>
</header>

<section class="services-layout">
    <div class="panel">
        <h2>İşlemler</h2>
        <div class="service-list">
            <?php foreach ($services as $service): ?>
                <form class="service-card" method="post" action="/services/update">
                    <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                    <div class="service-card-main">
                        <label>
                            İşlem adı
                            <input type="text" name="name" value="<?= e($service['name']) ?>" required>
                        </label>
                        <label>
                            Fiyat
                            <input type="number" step="0.01" min="0" name="price" value="<?= e((string) $service['price']) ?>" required>
                        </label>
                    </div>
                    <label>
                        Açıklama
                        <textarea name="description" rows="3"><?= e($service['description'] ?? '') ?></textarea>
                    </label>
                    <div class="form-footer">
                        <label class="checkline">
                            <input type="checkbox" name="is_active" value="1" <?= (int) $service['is_active'] === 1 ? 'checked' : '' ?>>
                            <span>Aktif</span>
                        </label>
                        <div class="button-row">
                            <button type="submit">Kaydet</button>
                            <button class="button ghost" type="submit" formaction="/services/delete">Pasife al</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
            <?php if (!$services): ?>
                <p class="empty">Henüz işlem eklenmemiş.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <h2>Yeni işlem ekle</h2>
        <form class="stack-form" method="post" action="/services/create">
            <label>
                İşlem adı
                <input type="text" name="name" required>
            </label>
            <label>
                Fiyat
                <input type="number" step="0.01" min="0" name="price" required>
            </label>
            <label>
                Açıklama
                <textarea name="description" rows="4"></textarea>
            </label>
            <button type="submit">İşlem ekle</button>
        </form>
    </div>
</section>

