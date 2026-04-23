<?php
/**
 * dashboard.php
 * Giriş yapmış kullanıcıya özel yönetim paneli.
 * - Session kontrolü yapılır; giriş yapılmamışsa login.php'ye yönlendirilir.
 * - Kullanıcının profil bilgileri gösterilir.
 * - Son 5 başarısız giriş denemesi listelenir.
 * - Tüm giriş/çıkış geçmişi listelenir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

session_start();

// ─── Session Kontrolü (Korumalı Sayfa) ───────────────────────────────────────
// Oturum açık değilse login sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Oturumdaki kullanıcı ID'sini al
$userId = (int) $_SESSION['user_id'];

try {
    $pdo = Database::getInstance()->getConnection();

    // ─── Kullanıcı Profil Bilgilerini Getir ───────────────────────────────
    $stmtUser = $pdo->prepare(
        'SELECT ad, soyad, email, created_at FROM users WHERE id = :id'
    );
    $stmtUser->execute([':id' => $userId]);
    $kullanici = $stmtUser->fetch();

    // Kullanıcı veritabanında bulunamadıysa oturumu sonlandır
    if (!$kullanici) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    // ─── Son 5 Başarısız Giriş Denemesini Getir ───────────────────────────
    $stmtBasarisiz = $pdo->prepare(
        "SELECT ip_adresi, aciklama, created_at
         FROM users_logs
         WHERE user_id    = :user_id
           AND islem_tipi = 'giris_basarisiz'
         ORDER BY created_at DESC
         LIMIT 5"
    );
    $stmtBasarisiz->execute([':user_id' => $userId]);
    $basarisizGirisler = $stmtBasarisiz->fetchAll();

    // ─── Tüm Giriş Geçmişini Getir (Başarılı + Başarısız) ────────────────
    $stmtGecmis = $pdo->prepare(
        "SELECT islem_tipi, ip_adresi, aciklama, created_at
         FROM users_logs
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT 50"
    );
    $stmtGecmis->execute([':user_id' => $userId]);
    $girisGecmisi = $stmtGecmis->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard Hatası: ' . $e->getMessage());
    die('Sayfa yüklenirken hata oluştu.');
}

/**
 * İşlem tipini Türkçe etikete ve CSS sınıfına çevirir.
 * Görüntüleme kolaylığı için yardımcı fonksiyon.
 *
 * @param string $tip  ENUM değeri
 * @return array       ['etiket' => '...', 'sinif' => '...']
 */
function islemTipiFormatla(string $tip): array
{
    $harita = [
        'giris_basarili'   => ['etiket' => '✅ Başarılı Giriş',   'sinif' => 'badge-success'],
        'giris_basarisiz'  => ['etiket' => '❌ Başarısız Giriş',  'sinif' => 'badge-danger'],
        'giris_engellendi' => ['etiket' => '🚫 Engellendi',       'sinif' => 'badge-warning'],
        'cikis'            => ['etiket' => '👋 Çıkış',            'sinif' => 'badge-info'],
    ];
    return $harita[$tip] ?? ['etiket' => htmlspecialchars($tip), 'sinif' => 'badge-secondary'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> – Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- ─── Üst Navigasyon Çubuğu ──────────────────────────────────────────── -->
    <nav class="navbar">
        <span class="navbar-brand">🔐 <?= htmlspecialchars(APP_NAME) ?></span>
        <div class="navbar-right">
            <span>Merhaba, <strong><?= htmlspecialchars($_SESSION['ad']) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm ml-15">Çıkış Yap</a>
        </div>
    </nav>

    <div class="container mt-30">

        <!-- ─── Profil Bilgileri Kartı ──────────────────────────────────────── -->
        <div class="card mb-30">
            <h3 class="card-title">👤 Profil Bilgileri</h3>
            <table class="table">
                <tr>
                    <th>Ad Soyad</th>
                    <td><?= htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']) ?></td>
                </tr>
                <tr>
                    <th>E-posta</th>
                    <td><?= htmlspecialchars($kullanici['email']) ?></td>
                </tr>
                <tr>
                    <th>Kayıt Tarihi</th>
                    <td><?= htmlspecialchars($kullanici['created_at']) ?></td>
                </tr>
            </table>
        </div>

        <!-- ─── Son 5 Başarısız Giriş Denemesi ─────────────────────────────── -->
        <div class="card mb-30">
            <h3 class="card-title">⚠️ Son 5 Başarısız Giriş Denemesi</h3>
            <?php if (empty($basarisizGirisler)): ?>
                <p class="text-muted">Başarısız giriş denemesi bulunmuyor.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>IP Adresi</th>
                            <th>Açıklama</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($basarisizGirisler as $kayit): ?>
                            <tr>
                                <td><?= htmlspecialchars($kayit['ip_adresi']) ?></td>
                                <td><?= htmlspecialchars($kayit['aciklama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($kayit['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ─── Giriş Geçmişi (Tümü) ───────────────────────────────────────── -->
        <div class="card mb-30">
            <h3 class="card-title">📋 Giriş / Çıkış Geçmişi</h3>
            <?php if (empty($girisGecmisi)): ?>
                <p class="text-muted">Kayıtlı işlem bulunmuyor.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>İşlem</th>
                            <th>IP Adresi</th>
                            <th>Açıklama</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($girisGecmisi as $kayit):
                            $format = islemTipiFormatla($kayit['islem_tipi']);
                        ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $format['sinif'] ?>">
                                        <?= $format['etiket'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($kayit['ip_adresi']) ?></td>
                                <td><?= htmlspecialchars($kayit['aciklama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($kayit['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div><!-- /container -->

    <script src="assets/js/main.js"></script>
</body>
</html>