<?php
$specialistQ = $specialistFilter > 0 ? '&specialist_id=' . (int) $specialistFilter : '';
$monthLabel = tr_month((int) date('n', $monthStartTs)) . ' ' . date('Y', $monthStartTs);
$addUrl = '/day?date=' . date('Y-m-d') . $specialistQ;
?>

<header class="page-header">
    <div>
        <span class="eyebrow"><?= e(role_label($user['role'])) ?></span>
        <h1>Salon takvimi</h1>
    </div>
    <a class="button" href="<?= e($addUrl) ?>">Randevu ekle</a>
</header>

<section class="panel calendar-panel">
    <div class="agenda-toolbar">
        <form class="specialist-filter" method="get" action="/dashboard">
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <label>
                Uzman
                <select name="specialist_id" onchange="this.form.submit()">
                    <?php if ($user['role'] === 'admin'): ?>
                        <option value="0">T&uuml;m uzmanlar</option>
                    <?php endif; ?>
                    <?php foreach ($specialists as $specialist): ?>
                        <option value="<?= (int) $specialist['id'] ?>" <?= (int) $specialistFilter === (int) $specialist['id'] ? 'selected' : '' ?>>
                            <?= e($specialist['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    </div>

    <div class="agenda-nav">
        <a class="agenda-nav-btn" href="/dashboard?month=<?= e($prevMonth) ?><?= e($specialistQ) ?>" aria-label="Önceki ay">‹</a>
        <div class="agenda-range">
            <strong><?= e($monthLabel) ?></strong>
        </div>
        <a class="agenda-nav-btn" href="/dashboard?month=<?= e($nextMonth) ?><?= e($specialistQ) ?>" aria-label="Sonraki ay">›</a>
    </div>

    <div class="month-grid">
        <?php foreach ([1, 2, 3, 4, 5, 6, 7] as $wd): ?>
            <span class="month-weekday"><?= e(tr_weekday_short($wd)) ?></span>
        <?php endforeach; ?>

        <?php for ($b = 0; $b < $lead; $b++): ?>
            <span class="month-cell is-blank" aria-hidden="true"></span>
        <?php endfor; ?>

        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
            <?php
            $cellTs = mktime(0, 0, 0, (int) date('n', $monthStartTs), $d, (int) date('Y', $monthStartTs));
            $cellKey = date('Y-m-d', $cellTs);
            $count = $countByDay[$cellKey] ?? 0;
            $isToday = $cellKey === $todayKey;
            $isPast = $cellKey < $todayKey;
            $classes = 'month-cell';
            if ($isToday) {
                $classes .= ' is-today';
            } elseif ($isPast) {
                $classes .= ' is-past';
            }
            ?>
            <a class="<?= $classes ?>" href="/day?date=<?= e($cellKey) ?><?= e($specialistQ) ?>">
                <span class="month-daynum"><?= $d ?></span>
                <?php if ($count > 0): ?>
                    <span class="month-count"><?= (int) $count ?></span>
                <?php endif; ?>
            </a>
        <?php endfor; ?>
    </div>

    <p class="calendar-hint">Bir g&uuml;ne dokunun; o g&uuml;n&uuml;n saatleri a&ccedil;ılır, boş saate randevu ekleyebilirsiniz.</p>
</section>
