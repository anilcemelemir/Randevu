<?php

declare(strict_types=1);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$path = current_path();

if ($path === '/logout') {
    Auth::logout();
    redirect('/');
}

if (!Auth::check() && !in_array($path, ['/', '/register'], true)) {
    redirect('/');
}

if (Auth::check() && in_array($path, ['/', '/register'], true)) {
    redirect('/dashboard');
}

match ($path) {
    '/' => handle_login(),
    '/register' => handle_register_disabled(),
    '/dashboard' => handle_dashboard(),
    '/day' => handle_day_view(),
    '/appointments/new' => handle_staff_new_appointment(),
    '/appointments/create' => handle_staff_create_appointment(),
    '/appointments/cancel' => handle_staff_cancel_appointment(),
    '/appointments/approve' => handle_appointment_approve(),
    '/appointments/history' => handle_appointment_history(),
    '/specialists' => handle_specialists(),
    '/specialists/create' => handle_specialist_create(),
    '/specialists/update' => handle_specialist_update(),
    '/specialists/delete' => handle_specialist_delete(),
    '/services' => handle_services(),
    '/services/create' => handle_service_create(),
    '/services/update' => handle_service_update(),
    '/services/delete' => handle_service_delete(),
    '/reports/revenue' => handle_revenue_report(),
    '/settings' => handle_settings(),
    '/settings/update' => handle_settings_update(),
    '/account/password' => handle_password(),
    '/account/password/update' => handle_password_update(),
    '/profile' => handle_profile(),
    '/profile/update' => handle_profile_update(),
    '/schedule' => handle_schedule(),
    '/schedule/update' => handle_schedule_update(),
    '/blocks/create' => handle_block_create(),
    default => not_found(),
};

function handle_login(): void
{
    $error = null;

    if (is_post()) {
        if (Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            redirect('/dashboard');
        }

        $error = 'E-posta veya şifre hatalı.';
    }

    view('auth/login.php', ['title' => 'Giriş', 'error' => $error, 'authPage' => true]);
}

function handle_register(): void
{
    $error = null;

    if (is_post()) {
        $name = trim($_POST['name'] ?? '');
        $email = normalize_email($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || !is_valid_plain_email($email) || strlen($password) < 6) {
            $error = 'Lütfen geçerli bilgiler girin. Şifre en az 6 karakter olmalı.';
        } else {
            try {
                $stmt = db()->prepare("INSERT INTO users (name, email, role, password_hash) VALUES (:name, :email, 'customer', :password_hash)");
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                Auth::login($email, $password);
                redirect('/dashboard');
            } catch (PDOException) {
                $error = 'Bu e-posta adresi zaten kayıtlı.';
            }
        }
    }

    view('auth/register.php', ['title' => 'Kayıt Ol', 'error' => $error, 'authPage' => true]);
}

function handle_register_disabled(): void
{
    flash('Müşteri kaydı kapalıdır. Randevular salon ekibi tarafından oluşturulur.', 'warning');
    redirect('/');
}

function handle_dashboard(): void
{
    $user = Auth::user();

    if ($user['role'] === 'customer') {
        Auth::logout();
        flash('Müşteri paneli kapalıdır. Randevular salon ekibi tarafından tutulur.', 'warning');
        redirect('/');
    }

    $specialists = specialists();
    $specialistIds = array_map(static fn (array $specialist): int => (int) $specialist['id'], $specialists);

    // Uzman filtresi (0 = tüm uzmanlar). Uzman rolü varsayılan olarak kendini görür.
    if (array_key_exists('specialist_id', $_GET)) {
        $specialistFilter = (int) $_GET['specialist_id'];
    } else {
        $specialistFilter = $user['role'] === 'specialist' ? (int) $user['id'] : 0;
    }
    if ($specialistFilter > 0 && !in_array($specialistFilter, $specialistIds, true)) {
        $specialistFilter = 0;
    }

    // Uzman rolü "tüm uzmanlar" (0) sorgusu yapamaz; her zaman belirli bir uzman seçilidir.
    if ($user['role'] === 'specialist' && $specialistFilter <= 0) {
        $specialistFilter = (int) $user['id'];
    }

    // Görüntülenen ay (YYYY-MM), varsayılan içinde bulunulan ay.
    $monthParam = (string) ($_GET['month'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}$/', $monthParam) || strtotime($monthParam . '-01') === false) {
        $monthParam = date('Y-m');
    }
    $monthStartTs = strtotime($monthParam . '-01 00:00:00');
    $monthStart = date('Y-m-d', $monthStartTs);
    $monthEnd = date('Y-m-d', strtotime('first day of next month', $monthStartTs)); // hariç

    // Aktif (iptal olmayan) randevuları güne göre say.
    $conditions = ['a.slot_start >= :start', 'a.slot_start < :end', "a.status IN ('booked', 'completed')"];
    $params = ['start' => $monthStart . ' 00:00:00', 'end' => $monthEnd . ' 00:00:00'];
    if ($specialistFilter > 0) {
        $conditions[] = 'a.specialist_id = :specialist_id';
        $params['specialist_id'] = $specialistFilter;
    }
    $where = 'WHERE ' . implode(' AND ', $conditions);

    $countStmt = db()->prepare(
        "SELECT date(a.slot_start) AS d, COUNT(*) AS c
         FROM appointments a
         $where
         GROUP BY date(a.slot_start)"
    );
    $countStmt->execute($params);

    $countByDay = [];
    foreach ($countStmt->fetchAll() as $row) {
        $countByDay[$row['d']] = (int) $row['c'];
    }

    view('dashboard/staff.php', [
        'title' => 'Salon Takvimi',
        'user' => $user,
        'specialists' => $specialists,
        'specialistFilter' => $specialistFilter,
        'month' => $monthParam,
        'monthStartTs' => $monthStartTs,
        'daysInMonth' => (int) date('t', $monthStartTs),
        'lead' => (int) date('N', $monthStartTs) - 1, // Pazartesi = 0 kaydırma
        'countByDay' => $countByDay,
        'todayKey' => date('Y-m-d'),
        'prevMonth' => date('Y-m', strtotime('-1 month', $monthStartTs)),
        'nextMonth' => date('Y-m', strtotime('+1 month', $monthStartTs)),
    ]);
}

function handle_day_view(): void
{
    Auth::requireRole(['admin', 'specialist']);

    $user = Auth::user();
    $specialists = specialists();
    $specialistIds = array_map(static fn (array $specialist): int => (int) $specialist['id'], $specialists);

    $date = (string) ($_GET['date'] ?? date('Y-m-d'));
    if (strtotime($date) === false) {
        $date = date('Y-m-d');
    }
    $date = date('Y-m-d', strtotime($date));

    // Etkin uzman: admin seçer (yoksa ilk uzman), uzman kendi takvimini görür.
    if ($user['role'] === 'specialist') {
        $requested = array_key_exists('specialist_id', $_GET) ? (int) $_GET['specialist_id'] : (int) $user['id'];
        $specialistId = ($requested > 0 && in_array($requested, $specialistIds, true)) ? $requested : (int) $user['id'];
    } else {
        $requested = (int) ($_GET['specialist_id'] ?? 0);
        $specialistId = ($requested > 0 && in_array($requested, $specialistIds, true)) ? $requested : (int) ($specialistIds[0] ?? 0);
    }

    // Randevuyu sadece admin veya kendi adına uzman ekleyebilir/işleyebilir.
    $canBook = $user['role'] === 'admin' || (int) $specialistId === (int) $user['id'];

    $schedule = $specialistId > 0
        ? day_slots($specialistId, $date)
        : ['open' => false, 'rows' => [], 'appointments' => []];

    view('day/index.php', [
        'title' => 'Gün planı',
        'user' => $user,
        'specialists' => $specialists,
        'specialistId' => $specialistId,
        'date' => $date,
        'schedule' => $schedule,
        'services' => active_services(),
        'canBook' => $canBook,
        'backMonth' => date('Y-m', strtotime($date)),
        'specialistFilter' => $specialistId,
    ]);
}

function handle_appointment_history(): void
{
    Auth::requireRole(['admin', 'specialist']);

    $statusFilter = $_GET['status'] ?? 'all';
    $allowedStatuses = ['all', 'booked', 'completed', 'cancelled'];
    if (!in_array($statusFilter, $allowedStatuses, true)) {
        $statusFilter = 'all';
    }

    $specialists = specialists();
    $specialistIds = array_map(static fn (array $specialist): int => (int) $specialist['id'], $specialists);
    $specialistFilter = (int) ($_GET['specialist_id'] ?? 0);
    if ($specialistFilter > 0 && !in_array($specialistFilter, $specialistIds, true)) {
        $specialistFilter = 0;
    }

    $dateFrom = trim($_GET['date_from'] ?? '');
    if ($dateFrom !== '' && strtotime($dateFrom) === false) {
        $dateFrom = '';
    }

    $dateTo = trim($_GET['date_to'] ?? '');
    if ($dateTo !== '' && strtotime($dateTo) === false) {
        $dateTo = '';
    }

    $customerQuery = trim($_GET['customer'] ?? '');

    $conditions = [];
    $params = [];

    if ($statusFilter !== 'all') {
        $conditions[] = 'a.status = :status';
        $params['status'] = $statusFilter;
    }

    if ($specialistFilter > 0) {
        $conditions[] = 'a.specialist_id = :specialist_id';
        $params['specialist_id'] = $specialistFilter;
    }

    if ($dateFrom !== '') {
        $conditions[] = 'date(a.slot_start) >= :date_from';
        $params['date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $conditions[] = 'date(a.slot_start) <= :date_to';
        $params['date_to'] = $dateTo;
    }

    if ($customerQuery !== '') {
        $conditions[] = "(COALESCE(a.customer_name, c.name, '') LIKE :customer_query)";
        $params['customer_query'] = '%' . $customerQuery . '%';
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $stmt = db()->prepare(
        "SELECT a.*, COALESCE(a.customer_name, c.name) AS customer_name, s.name AS specialist_name
         FROM appointments a
         LEFT JOIN users c ON c.id = a.customer_id
         JOIN users s ON s.id = a.specialist_id
         $where
         ORDER BY a.slot_start DESC"
    );
    $stmt->execute($params);

    view('appointments/history.php', [
        'title' => 'Randevu Geçmişi',
        'appointments' => $stmt->fetchAll(),
        'specialists' => $specialists,
        'statusFilter' => $statusFilter,
        'specialistFilter' => $specialistFilter,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'customerQuery' => $customerQuery,
        'user' => Auth::user(),
    ]);
}

function handle_staff_new_appointment(): void
{
    Auth::requireRole(['admin', 'specialist']);

    $user = Auth::user();
    $specialistId = $user['role'] === 'admin'
        ? (int) ($_GET['specialist_id'] ?? 0)
        : (int) $user['id'];
    $date = $_GET['date'] ?? date('Y-m-d');
    $duration = max(1, min(3, (int) ($_GET['duration'] ?? 1)));
    $slots = $specialistId > 0 ? available_slots($specialistId, $date, $duration) : [];

    view('appointments/staff_new.php', [
        'title' => 'Randevu Ekle',
        'services' => active_services(),
        'specialists' => specialists(),
        'selectedSpecialist' => $specialistId,
        'selectedDate' => $date,
        'selectedDuration' => $duration,
        'slots' => $slots,
        'user' => $user,
    ]);
}

function handle_staff_create_appointment(): void
{
    Auth::requireRole(['admin', 'specialist']);

    if (!is_post()) {
        redirect('/appointments/new');
    }

    $user = Auth::user();
    $specialistId = $user['role'] === 'admin'
        ? (int) ($_POST['specialist_id'] ?? 0)
        : (int) $user['id'];
    $duration = max(1, min(3, (int) ($_POST['duration'] ?? 1)));
    $slotStart = $_POST['slot_start'] ?? '';
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $serviceIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['service_ids'] ?? [])))));
    $note = trim($_POST['note'] ?? '');

    // Başarı/hata sonrası ilgili günün planına geri dön.
    $redirectDate = (string) ($_POST['date'] ?? '');
    if (strtotime($redirectDate) === false) {
        $redirectDate = strtotime($slotStart) !== false ? date('Y-m-d', strtotime($slotStart)) : date('Y-m-d');
    }
    $dayUrl = '/day?date=' . rawurlencode($redirectDate) . '&specialist_id=' . $specialistId;

    if ($customerName === '' || strtotime($slotStart) === false || !$serviceIds) {
        flash('Müşteri, işlem ve saat bilgileri zorunludur.', 'error');
        redirect($dayUrl);
    }

    $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
    $servicesStmt = db()->prepare("SELECT * FROM services WHERE is_active = 1 AND id IN ($placeholders) ORDER BY name");
    $servicesStmt->execute($serviceIds);
    $selectedServices = $servicesStmt->fetchAll();
    if (count($selectedServices) !== count($serviceIds)) {
        flash('Seçilen işlem bulunamadı.', 'error');
        redirect($dayUrl);
    }

    $serviceNames = array_map(static fn (array $service): string => $service['name'], $selectedServices);
    $serviceTotal = array_sum(array_map(static fn (array $service): float => (float) $service['price'], $selectedServices));
    $primaryServiceId = (int) $selectedServices[0]['id'];

    $date = date('Y-m-d', strtotime($slotStart));
    $validSlots = array_column(available_slots($specialistId, $date, $duration), 'start');
    if (!in_array($slotStart, $validSlots, true)) {
        flash('Seçilen saat artık uygun değil.', 'error');
        redirect($dayUrl);
    }

    $stmt = db()->prepare(
        "INSERT INTO appointments
            (customer_id, specialist_id, service_id, customer_name, customer_phone, service_name, service_price, slot_start, slot_end, note)
         VALUES
            (:customer_id, :specialist_id, :service_id, :customer_name, :customer_phone, :service_name, :service_price, :slot_start, :slot_end, :note)"
    );
    $stmt->execute([
        'customer_id' => default_customer_id(),
        'specialist_id' => $specialistId,
        'service_id' => $primaryServiceId,
        'customer_name' => $customerName,
        'customer_phone' => $customerPhone,
        'service_name' => implode(', ', $serviceNames),
        'service_price' => $serviceTotal,
        'slot_start' => $slotStart,
        'slot_end' => date('Y-m-d H:i:s', strtotime($slotStart . ' +' . $duration . ' hour')),
        'note' => $note,
    ]);

    $appointmentId = (int) db()->lastInsertId();
    $serviceInsert = db()->prepare(
        'INSERT INTO appointment_services (appointment_id, service_id, service_name, service_price)
         VALUES (:appointment_id, :service_id, :service_name, :service_price)'
    );
    foreach ($selectedServices as $service) {
        $serviceInsert->execute([
            'appointment_id' => $appointmentId,
            'service_id' => $service['id'],
            'service_name' => $service['name'],
            'service_price' => $service['price'],
        ]);
    }

    flash('Randevu deftere eklendi.');
    redirect($dayUrl);
}

function handle_staff_cancel_appointment(): void
{
    Auth::requireRole(['admin', 'specialist']);

    if (!is_post()) {
        redirect('/dashboard');
    }

    $user = Auth::user();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        redirect('/dashboard');
    }

    if ($user['role'] === 'specialist' && (int) $appointment['specialist_id'] !== (int) $user['id']) {
        redirect('/dashboard');
    }

    $backUrl = '/day?date=' . date('Y-m-d', strtotime($appointment['slot_start'])) . '&specialist_id=' . (int) $appointment['specialist_id'];

    if (!in_array($appointment['status'], ['booked', 'completed'], true)) {
        flash('Sadece planlanan veya onaylanan randevular iptal edilebilir.', 'error');
        redirect($backUrl);
    }

    if ($appointment['status'] === 'booked' && strtotime($appointment['slot_start']) <= time() + 3600) {
        flash('Randevuya 1 saatten az kaldığı için iptal edilemez.', 'error');
        redirect($backUrl);
    }

    $update = db()->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id");
    $update->execute(['id' => $id]);
    flash('Randevu iptal edildi.');
    redirect($backUrl);
}

function handle_appointment_approve(): void
{
    Auth::requireRole(['admin', 'specialist']);

    if (!is_post()) {
        redirect('/dashboard');
    }

    $user = Auth::user();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        redirect('/dashboard');
    }

    if ($user['role'] === 'specialist' && (int) $appointment['specialist_id'] !== (int) $user['id']) {
        redirect('/dashboard');
    }

    $backUrl = '/day?date=' . date('Y-m-d', strtotime($appointment['slot_start'])) . '&specialist_id=' . (int) $appointment['specialist_id'];

    if ($appointment['status'] !== 'booked') {
        flash('Sadece planlanan randevular onaylanabilir.', 'error');
        redirect($backUrl);
    }

    $update = db()->prepare("UPDATE appointments SET status = 'completed' WHERE id = :id");
    $update->execute(['id' => $id]);
    flash('Randevu onaylandı ve gelir hesabına işlendi.');
    redirect($backUrl);
}

function handle_new_appointment(): void
{
    Auth::requireRole(['customer']);

    $specialistId = (int) ($_GET['specialist_id'] ?? 0);
    $date = $_GET['date'] ?? date('Y-m-d');
    $duration = max(1, min(3, (int) ($_GET['duration'] ?? 1)));
    $slots = $specialistId > 0 ? available_slots($specialistId, $date, $duration) : [];

    view('appointments/new.php', [
        'title' => 'Yeni Randevu',
        'specialists' => specialists(),
        'selectedSpecialist' => $specialistId,
        'selectedDate' => $date,
        'selectedDuration' => $duration,
        'slots' => $slots,
    ]);
}

function handle_create_appointment(): void
{
    Auth::requireRole(['customer']);

    if (!is_post()) {
        redirect('/appointments/new');
    }

    $user = Auth::user();
    $specialistId = (int) ($_POST['specialist_id'] ?? 0);
    $duration = max(1, min(3, (int) ($_POST['duration'] ?? 1)));
    $slotStart = $_POST['slot_start'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $slotEnd = date('Y-m-d H:i:s', strtotime($slotStart . ' +' . $duration . ' hour'));
    $date = date('Y-m-d', strtotime($slotStart));

    $validSlots = array_column(available_slots($specialistId, $date, $duration), 'start');
    if (!$specialistId || !in_array($slotStart, $validSlots, true)) {
        flash('Seçilen saat artık uygun değil.', 'error');
        redirect('/appointments/new?specialist_id=' . $specialistId . '&date=' . $date . '&duration=' . $duration);
    }

    $stmt = db()->prepare(
        "INSERT INTO appointments (customer_id, specialist_id, slot_start, slot_end, note)
         VALUES (:customer_id, :specialist_id, :slot_start, :slot_end, :note)"
    );
    $stmt->execute([
        'customer_id' => $user['id'],
        'specialist_id' => $specialistId,
        'slot_start' => $slotStart,
        'slot_end' => $slotEnd,
        'note' => $note,
    ]);

    flash('Randevunuz oluşturuldu.');
    redirect('/dashboard');
}

function handle_cancel_appointment(): void
{
    if (!is_post()) {
        redirect('/dashboard');
    }

    $user = Auth::user();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        redirect('/dashboard');
    }

    if ($user['role'] !== 'admin' && (int) $appointment['customer_id'] !== (int) $user['id']) {
        redirect('/dashboard');
    }

    if (strtotime($appointment['slot_start']) <= time() + 3600) {
        flash('Randevuya 1 saatten az kaldığı için iptal edilemez.', 'error');
        redirect('/dashboard');
    }

    $update = db()->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id");
    $update->execute(['id' => $id]);
    flash('Randevu iptal edildi.');
    redirect('/dashboard');
}

function handle_create_appointment_v2(): void
{
    Auth::requireRole(['customer']);

    if (!is_post()) {
        redirect('/appointments/new');
    }

    $user = Auth::user();
    $specialistId = (int) ($_POST['specialist_id'] ?? 0);
    $duration = max(1, min(3, (int) ($_POST['duration'] ?? 1)));
    $slotStart = $_POST['slot_start'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if (strtotime($slotStart) === false) {
        flash('Seçilen saat geçersiz.', 'error');
        redirect('/appointments/new');
    }

    $slotEnd = date('Y-m-d H:i:s', strtotime($slotStart . ' +' . $duration . ' hour'));
    $date = date('Y-m-d', strtotime($slotStart));
    $validSlots = array_column(available_slots($specialistId, $date, $duration), 'start');

    if (!$specialistId || !in_array($slotStart, $validSlots, true)) {
        flash('Seçilen saat artık uygun değil.', 'error');
        redirect('/appointments/new?specialist_id=' . $specialistId . '&date=' . $date . '&duration=' . $duration);
    }

    $stmt = db()->prepare(
        "INSERT INTO appointments (customer_id, specialist_id, slot_start, slot_end, note)
         VALUES (:customer_id, :specialist_id, :slot_start, :slot_end, :note)"
    );
    $stmt->execute([
        'customer_id' => $user['id'],
        'specialist_id' => $specialistId,
        'slot_start' => $slotStart,
        'slot_end' => $slotEnd,
        'note' => $note,
    ]);

    if (strtotime($slotStart) <= time() + 3600) {
        flash('Randevunuz oluşturuldu. Randevuya 1 saatten az kaldığı için bu randevu iptal edilemez.', 'warning');
    } else {
        flash('Randevunuz oluşturuldu.');
    }

    redirect('/dashboard');
}

function handle_cancel_appointment_v2(): void
{
    if (!is_post()) {
        redirect('/dashboard');
    }

    $user = Auth::user();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM appointments WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        redirect('/dashboard');
    }

    if ($user['role'] !== 'admin' && (int) $appointment['customer_id'] !== (int) $user['id']) {
        redirect('/dashboard');
    }

    if (strtotime($appointment['slot_start']) <= time() + 3600) {
        flash('Randevuya 1 saatten az kaldığı için iptal edilemez.', 'error');
        redirect('/dashboard');
    }

    $update = db()->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id");
    $update->execute(['id' => $id]);
    flash('Randevu iptal edildi.');
    redirect('/dashboard');
}

function handle_specialists(): void
{
    Auth::requireRole(['admin']);

    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = :id AND role = 'specialist' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $specialist = $stmt->fetch();

        if (!$specialist) {
            redirect('/specialists');
        }

        $hoursStmt = db()->prepare('SELECT * FROM working_hours WHERE specialist_id = :id ORDER BY weekday');
        $hoursStmt->execute(['id' => $id]);

        $appointmentsStmt = db()->prepare(
            "SELECT a.*, c.name AS customer_name
             FROM appointments a
             JOIN users c ON c.id = a.customer_id
             WHERE a.specialist_id = :id
             ORDER BY a.slot_start DESC
             LIMIT 20"
        );
        $appointmentsStmt->execute(['id' => $id]);

        view('specialists/show.php', [
            'title' => $specialist['name'],
            'specialist' => $specialist,
            'hours' => $hoursStmt->fetchAll(),
            'appointments' => $appointmentsStmt->fetchAll(),
        ]);
        return;
    }

    $cards = db()->query(
        "SELECT u.*,
            COUNT(a.id) AS appointment_count,
            MIN(CASE WHEN a.status = 'booked' AND a.slot_start >= datetime('now') THEN a.slot_start END) AS next_appointment
         FROM users u
         LEFT JOIN appointments a ON a.specialist_id = u.id
         WHERE u.role = 'specialist'
         GROUP BY u.id
         ORDER BY u.name"
    )->fetchAll();

    view('specialists/index.php', [
        'title' => 'Uzmanlar',
        'specialists' => $cards,
    ]);
}

function handle_specialist_create(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/specialists');
    }

    $name = trim($_POST['name'] ?? '');
    $email = normalize_email($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatar = uploaded_asset_path('avatar', '/specialists');

    if ($name === '' || !is_valid_plain_email($email) || strlen($password) < 6) {
        flash('Uzman bilgileri eksik veya hatalı.', 'error');
        redirect('/specialists');
    }

    try {
        $stmt = db()->prepare(
            "INSERT INTO users (name, email, role, password_hash, specialty, phone, bio, avatar)
             VALUES (:name, :email, 'specialist', :password_hash, :specialty, :phone, :bio, :avatar)"
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'specialty' => $specialty,
            'phone' => $phone,
            'bio' => $bio,
            'avatar' => $avatar,
        ]);

        $specialistId = (int) db()->lastInsertId();
        $schedule = db()->prepare('INSERT INTO working_hours (specialist_id, weekday, start_time, end_time) VALUES (:specialist_id, :weekday, :start_time, :end_time)');
        for ($day = 1; $day <= 5; $day++) {
            $schedule->execute([
                'specialist_id' => $specialistId,
                'weekday' => $day,
                'start_time' => '09:00',
                'end_time' => '18:00',
            ]);
        }

        flash('Uzman profili oluşturuldu.');
    } catch (PDOException) {
        flash('Bu e-posta adresi zaten kayıtlı.', 'error');
    }

    redirect('/specialists');
}

function handle_specialist_update(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/specialists');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare("SELECT * FROM users WHERE id = :id AND role = 'specialist' LIMIT 1");
    $stmt->execute(['id' => $id]);
    $specialist = $stmt->fetch();

    if (!$specialist) {
        flash('Uzman bulunamadı.', 'error');
        redirect('/specialists');
    }

    $name = trim($_POST['name'] ?? '');
    $email = normalize_email($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatar = uploaded_asset_path('avatar', '/specialists?id=' . $id) ?? $specialist['avatar'];

    if ($name === '' || !is_valid_plain_email($email)) {
        flash('Uzman bilgileri eksik veya hatalı.', 'error');
        redirect('/specialists?id=' . $id);
    }

    if ($password !== '' && strlen($password) < 6) {
        flash('Yeni şifre en az 6 karakter olmalı.', 'error');
        redirect('/specialists?id=' . $id);
    }

    try {
        $params = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'specialty' => $specialty,
            'phone' => $phone,
            'bio' => $bio,
            'avatar' => $avatar,
        ];

        $passwordSql = '';
        if ($password !== '') {
            $passwordSql = ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $update = db()->prepare(
            "UPDATE users
             SET name = :name,
                 email = :email,
                 specialty = :specialty,
                 phone = :phone,
                 bio = :bio,
                 avatar = :avatar
                 $passwordSql
             WHERE id = :id AND role = 'specialist'"
        );
        $update->execute($params);

        flash('Uzman bilgileri güncellendi.');
    } catch (PDOException) {
        flash('Bu e-posta adresi başka bir kullanıcıda kayıtlı.', 'error');
    }

    redirect('/specialists?id=' . $id);
}

function handle_specialist_delete(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/specialists');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare("SELECT id FROM users WHERE id = :id AND role = 'specialist' LIMIT 1");
    $stmt->execute(['id' => $id]);

    if (!$stmt->fetch()) {
        flash('Uzman bulunamadı.', 'error');
        redirect('/specialists');
    }

    $delete = db()->prepare("DELETE FROM users WHERE id = :id AND role = 'specialist'");
    $delete->execute(['id' => $id]);

    flash('Uzman ve ilişkili mesai/randevu kayıtları silindi.');
    redirect('/specialists');
}

function handle_schedule(): void
{
    Auth::requireRole(['admin']);

    $specialistId = (int) ($_GET['specialist_id'] ?? 0);
    $hours = [];

    if ($specialistId > 0) {
        $stmt = db()->prepare('SELECT * FROM working_hours WHERE specialist_id = :specialist_id ORDER BY weekday');
        $stmt->execute(['specialist_id' => $specialistId]);
        foreach ($stmt->fetchAll() as $row) {
            $hours[(int) $row['weekday']] = $row;
        }
    }

    view('schedule/index.php', [
        'title' => 'Mesai Ayarları',
        'specialists' => specialists(),
        'selectedSpecialist' => $specialistId,
        'hours' => $hours,
    ]);
}

function handle_settings(): void
{
    Auth::requireRole(['admin']);

    view('settings/index.php', [
        'title' => 'Ayarlar',
        'settings' => app_settings(),
    ]);
}

function handle_services(): void
{
    Auth::requireRole(['admin']);

    view('services/index.php', [
        'title' => 'İşlem Fiyat Listesi',
        'services' => all_services(),
    ]);
}

function handle_service_create(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/services');
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));

    if ($name === '' || $price < 0) {
        flash('İşlem adı ve fiyat bilgisi geçerli olmalıdır.', 'error');
        redirect('/services');
    }

    $stmt = db()->prepare('INSERT INTO services (name, description, price) VALUES (:name, :description, :price)');
    $stmt->execute(['name' => $name, 'description' => $description, 'price' => $price]);
    flash('İşlem eklendi.');
    redirect('/services');
}

function handle_service_update(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/services');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    if ($id <= 0 || $name === '' || $price < 0) {
        flash('İşlem bilgileri geçerli değil.', 'error');
        redirect('/services');
    }

    $stmt = db()->prepare('UPDATE services SET name = :name, description = :description, price = :price, is_active = :is_active WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'is_active' => $isActive,
        'id' => $id,
    ]);

    flash('İşlem güncellendi.');
    redirect('/services');
}

function handle_service_delete(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/services');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('UPDATE services SET is_active = 0 WHERE id = :id');
    $stmt->execute(['id' => $id]);
    flash('İşlem pasife alındı.');
    redirect('/services');
}

function handle_revenue_report(): void
{
    Auth::requireRole(['admin']);

    $period = $_GET['period'] ?? 'month';
    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));

    if ($period === 'year') {
        $start = sprintf('%04d-01-01', $year);
        $end = sprintf('%04d-12-31', $year);
    } else {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));
        $period = 'month';
    }

    $stmt = db()->prepare(
        "SELECT a.*, COALESCE(a.customer_name, c.name) AS customer_name, s.name AS specialist_name
         FROM appointments a
         LEFT JOIN users c ON c.id = a.customer_id
         JOIN users s ON s.id = a.specialist_id
         WHERE date(a.slot_start) BETWEEN :start AND :end
           AND a.status = 'completed'
         ORDER BY a.slot_start ASC"
    );
    $stmt->execute(['start' => $start, 'end' => $end]);
    $appointments = $stmt->fetchAll();
    $total = array_sum(array_map(fn (array $appointment) => (float) $appointment['service_price'], $appointments));

    view('reports/revenue.php', [
        'title' => 'Gelir Raporu',
        'appointments' => $appointments,
        'total' => $total,
        'period' => $period,
        'year' => $year,
        'month' => $month,
        'start' => $start,
        'end' => $end,
    ]);
}

function handle_password(): void
{
    Auth::requireRole(['specialist', 'admin', 'customer']);

    view('account/password.php', [
        'title' => 'Şifre Değiştir',
        'user' => Auth::user(),
    ]);
}

function handle_password_update(): void
{
    Auth::requireRole(['specialist', 'admin', 'customer']);

    if (!is_post()) {
        redirect('/account/password');
    }

    $user = Auth::user();
    $current = (string) ($_POST['current_password'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $user['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        flash('Mevcut şifre hatalı.', 'error');
        redirect('/account/password');
    }

    if (strlen($password) < 8 || $password !== $confirmation) {
        flash('Yeni şifre en az 8 karakter olmalı ve tekrarıyla eşleşmeli.', 'error');
        redirect('/account/password');
    }

    $update = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
    $update->execute([
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $user['id'],
    ]);

    flash('Şifreniz güncellendi.');
    redirect('/account/password');
}

function handle_profile(): void
{
    Auth::requireRole(['specialist']);

    $stmt = db()->prepare('SELECT id, name, email, specialty, phone, bio, avatar FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => Auth::user()['id']]);

    view('account/profile.php', [
        'title' => 'Profilim',
        'profile' => $stmt->fetch(),
    ]);
}

function handle_profile_update(): void
{
    Auth::requireRole(['specialist']);

    if (!is_post()) {
        redirect('/profile');
    }

    $user = Auth::user();
    $name = trim($_POST['name'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatar = uploaded_asset_path('avatar', '/profile');

    if ($name === '') {
        flash('Ad soyad alanı boş bırakılamaz.', 'error');
        redirect('/profile');
    }

    if ($avatar !== null) {
        $stmt = db()->prepare(
            'UPDATE users SET name = :name, specialty = :specialty, phone = :phone, bio = :bio, avatar = :avatar WHERE id = :id'
        );
        $stmt->execute([
            'name' => $name,
            'specialty' => $specialty,
            'phone' => $phone,
            'bio' => $bio,
            'avatar' => $avatar,
            'id' => $user['id'],
        ]);
    } else {
        $stmt = db()->prepare(
            'UPDATE users SET name = :name, specialty = :specialty, phone = :phone, bio = :bio WHERE id = :id'
        );
        $stmt->execute([
            'name' => $name,
            'specialty' => $specialty,
            'phone' => $phone,
            'bio' => $bio,
            'id' => $user['id'],
        ]);
    }

    flash('Profil bilgileriniz güncellendi.');
    redirect('/profile');
}

function handle_settings_update(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/settings');
    }

    foreach (['brand_name', 'auth_eyebrow', 'auth_title', 'auth_body'] as $key) {
        update_setting($key, trim($_POST[$key] ?? ''));
    }

    foreach (['brand_logo', 'favicon', 'auth_background'] as $key) {
        $path = uploaded_asset_path($key, '/settings');
        if ($path !== null) {
            update_setting($key, $path);
        }
    }

    flash('Ayarlar güncellendi.');
    redirect('/settings');
}

function update_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (key, value) VALUES (:key, :value)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value'
    );
    $stmt->execute(['key' => $key, 'value' => $value]);
}

function uploaded_asset_path(string $field, string $failureRedirect = '/settings'): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        flash('Dosya yüklenemedi.', 'error');
        redirect($failureRedirect);
    }

    if (($_FILES[$field]['size'] ?? 0) > 4 * 1024 * 1024) {
        flash('Görsel boyutu 4 MB altında olmalı.', 'error');
        redirect($failureRedirect);
    }

    $tmp = $_FILES[$field]['tmp_name'];
    $mime = mime_content_type($tmp) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
    ];

    if (!isset($allowed[$mime])) {
        flash('Lütfen JPG, PNG, WebP, SVG veya ICO görsel yükleyin.', 'error');
        redirect($failureRedirect);
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $filename = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $directory . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        flash('Dosya kaydedilemedi.', 'error');
        redirect($failureRedirect);
    }

    return '/uploads/' . $filename;
}

function handle_schedule_update(): void
{
    Auth::requireRole(['admin']);

    if (!is_post()) {
        redirect('/schedule');
    }

    $specialistId = (int) ($_POST['specialist_id'] ?? 0);
    if ($specialistId <= 0) {
        redirect('/schedule');
    }

    $delete = db()->prepare('DELETE FROM working_hours WHERE specialist_id = :specialist_id');
    $delete->execute(['specialist_id' => $specialistId]);

    $insert = db()->prepare(
        'INSERT INTO working_hours (specialist_id, weekday, start_time, end_time)
         VALUES (:specialist_id, :weekday, :start_time, :end_time)'
    );

    foreach (weekdays() as $weekday => $label) {
        if (empty($_POST['enabled'][$weekday])) {
            continue;
        }

        $start = $_POST['start_time'][$weekday] ?? '09:00';
        $end = $_POST['end_time'][$weekday] ?? '18:00';

        if ($start < $end) {
            $insert->execute([
                'specialist_id' => $specialistId,
                'weekday' => $weekday,
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }
    }

    flash('Mesai saatleri güncellendi.');
    redirect('/schedule?specialist_id=' . $specialistId);
}

function handle_block_create(): void
{
    Auth::requireRole(['admin', 'specialist']);

    if (!is_post()) {
        redirect('/dashboard');
    }

    $user = Auth::user();
    $specialistId = $user['role'] === 'admin' ? (int) ($_POST['specialist_id'] ?? 0) : (int) $user['id'];
    $slotStart = $_POST['slot_start'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $slotEnd = date('Y-m-d H:i:s', strtotime($slotStart . ' +1 hour'));

    if ($specialistId <= 0 || strtotime($slotStart) === false) {
        flash('Blok saat bilgisi geçersiz.', 'error');
        redirect('/dashboard');
    }

    $stmt = db()->prepare(
        'INSERT INTO blocked_slots (specialist_id, slot_start, slot_end, reason)
         VALUES (:specialist_id, :slot_start, :slot_end, :reason)'
    );
    $stmt->execute([
        'specialist_id' => $specialistId,
        'slot_start' => date('Y-m-d H:i:s', strtotime($slotStart)),
        'slot_end' => $slotEnd,
        'reason' => $reason,
    ]);

    flash('Saat bloğu oluşturuldu.');
    redirect('/dashboard');
}

function specialists(): array
{
    return db()->query("SELECT id, name, specialty, phone, bio FROM users WHERE role = 'specialist' ORDER BY name")->fetchAll();
}

function active_services(): array
{
    return db()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY name')->fetchAll();
}

function all_services(): array
{
    return db()->query('SELECT * FROM services ORDER BY is_active DESC, name')->fetchAll();
}

function service_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM services WHERE id = :id AND is_active = 1 LIMIT 1');
    $stmt->execute(['id' => $id]);
    $service = $stmt->fetch();

    return $service ?: null;
}

function default_customer_id(): int
{
    $stmt = db()->prepare("SELECT id FROM users WHERE email = 'musteri@salon.test' LIMIT 1");
    $stmt->execute();
    $id = $stmt->fetchColumn();

    if ($id) {
        return (int) $id;
    }

    $insert = db()->prepare("INSERT INTO users (name, email, role, password_hash) VALUES ('Defter Müşterisi', 'musteri@salon.test', 'customer', :hash)");
    $insert->execute(['hash' => password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT)]);

    return (int) db()->lastInsertId();
}

function available_slots(int $specialistId, string $date, int $duration = 1): array
{
    $duration = max(1, min(3, $duration));

    if (strtotime($date) === false) {
        return [];
    }

    $weekday = (int) date('N', strtotime($date));
    $stmt = db()->prepare('SELECT * FROM working_hours WHERE specialist_id = :specialist_id AND weekday = :weekday LIMIT 1');
    $stmt->execute(['specialist_id' => $specialistId, 'weekday' => $weekday]);
    $hours = $stmt->fetch();

    if (!$hours) {
        return [];
    }

    $busyStmt = db()->prepare(
        "SELECT slot_start, slot_end FROM appointments
         WHERE specialist_id = :specialist_id AND date(slot_start) = :date AND status IN ('booked', 'completed')
         UNION ALL
         SELECT slot_start, slot_end FROM blocked_slots
         WHERE specialist_id = :specialist_id AND date(slot_start) = :date"
    );
    $busyStmt->execute(['specialist_id' => $specialistId, 'date' => $date]);
    $busyRanges = $busyStmt->fetchAll();

    $start = strtotime($date . ' ' . $hours['start_time']);
    $end = strtotime($date . ' ' . $hours['end_time']);
    $slots = [];

    for ($time = $start; $time + ($duration * 3600) <= $end; $time += 3600) {
        $slotStart = date('Y-m-d H:i:s', $time);
        if ($time <= time()) {
            continue;
        }

        $slotEnd = $time + ($duration * 3600);
        $isAvailable = true;

        foreach ($busyRanges as $range) {
            $busyStart = strtotime($range['slot_start']);
            $busyEnd = strtotime($range['slot_end']);

            if ($busyStart === false || $busyEnd === false) {
                continue;
            }

            if ($time < $busyEnd && $slotEnd > $busyStart) {
                $isAvailable = false;
                break;
            }
        }

        if (!$isAvailable) {
            continue;
        }

        $slots[] = [
            'start' => $slotStart,
            'label' => date('H:i', $time) . ' - ' . date('H:i', $time + ($duration * 3600)),
            'is_soon' => $time <= time() + 3600,
        ];
    }

    return $slots;
}

/**
 * Bir uzmanın belirli bir gündeki saatlik boş/dolu şemasını döndürür.
 * Her satır: type = busy | blocked | past | free.
 */
function day_slots(int $specialistId, string $date): array
{
    if (strtotime($date) === false) {
        return ['open' => false, 'rows' => [], 'appointments' => []];
    }

    $weekday = (int) date('N', strtotime($date));
    $hoursStmt = db()->prepare('SELECT * FROM working_hours WHERE specialist_id = :s AND weekday = :w LIMIT 1');
    $hoursStmt->execute(['s' => $specialistId, 'w' => $weekday]);
    $hours = $hoursStmt->fetch();

    $apptStmt = db()->prepare(
        "SELECT a.*, COALESCE(a.customer_name, c.name) AS customer_name
         FROM appointments a
         LEFT JOIN users c ON c.id = a.customer_id
         WHERE a.specialist_id = :s AND date(a.slot_start) = :d
         ORDER BY a.slot_start ASC"
    );
    $apptStmt->execute(['s' => $specialistId, 'd' => $date]);
    $dayAppointments = $apptStmt->fetchAll();

    if (!$hours) {
        return ['open' => false, 'rows' => [], 'appointments' => $dayAppointments];
    }

    $blockStmt = db()->prepare('SELECT * FROM blocked_slots WHERE specialist_id = :s AND date(slot_start) = :d');
    $blockStmt->execute(['s' => $specialistId, 'd' => $date]);
    $blocks = $blockStmt->fetchAll();

    $start = strtotime($date . ' ' . $hours['start_time']);
    $end = strtotime($date . ' ' . $hours['end_time']);
    $now = time();
    $rows = [];

    for ($t = $start; $t + 3600 <= $end; $t += 3600) {
        $slotEnd = $t + 3600;

        // Bu saati kaplayan randevu (yalnızca planlanan/onaylanan meşgul eder).
        $apptHere = null;
        $covered = false;
        foreach ($dayAppointments as $appointment) {
            if (!in_array($appointment['status'], ['booked', 'completed'], true)) {
                continue;
            }
            $apptStart = strtotime($appointment['slot_start']);
            $apptEnd = strtotime($appointment['slot_end']);
            if ($apptStart <= $t && $t < $apptEnd) {
                if ($apptStart === $t) {
                    $apptHere = $appointment;
                } else {
                    $covered = true;
                }
                break;
            }
        }
        if ($covered) {
            continue; // Daha erken başlayan randevu tarafından kaplanıyor.
        }
        if ($apptHere) {
            $rows[] = [
                'type' => 'busy',
                'start' => $t,
                'label' => date('H:i', $t) . ' - ' . date('H:i', strtotime($apptHere['slot_end'])),
                'appointment' => $apptHere,
            ];
            continue;
        }

        $blockReason = null;
        foreach ($blocks as $block) {
            if (strtotime($block['slot_start']) < $slotEnd && strtotime($block['slot_end']) > $t) {
                $blockReason = (string) ($block['reason'] ?? '');
                break;
            }
        }
        if ($blockReason !== null) {
            $rows[] = ['type' => 'blocked', 'start' => $t, 'label' => date('H:i', $t) . ' - ' . date('H:i', $slotEnd), 'reason' => $blockReason];
            continue;
        }

        if ($t <= $now) {
            $rows[] = ['type' => 'past', 'start' => $t, 'label' => date('H:i', $t) . ' - ' . date('H:i', $slotEnd)];
            continue;
        }

        $rows[] = ['type' => 'free', 'start' => $t, 'label' => date('H:i', $t) . ' - ' . date('H:i', $slotEnd)];
    }

    return ['open' => true, 'hours' => $hours, 'rows' => $rows, 'appointments' => $dayAppointments];
}

function not_found(): void
{
    http_response_code(404);
    view('errors/404.php', ['title' => 'Sayfa bulunamadı']);
}
