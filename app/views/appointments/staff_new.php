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
                        <option value="">Se&ccedil;in</option>
                        <?php foreach ($specialists as $specialist): ?>
                            <option value="<?= (int) $specialist['id'] ?>" <?= (int) $selectedSpecialist === (int) $specialist['id'] ? 'selected' : '' ?>>
                                <?= e($specialist['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <label>
                S&uuml;re
                <select name="duration" required>
                    <option value="1" <?= (int) $selectedDuration === 1 ? 'selected' : '' ?>>1 saat</option>
                    <option value="2" <?= (int) $selectedDuration === 2 ? 'selected' : '' ?>>2 saat</option>
                    <option value="3" <?= (int) $selectedDuration === 3 ? 'selected' : '' ?>>3 saat</option>
                </select>
            </label>
            <div class="booking-date">
                <label class="field-caption" for="booking-date-input">Tarih</label>
                <input type="date" id="booking-date-input" name="date" value="<?= e($selectedDate) ?>" min="<?= e(date('Y-m-d')) ?>" required>
                <div class="calendar" data-calendar data-calendar-for="booking-date-input" hidden></div>
                <p class="calendar-hint">G&uuml;n se&ccedil;tiğinizde o g&uuml;ne ait uygun saatler otomatik gelir.</p>
            </div>
            <button type="submit">M&uuml;sait saatleri g&ouml;ster</button>
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
                    M&uuml;şteri adı
                    <input type="text" name="customer_name" required>
                </label>
                <label>
                    M&uuml;şteri telefonu
                    <input type="text" name="customer_phone" placeholder="+90 5xx xxx xx xx">
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
                <p class="helper-text">Se&ccedil;tiğiniz s&uuml;re i&ccedil;in ardışık boş bloklar tek randevu olarak ayrılır.</p>
                <div class="slot-grid">
                    <?php foreach ($slots as $slot): ?>
                        <label class="slot-option <?= !empty($slot['is_soon']) ? 'soon' : '' ?>">
                            <input type="radio" name="slot_start" value="<?= e($slot['start']) ?>" required>
                            <span>
                                <?= e($slot['label']) ?>
                                <?php if (!empty($slot['is_soon'])): ?>
                                    <small>1 saat i&ccedil;inde olduğu i&ccedil;in iptal edilemez</small>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <label>
                    İşlem a&ccedil;ıklaması
                    <textarea name="note" rows="4" placeholder="Yapılan/yapılacak işlem detayları"></textarea>
                </label>
                <button type="submit">Deftere ekle</button>
            </form>
        <?php elseif ($user['role'] === 'admin' && !$selectedSpecialist): ?>
            <div class="empty-state">
                <strong>&Ouml;nce uzman se&ccedil;in.</strong>
                <p>Admin olarak randevuyu hangi uzman adına ekleyeceğinizi se&ccedil;melisiniz.</p>
            </div>
        <?php elseif (!$services): ?>
            <div class="empty-state">
                <strong>Aktif işlem bulunmuyor.</strong>
                <p>Admin fiyat listesine en az bir işlem eklemeli.</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <strong>Bu tarih i&ccedil;in uygun saat bulunamadı.</strong>
                <p>Farklı bir tarih veya daha kısa s&uuml;re se&ccedil;erek tekrar deneyin.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
