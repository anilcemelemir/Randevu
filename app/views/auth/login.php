<?php $settings = app_settings(); ?>
<section class="auth-panel">
    <div class="auth-copy">
        <span class="eyebrow"><?= e($settings['auth_eyebrow']) ?></span>
        <h1><?= e($settings['auth_title']) ?></h1>
        <p><?= e($settings['auth_body']) ?></p>
    </div>

    <form class="auth-card" method="post" action="/">
        <div>
            <h2>Giriş yap</h2>
            <p>Salon ekibi hesabıyla devam edin.</p>
        </div>

        <?php if ($error): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <label>
            E-posta
            <input type="email" name="email" placeholder="ornek@salon.com" required autofocus>
        </label>

        <label>
            Şifre
            <input type="password" name="password" placeholder="••••••••" required>
        </label>

        <button type="submit">Giriş yap</button>
    </form>
</section>

