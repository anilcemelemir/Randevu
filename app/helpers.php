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
