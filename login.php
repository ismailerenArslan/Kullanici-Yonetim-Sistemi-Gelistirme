<?php
/**
 * login.php
 * Kullanıcı giriş formu ve kimlik doğrulama mantığı.
 * - Başarılı girişte session başlatılır ve dashboard.php'ye yönlendirilir.
 * - Aynı IP'den 3 hatalı girişte IP belirli süre engellenir.
 * - Her giriş denemesi (başarılı/başarısız/engellendi) users_logs tablosuna kaydedilir.
 * - Session fixation saldırısına karşı session_regenerate_id(true) kullanılır.
 * - CSRF token ile Cross-Site Request Forgery saldırılarına karşı korunur.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

session_start();

// Zaten giriş yapmışsa panele yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Hata ve bilgi mesajları
$hata  = '';
$bilgi = '';

// ─── CSRF Token Oluştur ───────────────────────────────────────────────────────
// Her sayfa yüklemesinde session'da bir token yoksa yeni oluştur.
// Token, form gönderiminde doğrulanarak CSRF saldırılarını engeller.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Başarılı kayıt sonrası yönlendirme mesajı
if (isset($_GET['kayit']) && $_GET['kayit'] === 'basarili') {
    $bilgi = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
}

/**
 * Ziyaretçinin IP adresini döndüren yardımcı fonksiyon.
 */
function getIpAdresi(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        return trim($ip);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Belirtilen IP adresinin kilitli olup olmadığını kontrol eder.
 */
function ipKilitliMi(PDO $pdo, string $ip): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM users_logs
         WHERE ip_adresi  = :ip
           AND islem_tipi = 'giris_basarisiz'
           AND created_at >= DATE_SUB(NOW(), INTERVAL :sure SECOND)"
    );
    $stmt->execute([':ip' => $ip, ':sure' => LOCKOUT_DURATION]);
    return (int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
}

/**
 * İşlem kaydını users_logs tablosuna yazar.
 */
function logYaz(PDO $pdo, ?int $userId, string $ip, string $islemTipi, ?string $aciklama = null): void
{
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

// ─── POST İsteği Geldiğinde Giriş İşlemini Yap ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ─── CSRF Token Doğrulama ─────────────────────────────────────────────────
    // hash_equals() zamanlama saldırılarına (timing attack) karşı güvenli karşılaştırma yapar.
    $csrfGelen = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfGelen)) {
        // Geçersiz token: olası CSRF saldırısı; isteği reddet
        $hata = 'Geçersiz form isteği. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        // ─── Token doğrulandı; kullanılan token'ı hemen yenile ───────────────
        // Tek kullanımlık token (replay saldırısını önler)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $email = trim($_POST['email'] ?? '');
        $sifre = $_POST['sifre']      ?? '';
        $ip    = getIpAdresi();

        try {
            $pdo = Database::getInstance()->getConnection();

            // ─── IP Kilitleme Kontrolü ────────────────────────────────────────
            if (ipKilitliMi($pdo, $ip)) {
                logYaz($pdo, null, $ip, 'giris_engellendi',
                    'Çok fazla başarısız deneme nedeniyle IP engellendi.');
                $hata = 'Çok fazla başarısız giriş denemesi. Lütfen '
                      . (LOCKOUT_DURATION / 60) . ' dakika sonra tekrar deneyin.';
            } else {
                // ─── Kullanıcıyı E-posta ile Ara ─────────────────────────────
                $stmt = $pdo->prepare(
                    'SELECT id, ad, soyad, email, sifre_hash FROM users WHERE email = :email'
                );
                $stmt->execute([':email' => $email]);
                $kullanici = $stmt->fetch();

                if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
                    // ─── Başarılı Giriş ───────────────────────────────────────
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $kullanici['id'];
                    $_SESSION['ad']      = $kullanici['ad'];
                    $_SESSION['soyad']   = $kullanici['soyad'];
                    $_SESSION['email']   = $kullanici['email'];

                    logYaz($pdo, $kullanici['id'], $ip, 'giris_basarili', 'Giriş başarılı.');

                    header('Location: dashboard.php');
                    exit;
                } else {
                    // ─── Başarısız Giriş ──────────────────────────────────────
                    $userId = $kullanici ? $kullanici['id'] : null;
                    logYaz($pdo, $userId, $ip, 'giris_basarisiz', 'Hatalı e-posta veya şifre.');

                    $stmt2 = $pdo->prepare(
                        "SELECT COUNT(*) FROM users_logs
                         WHERE ip_adresi  = :ip
                           AND islem_tipi = 'giris_basarisiz'
                           AND created_at >= DATE_SUB(NOW(), INTERVAL :sure SECOND)"
                    );
                    $stmt2->execute([':ip' => $ip, ':sure' => LOCKOUT_DURATION]);
                    $kalan = MAX_LOGIN_ATTEMPTS - (int) $stmt2->fetchColumn();

                    if ($kalan <= 0) {
                        $hata = 'Hesap bu IP için kilitlendi. '
                              . (LOCKOUT_DURATION / 60) . ' dakika sonra tekrar deneyin.';
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
            <h2 class="card-title">🔑 Giriş Yap</h2>

            <?php if ($bilgi): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($bilgi) ?>
                </div>
            <?php endif; ?>

            <?php if ($hata): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($hata) ?>
                </div>
            <?php endif; ?>

            <!-- Standart HTML formu kullanılır (JavaScript ile form oluşturmak gereksiz ve hatalıdır) -->
            <form method="POST" action="login.php">

                <!-- CSRF Token: Gizli alan olarak gömülür, POST ile doğrulanır -->
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" class="form-control"
                           required maxlength="150">
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </form>

            <p class="text-center mt-15">
                Hesabın yok mu? <a href="register.php">Kayıt Ol</a>
            </p>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
