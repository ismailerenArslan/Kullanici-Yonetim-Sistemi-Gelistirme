<?php
/**
 * config.php
 * Uygulama genelinde kullanılan sabitler ve veritabanı bağlantı bilgileri.
 * Tüm hassas bilgiler bu dosyada merkezi olarak tutulmaktadır.
 */

// ─── Veritabanı Bağlantı Sabitleri ────────────────────────────────────────────
define('DB_HOST',    'localhost');          // Veritabanı sunucu adresi
define('DB_NAME',    'ogrenci_no_system_db'); // Veritabanı adı (öğrenci numaranızla değiştirin)
define('DB_USER',    'root');              // Veritabanı kullanıcı adı
define('DB_PASS',    '');                 // Veritabanı şifresi (boşsa '' kullanın; boşluk şifre sayılır)
define('DB_CHARSET', 'utf8mb4');           // Karakter seti

// ─── Uygulama Sabitleri ────────────────────────────────────────────────────────
define('APP_NAME',    'Kullanıcı Yönetim Sistemi'); // Uygulama adı
define('BASE_URL',    'http://localhost/project');   // Uygulamanın temel URL'si

// ─── Güvenlik Sabitleri ────────────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS', 3);    // Bir IP'den izin verilen maksimum hatalı giriş sayısı
define('LOCKOUT_DURATION',   900);  // IP kilitleme süresi (saniye cinsinden, 15 dakika)

// ─── Oturum Yapılandırması ─────────────────────────────────────────────────────
// Oturum çerezini yalnızca HTTP üzerinden erişilebilir yaparak XSS riskini azalt
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode',  1);

$httpsAktif = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $httpsAktif,
    'httponly' => true,
    'samesite' => 'Strict',
]);