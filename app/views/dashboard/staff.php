<?php
$monthLabel = tr_month((int) date('n', $monthStartTs)) . ' ' . date('Y', $monthStartTs);
?>

<header class="page-header">
    <div>
        <span class="eyebrow"><?= e(role_label($user['role'])) ?></span>
        <h1>Salon takvimi</h1>
    </div>
</header>

<section class="panel calendar-panel">
    <div class="agenda-nav">
        <a class="agenda-nav-btn" href="/dashboard?month=<?= e($prevMonth) ?>" aria-label="Önceki ay">‹</a>
        <div class="agenda-range">
            <strong><?= e($monthLabel) ?></strong>
        </div>
        <a class="agenda-nav-btn" href="/dashboard?month=<?= e($nextMonth) ?>" aria-label="Sonraki ay">›</a>
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
            <a class="<?= $classes ?>" href="/day?date=<?= e($cellKey) ?>">
                <span class="month-daynum"><?= $d ?></span>
                <?php if ($count > 0): ?>
                    <span class="month-count"><?= (int) $count ?></span>
                <?php endif; ?>
            </a>
        <?php endfor; ?>
    </div>

    <p class="calendar-hint">Bir g&uuml;ne dokunun; o g&uuml;n&uuml;n saatleri a&ccedil;ılır, boş saate randevu ekleyebilirsiniz.</p>
</section>
