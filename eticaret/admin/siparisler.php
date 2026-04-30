<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['admin'])) { header("Location: /eticaret/giris.php"); exit(); }

// Durum güncelle
if (isset($_POST['durum_guncelle'])) {
    $sid   = filter_input(INPUT_POST, 'siparis_id', FILTER_VALIDATE_INT);
    $durum = $_POST['durum'] ?? '';
    $izinliDurumlar = ['Beklemede','Onaylandı','Kargoda','Teslim Edildi','İptal'];
    if ($sid && in_array($durum, $izinliDurumlar)) {
        $db->prepare("UPDATE Siparisler SET Durum = ? WHERE SiparisID = ?")
           ->execute([$durum, $sid]);
    }
    header("Location: siparisler.php?guncellendi=1");
    exit();
}

// Tüm siparişleri çek
$siparisler = $db->query(
    "SELECT s.*, k.AdSoyad, k.Eposta
     FROM Siparisler s
     JOIN Kullanicilar k ON s.KullaniciID = k.KullaniciID
     ORDER BY s.Tarih DESC"
)->fetchAll();

// Detay
$detaylar = [];
$seciliID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($seciliID) {
    $q = $db->prepare(
        "SELECT sd.*, u.UrunAdi FROM SiparisDetay sd
         JOIN Urunler u ON sd.UrunID = u.UrunID
         WHERE sd.SiparisID = ?"
    );
    $q->execute([$seciliID]);
    $detaylar = $q->fetchAll();
}

$durumRenk = [
    'Beklemede'      => 'tag-yellow',
    'Onaylandı'      => 'tag-blue',
    'Kargoda'        => 'tag-purple',
    'Teslim Edildi'  => 'tag-green',
    'İptal'          => 'tag-red',
];
?>

<div class="section-header" style="margin-bottom:24px;">
    <div>
        <h2>🧾 Siparişler</h2>
        <p class="text-muted" style="font-size:.88rem; margin-top:4px;"><?php echo count($siparisler); ?> sipariş</p>
    </div>
    <a href="/eticaret/admin/index.php" class="btn btn-ghost btn-sm">← Panel</a>
</div>

<?php if (isset($_GET['guncellendi'])): ?>
    <div class="alert alert-success">✅ Sipariş durumu güncellendi.</div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:<?php echo $seciliID ? '1fr 1.1fr' : '1fr'; ?>; gap:24px; align-items:start;">

    <!-- Liste -->
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius);">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Sipariş</th>
                        <th>Müşteri</th>
                        <th>Tutar</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siparisler)): ?>
                    <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:40px;">Henüz sipariş yok.</td></tr>
                    <?php else: ?>
                    <?php foreach ($siparisler as $s):
                        $renk  = $durumRenk[$s['Durum']] ?? 'tag-blue';
                        $aktif = ($seciliID == $s['SiparisID']);
                    ?>
                    <tr style="<?php echo $aktif ? 'background:rgba(79,142,247,.06);' : ''; ?>">
                        <td><span class="tag tag-blue">#<?php echo $s['SiparisID']; ?></span></td>
                        <td>
                            <div style="font-weight:600; font-size:.9rem;"><?php echo htmlspecialchars($s['AdSoyad']); ?></div>
                            <div class="text-muted" style="font-size:.78rem;"><?php echo htmlspecialchars($s['Eposta']); ?></div>
                        </td>
                        <td class="text-success fw-700"><?php echo number_format($s['ToplamTutar'], 2, ',', '.'); ?> TL</td>
                        <td class="text-muted" style="font-size:.82rem;"><?php echo date('d.m.Y H:i', strtotime($s['Tarih'])); ?></td>
                        <td><span class="tag <?php echo $renk; ?>"><?php echo htmlspecialchars($s['Durum']); ?></span></td>
                        <td>
                            <a href="siparisler.php?id=<?php echo $s['SiparisID']; ?>"
                               class="btn btn-dark btn-sm">Detay</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detay Paneli -->
    <?php if ($seciliID): ?>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; position:sticky; top:88px;">
        <h3 style="font-size:1rem; margin-bottom:18px; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
            Sipariş #<?php echo $seciliID; ?>
        </h3>

        <?php foreach ($detaylar as $d): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); font-size:.9rem;">
            <span style="font-weight:500;"><?php echo htmlspecialchars($d['UrunAdi']); ?></span>
            <span class="text-muted"><?php echo $d['Miktar']; ?> × <?php echo number_format($d['BirimFiyat'], 2, ',', '.'); ?> TL</span>
            <span class="text-success fw-700"><?php echo number_format($d['BirimFiyat'] * $d['Miktar'], 2, ',', '.'); ?> TL</span>
        </div>
        <?php endforeach; ?>

        <!-- Durum Güncelle -->
        <form method="POST" style="margin-top:20px;">
            <input type="hidden" name="siparis_id" value="<?php echo $seciliID; ?>">
            <div class="form-group">
                <label class="form-label">Durumu Güncelle</label>
                <select name="durum" class="form-control">
                    <?php
                    // Mevcut siparişin durumunu bul
                    $mevcutDurum = '';
                    foreach ($siparisler as $s) {
                        if ($s['SiparisID'] == $seciliID) { $mevcutDurum = $s['Durum']; break; }
                    }
                    foreach (['Beklemede','Onaylandı','Kargoda','Teslim Edildi','İptal'] as $d):
                    ?>
                    <option value="<?php echo $d; ?>" <?php echo $mevcutDurum === $d ? 'selected' : ''; ?>>
                        <?php echo $d; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="durum_guncelle" class="btn btn-primary btn-full">💾 Durumu Kaydet</button>
        </form>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
