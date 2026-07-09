<section class="customer-hero-panel compact-hero">
    <div>
        <span class="eyebrow">Randevu oluştur</span>
        <h1>Size uygun uzmanı ve saat aralığını seçin.</h1>
        <p>Birden fazla ardışık saat bloğunu tek randevu olarak ayırabilirsiniz.</p>
    </div>
</section>

<section class="booking-layout">
    <div class="panel booking-search">
        <h2>Uygun saatleri ara</h2>
        <form class="booking-form" method="get" action="/appointments/new">
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
            <label>
                Süre
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
                <p class="calendar-hint">Gün seçtiğinizde o güne ait uygun saatler otomatik gelir.</p>
            </div>
            <button type="submit">Saatleri göster</button>
        </form>
    </div>

    <div class="panel booking-slots">
        <h2>Müsait saatler</h2>
        <?php if ($selectedSpecialist && $slots): ?>
            <form class="slot-list" method="post" action="/appointments/create">
                <input type="hidden" name="specialist_id" value="<?= (int) $selectedSpecialist ?>">
                <input type="hidden" name="duration" value="<?= (int) $selectedDuration ?>">
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
                    İşlem notu
                    <textarea name="note" rows="4" placeholder="Yapılacak işlemi kısa not olarak yazabilirsiniz"></textarea>
                </label>
                <button type="submit">Randevuyu oluştur</button>
            </form>
        <?php elseif ($selectedSpecialist): ?>
            <div class="empty-state">
                <strong>Bu gün için uygun saat bulunamadı.</strong>
                <p>Farklı bir tarih veya daha kısa süre seçerek tekrar deneyebilirsiniz.</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <strong>Önce uzman ve tarih seçin.</strong>
                <p>Seçiminize göre müsait saatler burada listelenecek.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
