<?php $user = Auth::user(); ?>
<?php $settings = app_settings(); ?>
<?php $isCustomerShell = $user && $user['role'] === 'customer' && empty($authPage); ?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? setting('brand_name', 'Randevu')) ?> | <?= e(setting('brand_name', 'Randevu')) ?></title>
    <link rel="icon" href="<?= e(setting('favicon') ?: '/assets/images/favicon.svg') ?>">
    <link rel="stylesheet" href="/assets/app.css">
    <style>
        :root {
            --auth-background-image: url("<?= e(setting('auth_background') ?: '/assets/images/auth-hero.jpg') ?>");
        }
    </style>
</head>
<body class="<?= !empty($authPage) ? 'auth-shell' : ($isCustomerShell ? 'customer-shell' : 'app-shell') ?>">
    <?php if (!empty($authPage)): ?>
        <main class="auth-main">
            <div class="auth-ornaments" aria-hidden="true">
                <span class="ornament ornament-ring"></span>
                <span class="ornament ornament-leaf"></span>
                <span class="ornament ornament-drop"></span>
                <span class="ornament ornament-line"></span>
            </div>
            <?php require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template; ?>
        </main>
    <?php elseif ($isCustomerShell): ?>
        <header class="customer-topbar">
            <a class="brand" href="/dashboard">
                <?php if (!empty($settings['brand_logo'])): ?>
                    <img class="brand-logo" src="<?= e($settings['brand_logo']) ?>" alt="<?= e($settings['brand_name']) ?>">
                <?php else: ?>
                    <span class="brand-mark"><?= e(initial($settings['brand_name'] ?: 'Randevu')) ?></span>
                    <span>
                        <strong><?= e($settings['brand_name']) ?></strong>
                    </span>
                <?php endif; ?>
            </a>

            <button class="mobile-menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-label="Menüyü aç">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="customer-nav">
                <a class="<?= route_is('/dashboard') ? 'active' : '' ?>" href="/dashboard">Randevularım</a>
                <a class="<?= route_is('/appointments/new') ? 'active' : '' ?>" href="/appointments/new">Yeni randevu</a>
            </nav>

            <div class="customer-actions">
                <span><?= e($user['name']) ?></span>
                <a href="/account/password">Şifre</a>
                <a href="/logout">Çıkış</a>
            </div>
        </header>

        <main class="customer-content">
            <?php $flash = flash(); ?>
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <?php require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template; ?>
        </main>
    <?php else: ?>
        <aside class="sidebar">
            <a class="brand" href="/dashboard">
                <?php if (!empty($settings['brand_logo'])): ?>
                    <img class="brand-logo" src="<?= e($settings['brand_logo']) ?>" alt="<?= e($settings['brand_name']) ?>">
                <?php else: ?>
                    <span class="brand-mark"><?= e(initial($settings['brand_name'] ?: 'Randevu')) ?></span>
                    <span>
                        <strong><?= e($settings['brand_name']) ?></strong>
                    </span>
                <?php endif; ?>
            </a>

            <button class="mobile-menu-toggle sidebar-toggle" type="button" data-menu-toggle aria-expanded="false" aria-label="Menüyü aç">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav">
                <a class="<?= route_is('/dashboard') ? 'active' : '' ?>" href="/dashboard">Takvim</a>
                <?php if ($user && in_array($user['role'], ['admin', 'specialist'], true)): ?>
                    <a class="<?= route_is('/appointments/history') ? 'active' : '' ?>" href="/appointments/history">Randevu ge&ccedil;mişi</a>
                <?php endif; ?>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <a class="<?= route_is('/specialists') ? 'active' : '' ?>" href="/specialists">Uzmanlar</a>
                <?php endif; ?>
                <?php if ($user && $user['role'] === 'specialist'): ?>
                    <a class="<?= route_is('/profile') ? 'active' : '' ?>" href="/profile">Profilim</a>
                <?php endif; ?>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <a class="<?= route_is('/schedule') ? 'active' : '' ?>" href="/schedule">Mesai</a>
                    <a class="<?= route_is('/services') ? 'active' : '' ?>" href="/services">Fiyat listesi</a>
                    <a class="<?= route_is('/reports/revenue') ? 'active' : '' ?>" href="/reports/revenue">Gelir</a>
                    <a class="<?= route_is('/settings') ? 'active' : '' ?>" href="/settings">Ayarlar</a>
                <?php endif; ?>
            </nav>

            <div class="profile">
                <span><?= e($user['name'] ?? '') ?></span>
                <small><?= e(role_label($user['role'] ?? '')) ?></small>
                <a href="/account/password">Şifre değiştir</a>
                <a href="/logout">Çıkış yap</a>
            </div>
        </aside>

        <main class="content">
            <?php $flash = flash(); ?>
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <?php require APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template; ?>
        </main>
    <?php endif; ?>
    <script src="/assets/calendar.js" defer></script>
    <script>
        document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                document.body.classList.toggle('nav-open');
                button.setAttribute('aria-expanded', document.body.classList.contains('nav-open') ? 'true' : 'false');
            });
        });
    </script>
</body>
</html>
