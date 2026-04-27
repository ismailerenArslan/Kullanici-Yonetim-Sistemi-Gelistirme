<?php
/**
 * login.php – Lüks tasarımlı giriş formu.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$hata  = '';
$bilgi = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['kayit']) && $_GET['kayit'] === 'basarili') {
    $bilgi = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
}

function getIpAdresi(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function ipKilitliMi(PDO $pdo, string $ip): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM users_logs
         WHERE ip_adresi = :ip AND islem_tipi = 'giris_basarisiz'
           AND created_at >= DATE_SUB(NOW(), INTERVAL :sure SECOND)"
    );
    $stmt->execute([':ip' => $ip, ':sure' => LOCKOUT_DURATION]);
    return (int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
}

function logYaz(PDO $pdo, ?int $userId, string $ip, string $islemTipi, ?string $aciklama = null): void {
    $stmt = $pdo->prepare(
        'INSERT INTO users_logs (user_id, ip_adresi, islem_tipi, aciklama)
         VALUES (:user_id, :ip_adresi, :islem_tipi, :aciklama)'
    );
    $stmt->execute([
        ':user_id'    => $userId,
        ':ip_adresi'  => $ip,
        ':islem_tipi' => $islemTipi,
        ':aciklama'   => $aciklama,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfGelen = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfGelen)) {
        $hata = 'Geçersiz form isteği. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $email = trim($_POST['email'] ?? '');
        $sifre = $_POST['sifre']      ?? '';
        $ip    = getIpAdresi();

        try {
            $pdo = Database::getInstance()->getConnection();
            if (ipKilitliMi($pdo, $ip)) {
                logYaz($pdo, null, $ip, 'giris_engellendi', 'Çok fazla başarısız deneme.');
                $hata = 'Çok fazla başarısız giriş denemesi. Lütfen ' . (LOCKOUT_DURATION / 60) . ' dakika sonra tekrar deneyin.';
            } else {
                $stmt = $pdo->prepare('SELECT id, ad, soyad, email, sifre_hash FROM users WHERE email = :email');
                $stmt->execute([':email' => $email]);
                $kullanici = $stmt->fetch();

                if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $kullanici['id'];
                    $_SESSION['ad']      = $kullanici['ad'];
                    $_SESSION['soyad']   = $kullanici['soyad'];
                    $_SESSION['email']   = $kullanici['email'];
                    logYaz($pdo, $kullanici['id'], $ip, 'giris_basarili', 'Giriş başarılı.');
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $userId = $kullanici ? $kullanici['id'] : null;
                    logYaz($pdo, $userId, $ip, 'giris_basarisiz', 'Hatalı e-posta veya şifre.');
                    $stmt2 = $pdo->prepare(
                        "SELECT COUNT(*) FROM users_logs
                         WHERE ip_adresi = :ip AND islem_tipi = 'giris_basarisiz'
                           AND created_at >= DATE_SUB(NOW(), INTERVAL :sure SECOND)"
                    );
                    $stmt2->execute([':ip' => $ip, ':sure' => LOCKOUT_DURATION]);
                    $kalan = MAX_LOGIN_ATTEMPTS - (int) $stmt2->fetchColumn();
                    if ($kalan <= 0) {
                        $hata = 'Hesap bu IP için kilitlendi. ' . (LOCKOUT_DURATION / 60) . ' dakika sonra tekrar deneyin.';
                    } else {
                        $hata = 'E-posta veya şifre hatalı. ' . $kalan . ' deneme hakkınız kaldı.';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('Giriş Hatası: ' . $e->getMessage());
            $hata = 'Giriş sırasında bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> – Giriş Yap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card mt-60">

            <p class="login-welcome-title">Hoş Geldiniz</p>

            <?php if ($bilgi): ?>
                <div class="alert alert-success"><?= htmlspecialchars($bilgi) ?></div>
            <?php endif; ?>
            <?php if ($hata): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="email">E-posta Adresi</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="ornek@mail.com" required maxlength="150">
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" class="form-control"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.5rem;">
                    Giriş Yap
                </button>
            </form>

            <hr class="gold-divider">
            <p class="text-center" style="font-size:.875rem; color:var(--text-dim);">
                Hesabın yok mu? <a href="register.php">Kayıt Ol</a>
            </p>
        </div>
    </div>
    <footer class="page-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>
