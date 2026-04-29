<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: /eticaret/giris.php");
    exit();
}

$kullaniciID = $_SESSION['kullanici_id'];

// Kullanıcının siparişlerini çek
$siparisler = $db->prepare(
    "SELECT * FROM Siparisler WHERE KullaniciID = ? ORDER BY Tarih DESC"
);
$siparisler->execute([$kullaniciID]);
$siparisler = $siparisler->fetchAll();

// Seçili sipariş detayı
$detaylar   = [];
$seciliID   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($seciliID) {
    // Sadece bu kullanıcının siparişi mi kontrol et
    $kontrol = $db->prepare("SELECT SiparisID FROM Siparisler WHERE SiparisID = ? AND KullaniciID = ?");
    $kontrol->execute([$seciliID, $kullaniciID]);
    if ($kontrol->fetch()) {
        $detaySorgu = $db->prepare(
            "SELECT sd.*, u.UrunAdi, u.GorselURL
             FROM SiparisDetay sd
             JOIN Urunler u ON sd.UrunID = u.UrunID
             WHERE sd.SiparisID = ?"
        );
        $detaySorgu->execute([$seciliID]);
        $detaylar = $detaySorgu->fetchAll();
    }
}
?>

<div class="section-header" style="margin-bottom:28px;">
    <h2>📦 Siparişlerim</h2>
    <a href="/eticaret/urunler.php" class="btn btn-ghost btn-sm">Alışverişe Devam</a>
</div>

<?php if (empty($siparisler)): ?>
    <div style="text-align:center; padding:80px 0; background:var(--surface); border:1px solid var(--border); border-radius:16px;">
        <div style="font-size:3rem; margin-bottom:16px;">📭</div>
        <h3 style="color:var(--muted); font-weight:500;">Henüz siparişin yok.</h3>
        <a href="/eticaret/urunler.php" class="btn btn-primary" style="margin-top:20px;">Alışverişe Başla</a>
    </div>
<?php else: ?>

<div style="display:grid; grid-template-columns:<?php echo $seciliID ? '1fr 1.2fr' : '1fr'; ?>; gap:24px; align-items:start;">

    <!-- Sipariş Listesi -->
    <div>
        <?php
        $durumRenk = [
            'Beklemede'  => 'tag-yellow',
            'Onaylandı'  => 'tag-blue',
            'Kargoda'    => 'tag-purple',
            'Teslim Edildi' => 'tag-green',
            'İptal'      => 'tag-red',
        ];
        foreach ($siparisler as $s):
            $aktif = ($seciliID == $s['SiparisID']);
            $renk  = $durumRenk[$s['Durum']] ?? 'tag-blue';
        ?>
        <a href="/eticaret/siparislerim.php?id=<?php echo $s['SiparisID']; ?>"
           style="display:block; text-decoration:none;">
            <div style="background:var(--surface); border:1px solid <?php echo $aktif ? 'var(--accent)' : 'var(--border)'; ?>; border-radius:var(--radius); padding:20px 22px; margin-bottom:12px; transition:.2s;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-family:'Syne',sans-serif; font-weight:700; color:var(--accent);">
                        Sipariş #<?php echo $s['SiparisID']; ?>
                    </span>
                    <span class="tag <?php echo $renk; ?>"><?php echo htmlspecialchars($s['Durum']); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="text-muted" style="font-size:.85rem;">
                        🗓 <?php echo date('d.m.Y H:i', strtotime($s['Tarih'])); ?>
                    </span>
                    <span style="font-weight:700; color:var(--success);">
                        <?php echo number_format($s['ToplamTutar'], 2, ',', '.'); ?> TL
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Sipariş Detayı -->
    <?php if ($seciliID && !empty($detaylar)): ?>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; position:sticky; top:88px;">
        <h3 style="font-size:1rem; margin-bottom:18px; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
            Sipariş #<?php echo $seciliID; ?> Detayı
        </h3>

        <?php foreach ($detaylar as $d): ?>
        <div style="display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border);">
            <div style="width:44px;height:44px;background:var(--surface2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem; flex-shrink:0;">
                <?php echo !empty($d['GorselURL'])
                    ? '<img src="'.htmlspecialchars($d['GorselURL']).'" style="width:44px;height:44px;object-fit:cover;border-radius:8px;">'
                    : '📦'; ?>
            </div>
            <div style="flex:1;">
                <div style="font-weight:600; font-size:.92rem;"><?php echo htmlspecialchars($d['UrunAdi']); ?></div>
                <div class="text-muted" style="font-size:.82rem;">
                    <?php echo $d['Miktar']; ?> adet × <?php echo number_format($d['BirimFiyat'], 2, ',', '.'); ?> TL
                </div>
            </div>
            <div style="font-weight:700; color:var(--success); font-size:.95rem;">
                <?php echo number_format($d['BirimFiyat'] * $d['Miktar'], 2, ',', '.'); ?> TL
            </div>
        </div>
        <?php endforeach; ?>

        <?php
        // Toplam
        $detayToplam = array_sum(array_map(fn($d) => $d['BirimFiyat'] * $d['Miktar'], $detaylar));
        ?>
        <div style="display:flex; justify-content:space-between; padding-top:16px;">
            <span style="font-weight:700;">Toplam</span>
            <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:1.3rem; color:var(--success);">
                <?php echo number_format($detayToplam, 2, ',', '.'); ?> TL
            </span>
        </div>
    </div>
    <?php elseif ($seciliID): ?>
    <div class="alert alert-danger">Sipariş detayı bulunamadı.</div>
    <?php endif; ?>

</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
