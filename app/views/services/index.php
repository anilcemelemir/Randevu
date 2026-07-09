<header class="page-header">
    <div>
        <span class="eyebrow">Admin</span>
        <h1>Fiyat listesi</h1>
    </div>
</header>

<section class="services-layout single-service-layout">
    <div class="panel">
        <h2>Nail Art</h2>
        <form class="service-card" method="post" action="/services/update">
            <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
            <div class="service-card-main">
                <label>
                    İşlem adı
                    <input type="text" value="Nail Art" readonly>
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
                <span class="helper-text">Randevu kayıtları otomatik olarak Nail Art üzerinden işlenir.</span>
                <button type="submit">Kaydet</button>
            </div>
        </form>
    </div>
</section>
