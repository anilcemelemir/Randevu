<header class="page-header">
    <div>
        <span class="eyebrow">Randevu defteri</span>
        <h1>Randevu ekle</h1>
    </div>
</header>

<section class="booking-layout">
    <div class="panel booking-search">
        <h2>Saat ara</h2>
        <form class="booking-form" method="get" action="/appointments/new">
            <?php if ($user['role'] === 'admin'): ?>
                <label>
                    Uzman
                    <select name="specialist_id" required>
                        <option value="">Seçin</option>
                        <?php foreach ($specialists as $specialist): ?>
                            <option value="<?= (int) $specialist['id'] ?>" <?= (int) $selectedSpecialist === (int) $specialist['id'] ? 'selected' : '' ?>>
                                <?= e($specialist['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <div class="booking-form-row">
                <label>
                    Tarih
                    <input type="date" name="date" value="<?= e($selectedDate) ?>" min="<?= e(date('Y-m-d')) ?>" required>
                </label>
                <label>
                    Süre
                    <select name="duration" required>
                        <option value="1" <?= (int) $selectedDuration === 1 ? 'selected' : '' ?>>1 saat</option>
                        <option value="2" <?= (int) $selectedDuration === 2 ? 'selected' : '' ?>>2 saat</option>
                        <option value="3" <?= (int) $selectedDuration === 3 ? 'selected' : '' ?>>3 saat</option>
                    </select>
                </label>
            </div>
            <button type="submit">Müsait saatleri göster</button>
        </form>
    </div>

    <div class="panel booking-slots">
        <h2>Randevu bilgileri</h2>
        <?php if ($slots && $services): ?>
            <form class="slot-list" method="post" action="/appointments/create">
                <input type="hidden" name="duration" value="<?= (int) $selectedDuration ?>">
                <?php if ($user['role'] === 'admin'): ?>
                    <input type="hidden" name="specialist_id" value="<?= (int) $selectedSpecialist ?>">
                <?php endif; ?>
                <label>
                    Müşteri adı
                    <input type="text" name="customer_name" required>
                </label>
                <label>
                    Müşteri telefonu
                    <input type="text" name="customer_phone" placeholder="+90 5xx xxx xx xx">
                </label>
                <label>
                    İşlem
                    <select name="service_id" required>
                        <option value="">Seçin</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= (int) $service['id'] ?>">
                                <?= e($service['name']) ?> - <?= e(number_format((float) $service['price'], 2, ',', '.')) ?> TL
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <p class="helper-text">Seçtiğiniz süre için ardışık boş bloklar tek randevu olarak ayrılır.</p>
                <div class="slot-grid">
                    <?php foreach ($slots as $slot): ?>
                        <label class="slot-option <?= !empty($slot['is_soon']) ? 'soon' : '' ?>">
                            <input type="radio" name="slot_start" value="<?= e($slot['start']) ?>" required>
                            <span>
                                <?= e($slot['label']) ?>
                                <?php if (!empty($slot['is_soon'])): ?>
                                    <small>1 saat içinde olduğu için iptal edilemez</small>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <label>
                    İşlem açıklaması
                    <textarea name="note" rows="4" placeholder="Yapılan/yapılacak işlem detayları"></textarea>
                </label>
                <button type="submit">Deftere ekle</button>
            </form>
        <?php elseif ($user['role'] === 'admin' && !$selectedSpecialist): ?>
            <div class="empty-state">
                <strong>Önce uzman seçin.</strong>
                <p>Admin olarak randevuyu hangi uzman adına ekleyeceğinizi seçmelisiniz.</p>
            </div>
        <?php elseif (!$services): ?>
            <div class="empty-state">
                <strong>Aktif işlem bulunmuyor.</strong>
                <p>Admin fiyat listesine en az bir işlem eklemeli.</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <strong>Bu tarih için uygun saat bulunamadı.</strong>
                <p>Farklı bir tarih veya daha kısa süre seçerek tekrar deneyin.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
