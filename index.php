<?php
/**
 * index.php
 * Uygulamanın giriş noktası; karşılama ekranı.
 * Kullanıcıyı login veya register sayfasına yönlendirir.
 */

require_once __DIR__ . '/config.php';

// Oturumu başlat
session_start();

// Kullanıcı zaten giriş yapmışsa doğrudan panele yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> – Ana Sayfa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card text-center mt-60">
            <h1 class="app-title">🔐 <?= htmlspecialchars(APP_NAME) ?></h1>
            <p class="subtitle">PHP &amp; MySQL ile geliştirilmiş güvenli kullanıcı yönetim sistemi.</p>
            <div class="btn-group mt-30">
                <a href="login.php"    class="btn btn-primary">Giriş Yap</a>
                <a href="register.php" class="btn btn-secondary">Kayıt Ol</a>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>