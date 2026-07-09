<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(DATA_PATH)) {
        mkdir(DATA_PATH, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DATA_PATH . DIRECTORY_SEPARATOR . 'database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function ensure_database(): void
{
    $pdo = db();
    $exists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();

    if ($exists) {
        migrate_database($pdo);
        return;
    }

    $schema = file_get_contents(ROOT_PATH . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql');
    $pdo->exec($schema);

    seed_database($pdo);
    migrate_database($pdo);
}

function migrate_database(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT)');
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL DEFAULT 0,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS appointment_services (
            appointment_id INTEGER NOT NULL,
            service_id INTEGER NOT NULL,
            service_name TEXT NOT NULL,
            service_price REAL NOT NULL DEFAULT 0,
            PRIMARY KEY (appointment_id, service_id),
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
        )'
    );

    $columns = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(), 'name');
    $additions = [
        'specialty' => 'TEXT',
        'phone' => 'TEXT',
        'bio' => 'TEXT',
        'avatar' => 'TEXT',
    ];

    foreach ($additions as $column => $type) {
        if (!in_array($column, $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column $type");
        }
    }

    $users = $pdo->query("SELECT id, email FROM users WHERE email LIKE '%@xn--ikikiznails-%.com'")->fetchAll();
    foreach ($users as $user) {
        $normalizedEmail = normalize_email($user['email']);
        if ($normalizedEmail !== $user['email'] && is_valid_plain_email($normalizedEmail)) {
            try {
                $updateEmail = $pdo->prepare('UPDATE users SET email = :email WHERE id = :id');
                $updateEmail->execute([
                    'email' => $normalizedEmail,
                    'id' => $user['id'],
                ]);
            } catch (PDOException) {
                // Keep the existing address if another account already uses the normalized email.
            }
        }
    }

    $appointmentColumns = array_column($pdo->query('PRAGMA table_info(appointments)')->fetchAll(), 'name');
    $appointmentAdditions = [
        'service_id' => 'INTEGER',
        'customer_name' => 'TEXT',
        'customer_phone' => 'TEXT',
        'service_name' => 'TEXT',
        'service_price' => 'REAL NOT NULL DEFAULT 0',
    ];

    foreach ($appointmentAdditions as $column => $type) {
        if (!in_array($column, $appointmentColumns, true)) {
            $pdo->exec("ALTER TABLE appointments ADD COLUMN $column $type");
        }
    }

    $pdo->exec("UPDATE users SET name = 'Ayşe Demir', specialty = COALESCE(specialty, 'Cilt bakımı'), phone = COALESCE(phone, '+90 555 010 1001'), bio = COALESCE(bio, 'Cilt analizi, medikal bakım ve yenileyici uygulamalarda uzman.') WHERE email = 'ayse@salon.test'");
    $pdo->exec("UPDATE users SET specialty = COALESCE(specialty, 'Kaş ve kirpik'), phone = COALESCE(phone, '+90 555 010 1002'), bio = COALESCE(bio, 'Kaş tasarımı, kirpik lifting ve doğal görünüm odaklı uygulamalar yapar.') WHERE email = 'zeynep@salon.test'");
    $pdo->exec("UPDATE users SET name = 'Müşteri Demo' WHERE email = 'musteri@salon.test'");

    foreach (default_settings() as $key => $value) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    seed_services($pdo);
}

function seed_database(PDO $pdo): void
{
    $password = password_hash('password', PASSWORD_DEFAULT);
    $users = [
        ['Admin', 'admin@salon.test', 'admin', $password],
        ['Ayşe Demir', 'ayse@salon.test', 'specialist', $password],
        ['Zeynep Kaya', 'zeynep@salon.test', 'specialist', $password],
        ['Müşteri Demo', 'musteri@salon.test', 'customer', $password],
    ];

    $stmt = $pdo->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (:name, :email, :role, :password_hash)');
    foreach ($users as [$name, $email, $role, $hash]) {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'password_hash' => $hash,
        ]);
    }

    $pdo->exec("UPDATE users SET specialty = 'Cilt bakımı', phone = '+90 555 010 1001', bio = 'Cilt analizi, medikal bakım ve yenileyici uygulamalarda uzman.' WHERE email = 'ayse@salon.test'");
    $pdo->exec("UPDATE users SET specialty = 'Kaş ve kirpik', phone = '+90 555 010 1002', bio = 'Kaş tasarımı, kirpik lifting ve doğal görünüm odaklı uygulamalar yapar.' WHERE email = 'zeynep@salon.test'");

    $specialists = $pdo->query("SELECT id FROM users WHERE role = 'specialist'")->fetchAll();
    $schedule = $pdo->prepare('INSERT INTO working_hours (specialist_id, weekday, start_time, end_time) VALUES (:specialist_id, :weekday, :start_time, :end_time)');

    foreach ($specialists as $specialist) {
        for ($day = 1; $day <= 5; $day++) {
            $schedule->execute([
                'specialist_id' => $specialist['id'],
                'weekday' => $day,
                'start_time' => '09:00',
                'end_time' => '18:00',
            ]);
        }
    }
}

function seed_services(PDO $pdo): void
{
    $stmt = $pdo->prepare("SELECT id FROM services WHERE name = 'Nail Art' LIMIT 1");
    $stmt->execute();
    $nailArtId = $stmt->fetchColumn();

    if ($nailArtId) {
        $update = $pdo->prepare(
            "UPDATE services
             SET name = 'Nail Art',
                 description = COALESCE(NULLIF(description, ''), 'Nail Art uygulamasi'),
                 is_active = 1
             WHERE id = :id"
        );
        $update->execute(['id' => $nailArtId]);
    } else {
        $insert = $pdo->prepare('INSERT INTO services (name, description, price) VALUES (:name, :description, :price)');
        $insert->execute([
            'name' => 'Nail Art',
            'description' => 'Nail Art uygulamasi',
            'price' => 0,
        ]);
        $nailArtId = (int) $pdo->lastInsertId();
    }

    $deactivate = $pdo->prepare('UPDATE services SET is_active = 0 WHERE id <> :id');
    $deactivate->execute(['id' => $nailArtId]);
}

function default_settings(): array
{
    return [
        'brand_name' => 'Randevu',
        'brand_subtitle' => 'Salon paneli',
        'brand_logo' => '',
        'favicon' => '',
        'auth_background' => '/assets/images/auth-hero.jpg',
        'auth_eyebrow' => 'Güzellik salonu randevu sistemi',
        'auth_title' => 'Bakım deneyimini daha sakin ve düzenli planlayın.',
        'auth_body' => 'Uzman mesaileri, blok saatler ve müşteri randevuları modern salon akışına uygun tek panelde birleşir.',
    ];
}

function cleanup_cancelled_appointments(): void
{
    $stmt = db()->prepare("DELETE FROM appointments WHERE status = 'cancelled' AND date(slot_start) < :today");
    $stmt->execute(['today' => date('Y-m-d')]);
}
