<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['admin'])) {
    header("Location: /eticaret/giris.php");
    exit();
}

$kullanicilar = $db->query(
    "SELECT k.KullaniciID, k.KullaniciAdi, k.AdSoyad, k.Eposta,
            COUNT(s.SiparisID) AS SiparisSayisi,
            COALESCE(SUM(s.ToplamTutar), 0) AS ToplamHarcama
     FROM Kullanicilar k
     LEFT JOIN Siparisler s ON s.KullaniciID = k.KullaniciID
     GROUP BY k.KullaniciID, k.KullaniciAdi, k.AdSoyad, k.Eposta
     ORDER BY k.KullaniciID DESC"
)->fetchAll();
?>

<div class="section-header" style="margin-bottom:24px;">
    <div>
        <h2>👥 Admin Kullanıcı Listesi</h2>
        <p class="text-muted" style="font-size:.88rem; margin-top:4px;"><?php echo count($kullanicilar); ?> kullanıcı</p>
    </div>
    <a href="/eticaret/admin/index.php" class="btn btn-ghost btn-sm">← Panel</a>
</div>

<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:4px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Kullanıcı Adı</th>
                    <th>Sipariş</th>
                    <th>Toplam Harcama</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kullanicilar as $k): ?>
                <tr>
                    <td><span class="tag tag-blue">#<?php echo $k['KullaniciID']; ?></span></td>
                    <td><?php echo htmlspecialchars($k['AdSoyad'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($k['Eposta']); ?></td>
                    <td><?php echo htmlspecialchars($k['KullaniciAdi']); ?></td>
                    <td><?php echo (int)$k['SiparisSayisi']; ?></td>
                    <td class="text-success fw-700"><?php echo number_format((float)$k['ToplamHarcama'], 2, ',', '.'); ?> TL</td>
                    <td>
                        <?php if ($k['KullaniciAdi'] === 'admin'): ?>
                            <span class="tag tag-purple">Admin</span>
                        <?php else: ?>
                            <span class="tag tag-green">Üye</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
