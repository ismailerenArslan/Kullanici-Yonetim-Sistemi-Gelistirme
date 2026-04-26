<?php
/**
 * dashboard.php – Kullanıcı paneli, profil bilgileri ve giriş geçmişi görüntüleme
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
session_start();

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$userId = (int) $_SESSION['user_id'];

try {
    $pdo = Database::getInstance()->getConnection();

    $stmtUser = $pdo->prepare('SELECT ad, soyad, email, created_at FROM users WHERE id = :id');
    $stmtUser->execute([':id' => $userId]);
    $kullanici = $stmtUser->fetch();

    if (!$kullanici) { session_destroy(); header('Location: login.php'); exit; }

    $stmtBasarisiz = $pdo->prepare(
        "SELECT ip_adresi, aciklama, created_at FROM users_logs
         WHERE user_id = :user_id AND islem_tipi = 'giris_basarisiz'
         ORDER BY created_at DESC LIMIT 5"
    );
    $stmtBasarisiz->execute([':user_id' => $userId]);
    $basarisizGirisler = $stmtBasarisiz->fetchAll();

    $stmtGecmis = $pdo->prepare(
        "SELECT islem_tipi, ip_adresi, aciklama, created_at FROM users_logs
         WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 50"
    );
    $stmtGecmis->execute([':user_id' => $userId]);
    $girisGecmisi = $stmtGecmis->fetchAll();

    // İstatistik sayıları
    $toplamGiris    = count(array_filter($girisGecmisi, fn($r) => $r['islem_tipi'] === 'giris_basarili'));
    $toplamBasarisiz = count($basarisizGirisler);

} catch (PDOException $e) {
    error_log('Dashboard Hatası: ' . $e->getMessage());
    die('Sayfa yüklenirken hata oluştu.');
}

function islemTipiFormatla(string $tip): array {
    $harita = [
        'giris_basarili'   => ['etiket' => '✓ Başarılı Giriş',  'sinif' => 'badge-success'],
        'giris_basarisiz'  => ['etiket' => '✕ Başarısız Giriş', 'sinif' => 'badge-danger'],
        'giris_engellendi' => ['etiket' => '⊘ Engellendi',      'sinif' => 'badge-warning'],
        'cikis'            => ['etiket' => '→ Çıkış',           'sinif' => 'badge-info'],
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
    <style>
        .dash-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .stat-box {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 1.2rem 1.5rem;
            animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both;
        }
        .stat-box:nth-child(1){animation-delay:.05s}
        .stat-box:nth-child(2){animation-delay:.1s}
        .stat-box:nth-child(3){animation-delay:.15s}
        .stat-label { font-size:.7rem; text-transform:uppercase; letter-spacing:.09em; color:var(--text-dim); margin-bottom:.4rem; }
        .stat-value { font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:700; color:var(--text); line-height:1; }
        .stat-value.accent { color:var(--gold); }
        .stat-value.red    { color:var(--danger); }
        @media(max-width:640px){ .dash-grid{ grid-template-columns:1fr; } }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="navbar-right">
            <span>Merhaba, <strong><?= htmlspecialchars($_SESSION['ad']) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Çıkış Yap</a>
        </div>
    </nav>

    <div class="container mt-30">

        <!-- İstatistikler -->
        <div class="dash-grid">
            <div class="stat-box">
                <p class="stat-label">Kayıt Tarihi</p>
                <p class="stat-value accent" style="font-size:1rem; margin-top:.25rem; font-family:'DM Sans',sans-serif; font-weight:500;">
                    <?= htmlspecialchars(date('d M Y', strtotime($kullanici['created_at']))) ?>
                </p>
            </div>
            <div class="stat-box">
                <p class="stat-label">Başarılı Giriş</p>
                <p class="stat-value accent"><?= $toplamGiris ?></p>
            </div>
            <div class="stat-box">
                <p class="stat-label">Başarısız Deneme</p>
                <p class="stat-value red"><?= $toplamBasarisiz ?></p>
            </div>
        </div>

        <!-- Profil -->
        <div class="card mb-30" style="animation-delay:.1s">
            <p style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--gold); margin-bottom:.5rem;">Hesap</p>
            <h3 class="card-title">Profil Bilgileri</h3>
            <table class="table profile-table">
                <tr>
                    <th>Ad Soyad</th>
                    <td><?= htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']) ?></td>
                </tr>
                <tr>
                    <th>E-posta</th>
                    <td><?= htmlspecialchars($kullanici['email']) ?></td>
                </tr>
                <tr>
                    <th>Üyelik Tarihi</th>
                    <td><?= htmlspecialchars($kullanici['created_at']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Son başarısız girişler -->
        <div class="card mb-30" style="animation-delay:.15s">
            <p style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--danger); margin-bottom:.5rem;">Güvenlik</p>
            <h3 class="card-title">Son 5 Başarısız Giriş Denemesi</h3>
            <?php if (empty($basarisizGirisler)): ?>
                <p class="text-muted" style="font-size:.875rem;">Başarısız giriş denemesi bulunmuyor.</p>
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
                                <td style="font-family:monospace; font-size:.82rem; color:var(--text-dim);"><?= htmlspecialchars($kayit['ip_adresi']) ?></td>
                                <td><?= htmlspecialchars($kayit['aciklama'] ?? '—') ?></td>
                                <td style="color:var(--text-dim); font-size:.82rem; white-space:nowrap;"><?= htmlspecialchars($kayit['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Giriş geçmişi -->
        <div class="card mb-30" style="animation-delay:.2s">
            <p style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--gold); margin-bottom:.5rem;">Geçmiş</p>
            <h3 class="card-title">Giriş / Çıkış Geçmişi</h3>
            <?php if (empty($girisGecmisi)): ?>
                <p class="text-muted" style="font-size:.875rem;">Kayıtlı işlem bulunmuyor.</p>
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
                            $format = islemTipiFormatla($kayit['islem_tipi']); ?>
                            <tr>
                                <td><span class="badge <?= $format['sinif'] ?>"><?= $format['etiket'] ?></span></td>
                                <td style="font-family:monospace; font-size:.82rem; color:var(--text-dim);"><?= htmlspecialchars($kayit['ip_adresi']) ?></td>
                                <td style="color:var(--text-dim); font-size:.84rem;"><?= htmlspecialchars($kayit['aciklama'] ?? '—') ?></td>
                                <td style="color:var(--text-dim); font-size:.82rem; white-space:nowrap;"><?= htmlspecialchars($kayit['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <footer class="page-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> &nbsp;·&nbsp; BGY206 Web Programlama&#8209;I
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
