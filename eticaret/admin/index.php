<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

// Yetki kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: /giris.php");
    exit();
}

// İstatistikler
try {
    $toplamUrun    = $db->query("SELECT COUNT(*) FROM Urunler")->fetchColumn();
    $toplamUye     = $db->query("SELECT COUNT(*) FROM Kullanicilar")->fetchColumn();
    $toplamSiparis = $db->query("SELECT COUNT(*) FROM Siparisler")->fetchColumn();
    $bekleyenSiparis = $db->query("SELECT COUNT(*) FROM Siparisler WHERE Durum = N'Beklemede'")->fetchColumn();
    $sonUrunler = $db->query("SELECT TOP 5 * FROM Urunler ORDER BY UrunID DESC")->fetchAll();
} catch (Exception $e) {
    $toplamUrun = $toplamUye = 0;
    $sonUrunler = [];
}
?>

<!-- Başlık -->
<div class="section-header" style="margin-bottom:32px;">
    <div>
        <h2 style="font-size:1.9rem;">Yönetim Paneli</h2>
        <p class="text-muted" style="margin-top:4px;">
            Hoş geldin, <strong><?php echo htmlspecialchars($_SESSION['ad_soyad']); ?></strong>
        </p>
    </div>
    <span class="tag tag-green" style="padding:6px 14px; font-size:.82rem;">● Çevrimiçi</span>
</div>

<!-- İstatistik Kartları -->
<div class="grid grid-3" style="margin-bottom:36px; gap:20px;">
    <div class="stat-card blue">
        <div class="stat-label">Toplam Ürün</div>
        <div class="stat-value"><?php echo $toplamUrun; ?></div>
        <a href="/eticaret/admin/urun_listesi.php" class="stat-sub" style="color:var(--accent);">Tümünü Gör →</a>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Kayıtlı Üye</div>
        <div class="stat-value"><?php echo $toplamUye; ?></div>
        <div class="stat-sub">Aktif Kullanıcılar</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Toplam Sipariş</div>
        <div class="stat-value"><?php echo $toplamSiparis; ?></div>
        <a href="/eticaret/admin/siparisler.php" class="stat-sub" style="color:#a78bfa;">
            <?php echo $bekleyenSiparis; ?> beklemede →
        </a>
    </div>
</div>

<!-- Hızlı İşlemler -->
<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:28px; margin-bottom:28px;">
    <h3 style="margin-bottom:20px; font-size:1rem;">🛠 Hızlı İşlemler</h3>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="/eticaret/admin/urun_ekle.php"   class="btn btn-primary">➕ Yeni Ürün Ekle</a>
        <a href="/eticaret/admin/urun_listesi.php" class="btn btn-dark">📝 Ürünleri Yönet</a>
        <a href="/eticaret/admin/siparisler.php"   class="btn btn-dark">🧾 Siparişleri Yönet</a>
        <a href="/eticaret/index.php" target="_blank" class="btn btn-ghost">👁️ Siteyi Önizle</a>
        <a href="/eticaret/cikis.php" class="btn btn-ghost" style="color:var(--danger); border-color:var(--danger);">🚪 Çıkış</a>
    </div>
</div>

<!-- Son Eklenen Ürünler -->
<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:28px;">
    <div class="section-header" style="margin-bottom:16px;">
        <h3 style="font-size:1rem;">🕐 Son Eklenen Ürünler</h3>
        <a href="/eticaret/admin/urun_listesi.php" class="btn btn-ghost btn-sm">Tümü →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ürün Adı</th>
                    <th>Fiyat</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sonUrunler as $u): ?>
                <tr>
                    <td><span class="tag tag-blue">#<?php echo $u['UrunID']; ?></span></td>
                    <td><?php echo htmlspecialchars($u['UrunAdi']); ?></td>
                    <td class="text-success fw-700"><?php echo number_format($u['Fiyat'], 2, ',', '.'); ?> TL</td>
                    <td style="display:flex; gap:8px;">
                        <a href="/eticaret/admin/urun_guncelle.php?id=<?php echo $u['UrunID']; ?>" class="btn btn-dark btn-sm">✏️ Düzenle</a>
                        <a href="/eticaret/admin/urun_listesi.php?sil=<?php echo $u['UrunID']; ?>"
                           class="btn btn-ghost btn-sm" style="color:var(--danger);"
                           onclick="return confirm('Silmek istiyor musun?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
