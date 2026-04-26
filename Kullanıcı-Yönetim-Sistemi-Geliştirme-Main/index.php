<?php
require_once __DIR__ . '/config.php';
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card text-center mt-60" style="padding:4rem 3rem; max-width:520px;">
            <span style="font-size:2rem; display:block; margin-bottom:.75rem;">⬡</span>
            <h1 class="app-title"><?= htmlspecialchars(APP_NAME) ?></h1>
            <hr class="gold-divider">
            <p class="subtitle">PHP &amp; MySQL ile geliştirilmiş<br>güvenli kullanıcı yönetim sistemi</p>
            <div class="btn-group mt-30">
                <a href="login.php"    class="btn btn-primary">Giriş Yap</a>
                <a href="register.php" class="btn btn-secondary">Kayıt Ol</a>
            </div>
        </div>
    </div>
    <footer class="page-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> &nbsp;·&nbsp; BGY206 Web Programlama&#8209;I
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>
