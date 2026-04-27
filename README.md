# Kullanıcı Yönetim Sistemi

PHP ve MySQL kullanılarak geliştirilmiş, temel kullanıcı kayıt, giriş, çıkış ve giriş geçmişi takibi özelliklerine sahip bir kullanıcı yönetim sistemi projesidir.

Bu proje; kullanıcıların güvenli şekilde hesap oluşturmasını, oturum açmasını, oturum kapatmasını ve panel üzerinden profil/giriş geçmişi bilgilerini görüntülemesini sağlar.

## İçindekiler

- [Özellikler](#özellikler)
- [Kullanılan Teknolojiler](#kullanılan-teknolojiler)
- [Proje Yapısı](#proje-yapısı)
- [Kurulum](#kurulum)
- [Veritabanı Kurulumu](#veritabanı-kurulumu)
- [Yapılandırma](#yapılandırma)
- [Kullanım](#kullanım)
- [Güvenlik Özellikleri](#güvenlik-özellikleri)
- [Son Düzeltmeler](#son-düzeltmeler)
- [Geliştirme Notları](#geliştirme-notları)

## Özellikler

- Kullanıcı kayıt sistemi
- Kullanıcı giriş ve çıkış işlemleri
- Oturum bazlı kullanıcı doğrulama
- Kullanıcı paneli
- Profil bilgilerini görüntüleme
- Başarılı ve başarısız giriş kayıtlarını takip etme
- IP bazlı başarısız giriş denemesi sınırlandırma
- CSRF token ile form güvenliği
- Şifrelerin hashlenerek veritabanında saklanması
- Giriş/çıkış işlem geçmişi
- Responsive arayüz tasarımı

## Kullanılan Teknolojiler

| Teknoloji | Açıklama |
| --- | --- |
| PHP | Sunucu tarafı uygulama geliştirme |
| MySQL | Veritabanı yönetimi |
| PDO | Güvenli veritabanı bağlantısı ve sorgu işlemleri |
| HTML5 | Sayfa iskeleti |
| CSS3 | Arayüz tasarımı |
| JavaScript | İstemci tarafı form kontrolleri |

## Proje Yapısı

```text
Kullanici-Yonetim-Sistemi-Gelistirme-main/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── img/
│   │   ├── dağ.jpg
│   │   └── wallpaper.avif
│   └── js/
│       └── main.js
├── config.php
├── database.php
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── ogrenci_no_system_db.sql
├── register.php
└── README.md
```

## Kurulum

1. Proje dosyalarını web sunucunuzun çalışma dizinine taşıyın.

   XAMPP kullanıyorsanız örnek konum:

   ```text
   C:\xampp\htdocs\Kullanici-Yonetim-Sistemi-Gelistirme-main
   ```

2. Apache ve MySQL servislerini başlatın.

3. Tarayıcıdan phpMyAdmin arayüzünü açın:

   ```text
   http://localhost/phpmyadmin
   ```

4. Projede bulunan `ogrenci_no_system_db.sql` dosyasını içe aktarın.

5. `config.php` dosyasındaki veritabanı bilgilerini kendi ortamınıza göre düzenleyin.

6. Projeyi tarayıcıdan açın:

   ```text
   http://localhost/Kullanici-Yonetim-Sistemi-Gelistirme-main
   ```

## Veritabanı Kurulumu

Veritabanı kurulumu için `ogrenci_no_system_db.sql` dosyası kullanılmalıdır.

SQL dosyası aşağıdaki tabloları oluşturur:

| Tablo | Açıklama |
| --- | --- |
| `users` | Kullanıcı hesap bilgilerini tutar |
| `users_logs` | Giriş, başarısız giriş, engellenen giriş ve çıkış kayıtlarını tutar |

SQL dosyası varsayılan olarak şu veritabanı adını kullanır:

```text
ogrenci_no_system_db
```

Veritabanı adını değiştirmek isterseniz hem `ogrenci_no_system_db.sql` dosyasında hem de `config.php` dosyasındaki `DB_NAME` değerinde aynı değişikliği yapmanız gerekir.

## Yapılandırma

Temel ayarlar `config.php` dosyasında yer alır.

```php
define('DB_HOST',    'localhost');
define('DB_NAME',    'ogrenci_no_system_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
```

Giriş güvenliği için kullanılan sınırlar:

```php
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION',   900);
```

Bu ayarlara göre aynı IP adresinden 15 dakika içinde 3 başarısız giriş denemesi yapılırsa giriş denemeleri geçici olarak engellenir.

## Kullanım

### Kayıt Olma

Kullanıcılar `register.php` sayfası üzerinden ad, soyad, e-posta ve şifre bilgileriyle yeni hesap oluşturabilir.

Şifre için temel kurallar:

- En az 8 karakter olmalıdır.
- En az 1 büyük harf içermelidir.
- En az 1 rakam içermelidir.
- Şifre ve şifre tekrar alanları aynı olmalıdır.

### Giriş Yapma

Kullanıcılar `login.php` sayfası üzerinden e-posta ve şifre bilgileriyle sisteme giriş yapabilir.

Başarılı girişten sonra kullanıcı `dashboard.php` sayfasına yönlendirilir.

### Kullanıcı Paneli

Panelde kullanıcıya ait şu bilgiler görüntülenir:

- Ad ve soyad
- E-posta adresi
- Üyelik tarihi
- Başarılı giriş sayısı
- Başarısız giriş denemesi sayısı
- Son başarısız giriş denemeleri
- Giriş/çıkış geçmişi

### Çıkış Yapma

Kullanıcı `logout.php` üzerinden oturumunu güvenli şekilde sonlandırabilir. Çıkış işlemi ayrıca `users_logs` tablosuna kaydedilir.

## Güvenlik Özellikleri

Projede aşağıdaki güvenlik önlemleri uygulanmıştır:

- SQL injection riskine karşı PDO prepared statement kullanımı
- Şifrelerin `password_hash()` ile hashlenerek saklanması
- Girişte `password_verify()` ile şifre doğrulama
- Form işlemlerinde CSRF token kontrolü
- Başarısız giriş denemelerinde IP bazlı geçici kilitleme
- Oturum açıldıktan sonra `session_regenerate_id(true)` kullanımı
- Oturum çerezlerinde `httponly` ve `samesite` ayarları
- Kullanıcı çıktılarında `htmlspecialchars()` kullanımı
- Giriş ve çıkış işlemlerinin loglanması

## Son Düzeltmeler

Bu sürümde aşağıdaki hata ve güvenlik iyileştirmeleri yapılmıştır:

- Dashboard üzerindeki başarılı ve başarısız giriş sayıları artık sınırlı liste verilerinden değil, doğrudan veritabanındaki gerçek toplamlar üzerinden hesaplanır.
- Login ve logout işlemlerinde kullanıcı tarafından sahte gönderilebilen `HTTP_X_FORWARDED_FOR` başlığı yerine `REMOTE_ADDR` kullanılır.
- `mbstring` eklentisi olmayan PHP ortamlarında kayıt sayfasının hata vermemesi için güvenli metin uzunluğu hesaplama fonksiyonu eklenmiştir.

## Geliştirme Notları

- Üretim ortamında `DB_USER` ve `DB_PASS` bilgileri varsayılan değerlerle bırakılmamalıdır.
- Canlı ortamda HTTPS kullanılmalıdır.
- `BASE_URL` değeri uygulamanın gerçek yayın adresine göre güncellenmelidir.
- Veritabanı adı proje teslim gereksinimine göre güncellenmelidir.
- PHP `PDO` ve `pdo_mysql` eklentilerinin aktif olduğundan emin olunmalıdır.

## Lisans

Bu proje eğitim ve geliştirme amacıyla hazırlanmıştır.
