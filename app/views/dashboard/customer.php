<?php
$activeAppointments = array_values(array_filter($appointments, fn (array $appointment) => $appointment['status'] === 'booked' && strtotime($appointment['slot_start']) >= time()));
$pastAppointments = array_values(array_filter($appointments, fn (array $appointment) => $appointment['status'] !== 'booked' || strtotime($appointment['slot_start']) < time()));
usort($activeAppointments, fn (array $a, array $b) => strtotime($a['slot_start']) <=> strtotime($b['slot_start']));
$nextAppointment = $activeAppointments[0] ?? null;
?>

<section class="customer-hero-panel">
    <div>
        <span class="eyebrow">Müşteri portalı</span>
        <h1>Randevularınızı sade ve hızlı yönetin.</h1>
        <p>Uygun uzmanı seçin, ardışık saat bloklarını tek seferde ayırın ve işlem notunuzu ekleyin.</p>
    </div>
    <a class="button" href="/appointments/new">Yeni randevu al</a>
</section>

<section class="customer-summary">
    <article>
        <span>Yaklaşan</span>
        <strong><?= count($activeAppointments) ?></strong>
        <small>aktif randevu</small>
    </article>
    <article>
        <span>Geçmiş</span>
        <strong><?= count($pastAppointments) ?></strong>
        <small>kayıt</small>
    </article>
    <article>
        <span>Sıradaki</span>
        <strong><?= $nextAppointment ? e(date('d.m', strtotime($nextAppointment['slot_start']))) : '-' ?></strong>
        <small><?= $nextAppointment ? e(date('H:i', strtotime($nextAppointment['slot_start']))) : 'randevu yok' ?></small>
    </article>
</section>

<section class="customer-grid">
    <div class="panel">
        <h2>Yaklaşan randevular</h2>
        <div class="appointment-cards">
            <?php foreach ($activeAppointments as $appointment): ?>
                <article class="appointment-card">
                    <div class="date-tile">
                        <strong><?= e(date('d', strtotime($appointment['slot_start']))) ?></strong>
                        <span><?= e(date('m.Y', strtotime($appointment['slot_start']))) ?></span>
                    </div>
                    <div class="appointment-main">
                        <h3><?= e($appointment['specialist_name']) ?></h3>
                        <p><?= e(date('H:i', strtotime($appointment['slot_start']))) ?> - <?= e(date('H:i', strtotime($appointment['slot_end']))) ?></p>
                        <small><?= e($appointment['note'] ?: 'İşlem notu eklenmedi') ?></small>
                    </div>
                    <form method="post" action="/appointments/cancel">
                        <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                        <button class="button ghost compact" type="submit">İptal et</button>
                    </form>
                </article>
            <?php endforeach; ?>

            <?php if (!$activeAppointments): ?>
                <div class="empty-state">
                    <strong>Henüz yaklaşan randevunuz yok.</strong>
                    <p>Size uygun uzmanı ve saati seçerek yeni bir randevu oluşturabilirsiniz.</p>
                    <a class="button" href="/appointments/new">Randevu al</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <aside class="panel customer-aside">
        <h2>Planlama ipuçları</h2>
        <div class="timeline">
            <div class="timeline-item">
                <strong>Tek işlem</strong>
                <span>1 saatlik standart blok seçin.</span>
            </div>
            <div class="timeline-item">
                <strong>Kombine bakım</strong>
                <span>2 veya 3 saatlik ardışık blokları tek seferde ayırın.</span>
            </div>
            <div class="timeline-item">
                <strong>İşlem notu</strong>
                <span>Uzmanın hazırlık yapabilmesi için kısa not bırakın.</span>
            </div>
        </div>
    </aside>
</section>

<?php if ($pastAppointments): ?>
    <section class="panel history-panel">
        <h2>Geçmiş randevular</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Uzman</th>
                        <th>Tarih</th>
                        <th>Saat</th>
                        <th>Not</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pastAppointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['specialist_name']) ?></td>
                            <td><?= e(date('d.m.Y', strtotime($appointment['slot_start']))) ?></td>
                            <td><?= e(date('H:i', strtotime($appointment['slot_start']))) ?> - <?= e(date('H:i', strtotime($appointment['slot_end']))) ?></td>
                            <td><?= e($appointment['note'] ?: '-') ?></td>
                            <td><span class="badge"><?= e(status_label($appointment['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
