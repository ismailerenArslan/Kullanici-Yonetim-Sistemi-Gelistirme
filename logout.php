<?php
/**
 * logout.php
 * Kullanıcının oturumunu güvenli bir şekilde sonlandırır.
 * - Çıkış işlemini users_logs tablosuna kaydeder.
 * - Tüm session değişkenlerini ve çerezi temizler.
 * - Kullanıcıyı login.php'ye yönlendirir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

session_start();

// Oturumda kullanıcı varsa çıkış logunu kaydet
if (isset($_SESSION['user_id'])) {
    try {
        $pdo    = Database::getInstance()->getConnection();
        $userId = (int) $_SESSION['user_id'];

        // Kullanıcının IP adresini al
        $ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
            : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        // Çıkış işlemini logla
        $stmt = $pdo->prepare(
            'INSERT INTO users_logs (user_id, ip_adresi, islem_tipi, aciklama)
             VALUES (:user_id, :ip_adresi, :islem_tipi, :aciklama)'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':ip_adresi'  => $ip,
            ':islem_tipi' => 'cikis',
            ':aciklama'   => 'Kullanıcı oturumu kapattı.',
        ]);
    } catch (PDOException $e) {
        // Loglama başarısız olsa bile çıkışa devam et
        error_log('Logout Loglama Hatası: ' . $e->getMessage());
    }
}

// ─── Oturumu Tamamen Sonlandır ────────────────────────────────────────────────

// Tüm session değişkenlerini sil
$_SESSION = [];

// Tarayıcıdaki session çerezini sil
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Session'ı sunucu tarafında da yok et
session_destroy();

// Kullanıcıyı giriş sayfasına yönlendir
header('Location: login.php');
exit;