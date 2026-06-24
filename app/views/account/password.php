<header class="page-header">
    <div>
        <span class="eyebrow">Hesap</span>
        <h1>Şifre değiştir</h1>
    </div>
</header>

<section class="grid balanced">
    <div class="panel">
        <h2>Yeni şifre belirle</h2>
        <form class="stack-form" method="post" action="/account/password/update">
            <label>
                Mevcut şifre
                <input type="password" name="current_password" required>
            </label>
            <label>
                Yeni şifre
                <input type="password" name="password" minlength="8" required>
            </label>
            <label>
                Yeni şifre tekrar
                <input type="password" name="password_confirmation" minlength="8" required>
            </label>
            <button type="submit">Şifreyi güncelle</button>
        </form>
    </div>

    <div class="panel tone">
        <h2>Hesap güvenliği</h2>
        <p>Geçici şifreyle giriş yaptıktan sonra kişisel ve güçlü bir şifre belirleyin. Şifre en az 8 karakter olmalı.</p>
    </div>
</section>
