<?php
/**
 * register.php
 * Yeni kullanıcı kayıt formu ve kayıt işleme mantığı.
 * - Ad ve soyad ayrı alınır.
 * - E-posta benzersizliği kontrol edilir.
 * - Şifre password_hash() ile bcrypt ile hashlenir.
 * - CSRF token ile Cross-Site Request Forgery saldırılarına karşı korunur.
 * - Başarılı kayıt sonrası login.php'ye yönlendirme yapılır.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

session_start();

// Zaten giriş yapmışsa panele yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Hata ve başarı mesajlarını tutacak değişkenler
$hatalar = [];
$form    = ['ad' => '', 'soyad' => '', 'email' => ''];

// ─── CSRF Token Oluştur ───────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ─── POST İsteği Geldiğinde Kayıt İşlemini Yap ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ─── CSRF Token Doğrulama ─────────────────────────────────────────────────
    $csrfGelen = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfGelen)) {
        $hatalar[] = 'Geçersiz form isteği. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        // ─── Token doğrulandı; kullanılan token'ı hemen yenile ───────────────
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Kullanıcıdan gelen verileri al
        $ad          = trim($_POST['ad']          ?? '');
        $soyad       = trim($_POST['soyad']       ?? '');
        $email       = trim($_POST['email']       ?? '');
        $sifre       = $_POST['sifre']            ?? '';
        $sifreTekrar = $_POST['sifre_tekrar']     ?? '';

        // Eski değerleri form alanlarını doldurmak için sakla
        $form = ['ad' => $ad, 'soyad' => $soyad, 'email' => $email];

        // ─── Doğrulama Kontrolleri ────────────────────────────────────────────
        if (empty($ad)) {
            $hatalar[] = 'Ad alanı zorunludur.';
        } elseif (mb_strlen($ad) > 50) {
            $hatalar[] = 'Ad en fazla 50 karakter olabilir.';
        }

        if (empty($soyad)) {
            $hatalar[] = 'Soyad alanı zorunludur.';
        } elseif (mb_strlen($soyad) > 50) {
            $hatalar[] = 'Soyad en fazla 50 karakter olabilir.';
        }

        if (empty($email)) {
            $hatalar[] = 'E-posta alanı zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
        } elseif (mb_strlen($email) > 150) {
            $hatalar[] = 'E-posta en fazla 150 karakter olabilir.';
        }

        if (empty($sifre)) {
            $hatalar[] = 'Şifre alanı zorunludur.';
        } elseif (mb_strlen($sifre) < 8) {
            $hatalar[] = 'Şifre en az 8 karakter olmalıdır.';
        } elseif (!preg_match('/[A-Z]/', $sifre)) {
            $hatalar[] = 'Şifre en az bir büyük harf içermelidir.';
        } elseif (!preg_match('/[0-9]/', $sifre)) {
            $hatalar[] = 'Şifre en az bir rakam içermelidir.';
        }

        if ($sifre !== $sifreTekrar) {
            $hatalar[] = 'Şifre ve şifre tekrar alanları eşleşmiyor.';
        }

        // ─── Hata Yoksa Veritabanı İşlemini Yap ──────────────────────────────
        if (empty($hatalar)) {
            try {
                $pdo = Database::getInstance()->getConnection();

                // E-posta adresinin daha önce kayıtlı olup olmadığını kontrol et
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
                $stmt->execute([':email' => $email]);

                if ($stmt->fetchColumn()) {
                    $hatalar[] = 'Bu e-posta adresi zaten kayıtlı. Lütfen giriş yapın.';
                } else {
                    // Şifreyi bcrypt ile hashle
                    $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);

                    $insert = $pdo->prepare(
                        'INSERT INTO users (ad, soyad, email, sifre_hash)
                         VALUES (:ad, :soyad, :email, :sifre_hash)'
                    );
                    $insert->execute([
                        ':ad'         => $ad,
                        ':soyad'      => $soyad,
                        ':email'      => $email,
                        ':sifre_hash' => $sifreHash,
                    ]);

                    header('Location: login.php?kayit=basarili');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Kayıt Hatası: ' . $e->getMessage());
                $hatalar[] = 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> – Kayıt Ol</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card mt-60">
            <h2 class="card-title">📝 Kayıt Ol</h2>

            <?php if (!empty($hatalar)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($hatalar as $hata): ?>
                            <li><?= htmlspecialchars($hata) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Standart HTML formu kullanılır -->
            <form method="POST" action="register.php">

                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="ad">Ad</label>
                    <input type="text" id="ad" name="ad" class="form-control"
                           value="<?= htmlspecialchars($form['ad']) ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="soyad">Soyad</label>
                    <input type="text" id="soyad" name="soyad" class="form-control"
                           value="<?= htmlspecialchars($form['soyad']) ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($form['email']) ?>" required maxlength="150">
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" class="form-control"
                           required minlength="8">

                    <!-- Şifre gücü göstergesi -->
                    <div id="guc-cubuk-kap" style="margin-top:8px; display:none;">
                        <div style="background:#e0e0e0; border-radius:4px; height:6px; overflow:hidden;">
                            <div id="guc-cubuk" style="height:100%; width:0%; border-radius:4px; transition:width .3s, background .3s;"></div>
                        </div>
                        <ul id="guc-listesi" style="margin:8px 0 0; padding-left:18px; font-size:13px; color:#555; line-height:1.8;">
                            <li id="kural-uzunluk">En az 8 karakter</li>
                            <li id="kural-buyuk">En az 1 büyük harf (A-Z)</li>
                            <li id="kural-rakam">En az 1 rakam (0-9)</li>
                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sifre_tekrar">Şifre Tekrar</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" class="form-control"
                           required minlength="8">
                    <p id="eslesmez-uyari" style="display:none; color:#c0392b; font-size:13px; margin-top:5px;">
                        Şifreler eşleşmiyor.
                    </p>
                </div>
                <button type="submit" id="kayit-btn" class="btn btn-primary btn-block">Kayıt Ol</button>
            </form>

            <script>
            (function () {
                var sifre       = document.getElementById('sifre');
                var tekrar      = document.getElementById('sifre_tekrar');
                var cubukKap    = document.getElementById('guc-cubuk-kap');
                var cubuk       = document.getElementById('guc-cubuk');
                var kuralUzunluk = document.getElementById('kural-uzunluk');
                var kuralBuyuk  = document.getElementById('kural-buyuk');
                var kuralRakam  = document.getElementById('kural-rakam');
                var uyari       = document.getElementById('eslesmez-uyari');
                var btn         = document.getElementById('kayit-btn');

                var RENKLER = ['#e74c3c', '#e67e22', '#f1c40f', '#2ecc71'];
                var GENISLIK = ['25%', '50%', '75%', '100%'];

                function kuralRengi(el, gecti) {
                    el.style.color = gecti ? '#27ae60' : '#555';
                    el.style.fontWeight = gecti ? '600' : 'normal';
                }

                function gucHesapla(deger) {
                    var puan = 0;
                    var uzunlukOk = deger.length >= 8;
                    var buyukOk   = /[A-Z]/.test(deger);
                    var rakamOk   = /[0-9]/.test(deger);

                    if (uzunlukOk) puan++;
                    if (buyukOk)   puan++;
                    if (rakamOk)   puan++;
                    // Bonus: özel karakter varsa ekstra puan
                    if (/[^A-Za-z0-9]/.test(deger)) puan++;

                    kuralRengi(kuralUzunluk, uzunlukOk);
                    kuralRengi(kuralBuyuk,   buyukOk);
                    kuralRengi(kuralRakam,   rakamOk);

                    return { puan: Math.min(puan, 4), uzunlukOk: uzunlukOk, buyukOk: buyukOk, rakamOk: rakamOk };
                }

                sifre.addEventListener('input', function () {
                    var deger = this.value;

                    if (deger.length === 0) {
                        cubukKap.style.display = 'none';
                        return;
                    }

                    cubukKap.style.display = 'block';
                    var sonuc = gucHesapla(deger);
                    var idx   = sonuc.puan > 0 ? sonuc.puan - 1 : 0;
                    cubuk.style.width      = GENISLIK[idx];
                    cubuk.style.background = RENKLER[idx];

                    // Şifre tekrar alanı doluysa eşleşme kontrolü güncelle
                    if (tekrar.value.length > 0) {
                        uyari.style.display = (deger !== tekrar.value) ? 'block' : 'none';
                    }
                });

                tekrar.addEventListener('input', function () {
                    uyari.style.display = (sifre.value !== this.value) ? 'block' : 'none';
                });

                // Sunucu zaten kontrol ediyor; bu sadece kullanıcıya hızlı geri bildirim için
                document.querySelector('form').addEventListener('submit', function (e) {
                    var sonuc = gucHesapla(sifre.value);
                    if (!sonuc.uzunlukOk || !sonuc.buyukOk || !sonuc.rakamOk) {
                        e.preventDefault();
                        cubukKap.style.display = 'block';
                        alert('Lütfen şifre kurallarını karşıladığınızdan emin olun.');
                        return;
                    }
                    if (sifre.value !== tekrar.value) {
                        e.preventDefault();
                        uyari.style.display = 'block';
                    }
                });
            })();
            </script>

            <p class="text-center mt-15">
                Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
            </p>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
