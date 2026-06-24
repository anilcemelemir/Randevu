<header class="page-header">
    <div>
        <span class="eyebrow">Admin</span>
        <h1>Mesai ayarları</h1>
    </div>
</header>

<section class="grid two">
    <div class="panel">
        <h2>Uzman sec</h2>
        <form class="inline-form" method="get" action="/schedule">
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
            <button type="submit">Getir</button>
        </form>
    </div>

    <div class="panel">
        <h2>Haftalik mesai</h2>
        <?php if ($selectedSpecialist): ?>
            <form class="schedule-form" method="post" action="/schedule/update">
                <input type="hidden" name="specialist_id" value="<?= (int) $selectedSpecialist ?>">
                <?php foreach (weekdays() as $weekday => $label): ?>
                    <?php $row = $hours[$weekday] ?? null; ?>
                    <div class="schedule-row">
                        <label class="checkline">
                            <input type="checkbox" name="enabled[<?= (int) $weekday ?>]" value="1" <?= $row ? 'checked' : '' ?>>
                            <span><?= e($label) ?></span>
                        </label>
                        <input type="time" name="start_time[<?= (int) $weekday ?>]" value="<?= e($row['start_time'] ?? '09:00') ?>">
                        <input type="time" name="end_time[<?= (int) $weekday ?>]" value="<?= e($row['end_time'] ?? '18:00') ?>">
                    </div>
                <?php endforeach; ?>
                <button type="submit">Kaydet</button>
            </form>
        <?php else: ?>
            <p class="empty">Mesai saatlerini düzenlemek için bir uzman seçin.</p>
        <?php endif; ?>
    </div>
</section>
