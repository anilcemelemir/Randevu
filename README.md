# Randevu

Güzellik salonları için saf PHP + SQLite tabanlı randevu yönetim uygulaması.

## Gereksinimler

- PHP 8.2+
- SQLite PDO eklentisi

Composer gerekmez.

## Calistirma

```powershell
php -S localhost:8000 -t public public/router.php
```

Tarayicida `http://localhost:8000` adresini acin.

## Docker ile Calistirma

Windows PowerShell uzerinden bos portu otomatik bulup Docker'i baslatmak icin:

```powershell
.\scripts\start-docker.ps1
```

Varsayilan olarak `8080-8099` araliginda bos port arar ve `.env` dosyasina `APP_PORT` olarak yazar.

Elle port secmek isterseniz:

```powershell
$env:APP_PORT=8085
docker compose up --build
```

## Cenuta CI/CD

GitHub Actions workflow dosyasi `.github/workflows/deploy.yml` icindedir. `main` branch'e push yapildiginda PHP dosyalarini lint eder, deploy paketi hazirlar ve FTP ile hostinge yukler.

GitHub repo ayarlarında `Settings > Secrets and variables > Actions` bölümüne şu secret'ları ekleyin:

| Secret | Örnek |
| --- | --- |
| `CENUTA_FTP_SERVER` | `ftp.siteadresiniz.com` |
| `CENUTA_FTP_USERNAME` | Cenuta/cPanel FTP kullanici adi |
| `CENUTA_FTP_PASSWORD` | FTP sifresi |
| `CENUTA_FTP_SERVER_DIR` | `/public_html/randevu/` veya domain document root klasoru |

En temiz kurulum icin Cenuta/cPanel tarafinda domain veya subdomain document root'unu projenin `public` klasorune yonlendirin. Document root degistiremiyorsaniz `server-dir` degerini uygulamanin calisacagi klasore verin ve hosting panelinde bu klasoru domain kok dizini yapin.

## Varsayilan Hesaplar

Ilk calistirmada veritabani otomatik olusur ve asagidaki hesaplar eklenir:

| Rol | E-posta | Şifre |
| --- | --- | --- |
| Admin | admin@salon.test | password |
| Güzellik uzmanı | ayse@salon.test | password |
| Güzellik uzmanı | zeynep@salon.test | password |
| Müşteri | musteri@salon.test | password |

## Roller

- Admin: tum kullanicilari, uzmanlari, mesaileri, blok saatleri ve randevulari gorur; uzman calisma saatlerini ayarlar.
- Güzellik uzmanı: kendi ve diğer uzmanların randevu/mesai akışlarını görür; kendi takviminde blok saat oluşturur.
- Müşteri: uygun uzman ve saat seçerek randevu alır; kendi randevularını görür.
