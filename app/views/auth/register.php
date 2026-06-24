<?php $settings = app_settings(); ?>
<section class="auth-panel">
    <div class="auth-copy">
        <span class="eyebrow"><?= e($settings['auth_eyebrow']) ?></span>
        <h1><?= e($settings['auth_title']) ?></h1>
        <p><?= e($settings['auth_body']) ?></p>
    </div>

    <form class="auth-card" method="post" action="/register">
        <div>
            <h2>Kayıt ol</h2>
            <p>Müşteri hesabı oluşturun.</p>
        </div>

        <?php if ($error): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <label>
            Ad soyad
            <input type="text" name="name" placeholder="Adiniz Soyadiniz" required autofocus>
        </label>

        <label>
            E-posta
            <input type="email" name="email" placeholder="ornek@mail.com" required>
        </label>

        <label>
            Şifre
            <input type="password" name="password" placeholder="En az 6 karakter" minlength="6" required>
        </label>

        <button type="submit">Kayıt ol</button>

        <p class="auth-switch">Zaten hesabınız var mı? <a href="/">Giriş yapın</a></p>
    </form>
</section>
