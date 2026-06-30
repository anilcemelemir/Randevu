<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function normalize_email(string $email): string
{
    $email = trim($email);
    $email = str_replace(["\u{200B}", "\u{200C}", "\u{200D}", "\u{FEFF}"], '', $email);
    $email = strtr($email, [
        'İ' => 'i',
        'I' => 'i',
        'ı' => 'i',
        'Ğ' => 'g',
        'ğ' => 'g',
        'Ü' => 'u',
        'ü' => 'u',
        'Ş' => 's',
        'ş' => 's',
        'Ö' => 'o',
        'ö' => 'o',
        'Ç' => 'c',
        'ç' => 'c',
    ]);
    $email = strtolower($email);

    if (!str_contains($email, '@')) {
        return $email;
    }

    [$local, $domain] = explode('@', $email, 2);

    if (preg_match('/^xn--ikikiznails-[a-z0-9-]+\.com$/', $domain)) {
        $domain = 'ikikiznails.com';
    }

    return $local . '@' . $domain;
}

function is_valid_plain_email(string $email): bool
{
    return $email !== ''
        && preg_match('/^[\x21-\x7E]+$/', $email)
        && !str_contains($email, 'xn--')
        && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layout.php';
}

function app_settings(): array
{
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = function_exists('default_settings') ? default_settings() : [];
    $rows = db()->query('SELECT key, value FROM settings')->fetchAll();

    foreach ($rows as $row) {
        $settings[$row['key']] = (string) $row['value'];
    }

    return $settings;
}

function setting(string $key, ?string $fallback = null): string
{
    $settings = app_settings();
    return $settings[$key] ?? (string) $fallback;
}

function route_is(string $path): bool
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === $path;
}

function current_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

function weekdays(): array
{
    return [
        1 => 'Pazartesi',
        2 => 'Salı',
        3 => 'Çarşamba',
        4 => 'Perşembe',
        5 => 'Cuma',
        6 => 'Cumartesi',
        7 => 'Pazar',
    ];
}

function role_label(string $role): string
{
    return [
        'admin' => 'Admin',
        'specialist' => 'Güzellik uzmanı',
        'customer' => 'Müşteri',
    ][$role] ?? $role;
}

function status_label(string $status): string
{
    return [
        'booked' => 'Planlandı',
        'cancelled' => 'İptal edildi',
        'completed' => 'Onaylandı',
    ][$status] ?? $status;
}

function initial(string $name): string
{
    preg_match('/^\X/u', trim($name), $matches);
    return strtoupper($matches[0] ?? '?');
}
