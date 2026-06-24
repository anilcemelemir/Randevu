<header class="page-header">
    <div>
        <span class="eyebrow">Admin</span>
        <h1>Gelir raporu</h1>
    </div>
</header>

<section class="grid two">
    <div class="panel">
        <h2>Filtre</h2>
        <form class="inline-form" method="get" action="/reports/revenue">
            <label>
                Dönem
                <select name="period">
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Aylık</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Yıllık</option>
                </select>
            </label>
            <label>
                Yıl
                <input type="number" name="year" value="<?= (int) $year ?>" min="2020" max="2100">
            </label>
            <label>
                Ay
                <select name="month">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) $month === $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <button type="submit">Raporu getir</button>
        </form>
    </div>

    <div class="panel tone">
        <h2>Toplam gelir</h2>
        <p class="revenue-total"><?= e(number_format((float) $total, 2, ',', '.')) ?> TL</p>
        <p><?= e(date('d.m.Y', strtotime($start))) ?> - <?= e(date('d.m.Y', strtotime($end))) ?></p>
    </div>
</section>

<section class="panel history-panel">
    <h2>Randevu gelirleri</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Müşteri</th>
                    <th>Uzman</th>
                    <th>İşlem</th>
                    <th>Fiyat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= e(date('d.m.Y H:i', strtotime($appointment['slot_start']))) ?></td>
                        <td><?= e($appointment['customer_name'] ?: '-') ?></td>
                        <td><?= e($appointment['specialist_name']) ?></td>
                        <td><?= e($appointment['service_name'] ?: '-') ?></td>
                        <td><?= e(number_format((float) $appointment['service_price'], 2, ',', '.')) ?> TL</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$appointments): ?>
                    <tr><td colspan="5" class="empty">Bu dönem için gelir kaydı yok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

