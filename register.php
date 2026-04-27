<?php
/**
 * register.php – Lüks tasarımlı kayıt formu.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$hatalar = [];
$form    = ['ad' => '', 'soyad' => '', 'email' => ''];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function metinUzunlugu(string $metin): int {
    if (function_exists('mb_strlen')) {
        return mb_strlen($metin, 'UTF-8');
    }

    if (preg_match_all('/./us', $metin, $eslesmeler) !== false) {
        return count($eslesmeler[0]);
    }

    return strlen($metin);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfGelen = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfGelen)) {
        $hatalar[] = 'Geçersiz form isteği. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $ad          = trim($_POST['ad']          ?? '');
        $soyad       = trim($_POST['soyad']       ?? '');
        $email       = trim($_POST['email']       ?? '');
        $sifre       = $_POST['sifre']            ?? '';
        $sifreTekrar = $_POST['sifre_tekrar']     ?? '';
        $form        = ['ad' => $ad, 'soyad' => $soyad, 'email' => $email];

        if (empty($ad))            $hatalar[] = 'Ad alanı zorunludur.';
        elseif (metinUzunlugu($ad)>50) $hatalar[] = 'Ad en fazla 50 karakter olabilir.';

        if (empty($soyad))               $hatalar[] = 'Soyad alanı zorunludur.';
        elseif (metinUzunlugu($soyad)>50)    $hatalar[] = 'Soyad en fazla 50 karakter olabilir.';

        if (empty($email))                              $hatalar[] = 'E-posta alanı zorunludur.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
        elseif (metinUzunlugu($email)>150)                  $hatalar[] = 'E-posta en fazla 150 karakter olabilir.';

        if (empty($sifre))               $hatalar[] = 'Şifre alanı zorunludur.';
        elseif (metinUzunlugu($sifre)<8)     $hatalar[] = 'Şifre en az 8 karakter olmalıdır.';
        elseif (!preg_match('/[A-Z]/',$sifre)) $hatalar[] = 'Şifre en az bir büyük harf içermelidir.';
        elseif (!preg_match('/[0-9]/',$sifre)) $hatalar[] = 'Şifre en az bir rakam içermelidir.';

        if ($sifre !== $sifreTekrar) $hatalar[] = 'Şifre ve şifre tekrar alanları eşleşmiyor.';

        if (empty($hatalar)) {
            try {
                $pdo  = Database::getInstance()->getConnection();
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
                $stmt->execute([':email' => $email]);
                if ($stmt->fetchColumn()) {
                    $hatalar[] = 'Bu e-posta adresi zaten kayıtlı. Lütfen giriş yapın.';
                } else {
                    $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);
                    $insert    = $pdo->prepare(
                        'INSERT INTO users (ad, soyad, email, sifre_hash) VALUES (:ad, :soyad, :email, :sifre_hash)'
                    );
                    $insert->execute([':ad'=>$ad,':soyad'=>$soyad,':email'=>$email,':sifre_hash'=>$sifreHash]);
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

            <p style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--gold); margin-bottom:.5rem;">Yeni Hesap</p>
            <h2 class="card-title">Kayıt Ol</h2>

            <?php if (!empty($hatalar)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($hatalar as $hata): ?>
                            <li><?= htmlspecialchars($hata) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:.85rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="ad">Ad</label>
                        <input type="text" id="ad" name="ad" class="form-control"
                               placeholder="Adınız"
                               value="<?= htmlspecialchars($form['ad']) ?>" required maxlength="50">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="soyad">Soyad</label>
                        <input type="text" id="soyad" name="soyad" class="form-control"
                               placeholder="Soyadınız"
                               value="<?= htmlspecialchars($form['soyad']) ?>" required maxlength="50">
                    </div>
                </div>

                <div class="form-group mt-15">
                    <label for="email">E-posta Adresi</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="ornek@mail.com"
                           value="<?= htmlspecialchars($form['email']) ?>" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" class="form-control"
                           placeholder="••••••••" required minlength="8">
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
                           placeholder="••••••••" required minlength="8">
                    <p id="eslesmez-uyari" style="display:none; color:var(--danger); font-size:.8rem; margin-top:.4rem;">
                        Şifreler eşleşmiyor.
                    </p>
                </div>

                <button type="submit" id="kayit-btn" class="btn btn-primary btn-block" style="margin-top:1.5rem;">
                    Hesap Oluştur
                </button>
            </form>

            <script>
            (function () {
                var sifre=document.getElementById('sifre'), tekrar=document.getElementById('sifre_tekrar');
                var cubukKap=document.getElementById('guc-cubuk-kap'), cubuk=document.getElementById('guc-cubuk');
                var kuralUzunluk=document.getElementById('kural-uzunluk'), kuralBuyuk=document.getElementById('kural-buyuk');
                var kuralRakam=document.getElementById('kural-rakam'), uyari=document.getElementById('eslesmez-uyari');
                var RENKLER=['#e74c3c','#e67e22','#f1c40f','#2ecc71'], GENISLIK=['25%','50%','75%','100%'];

                function kuralRengi(el, gecti) {
                    el.style.color=gecti?'#4CAF7D':'var(--text-dim)';
                    el.style.fontWeight=gecti?'600':'normal';
                }

                function gucHesapla(v) {
                    var p=0, uOk=v.length>=8, bOk=/[A-Z]/.test(v), rOk=/[0-9]/.test(v);
                    if(uOk)p++; if(bOk)p++; if(rOk)p++;
                    if(/[^A-Za-z0-9]/.test(v))p++;
                    kuralRengi(kuralUzunluk,uOk); kuralRengi(kuralBuyuk,bOk); kuralRengi(kuralRakam,rOk);
                    return {puan:Math.min(p,4),uzunlukOk:uOk,buyukOk:bOk,rakamOk:rOk};
                }

                sifre.addEventListener('input', function(){
                    if(!this.value.length){cubukKap.style.display='none';return;}
                    cubukKap.style.display='block';
                    var s=gucHesapla(this.value), i=s.puan>0?s.puan-1:0;
                    cubuk.style.width=GENISLIK[i]; cubuk.style.background=RENKLER[i];
                    if(tekrar.value.length) uyari.style.display=(this.value!==tekrar.value)?'block':'none';
                });

                tekrar.addEventListener('input', function(){
                    uyari.style.display=(sifre.value!==this.value)?'block':'none';
                });

                document.querySelector('form').addEventListener('submit', function(e){
                    var s=gucHesapla(sifre.value);
                    if(!s.uzunlukOk||!s.buyukOk||!s.rakamOk){
                        e.preventDefault(); cubukKap.style.display='block';
                        alert('Lütfen şifre kurallarını karşıladığınızdan emin olun.'); return;
                    }
                    if(sifre.value!==tekrar.value){e.preventDefault();uyari.style.display='block';}
                });
            })();
            </script>

            <hr class="gold-divider">
            <p class="text-center" style="font-size:.875rem; color:var(--text-dim);">
                Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
            </p>
        </div>
    </div>
    <footer class="page-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>
