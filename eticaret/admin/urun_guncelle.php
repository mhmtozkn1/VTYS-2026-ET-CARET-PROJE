<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

if (!isset($_SESSION['kullanici_id'])) { header("Location: /giris.php"); exit(); }

$mesaj = "";
$urun  = null;

// Güncelleme gönderildiyse
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $ad       = trim($_POST['urun_adi']   ?? '');
    $fiyat    = $_POST['fiyat']           ?? '';
    $kategori = trim($_POST['kategori']   ?? '');
    $gorsel   = trim($_POST['gorsel_url'] ?? '');
    $stok     = (int)($_POST['stok']      ?? 0);
    $aciklama = trim($_POST['aciklama']   ?? '');

    if (!$id || empty($ad) || empty($fiyat)) {
        $mesaj = "<div class='alert alert-danger'>Gerekli alanlar eksik.</div>";
    } else {
        try {
            $guncelle = $db->prepare(
                "UPDATE Urunler SET UrunAdi=?, Fiyat=?, Kategori=?, GorselURL=?, Stok=?, Aciklama=?
                 WHERE UrunID=?"
            );
            $guncelle->execute([$ad, $fiyat, $kategori, $gorsel, $stok, $aciklama, $id]);
        } catch (PDOException $e) {
            // Eski şema
            $guncelle = $db->prepare("UPDATE Urunler SET UrunAdi=?, Fiyat=? WHERE UrunID=?");
            $guncelle->execute([$ad, $fiyat, $id]);
        }
        header("Location: /eticaret/admin/urun_listesi.php?durum=guncellendi");
        exit();
    }
}

// Ürünü yükle
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
   ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $sorgu = $db->prepare("SELECT * FROM Urunler WHERE UrunID = ?");
    $sorgu->execute([$id]);
    $urun = $sorgu->fetch();
}

if (!$urun) {
    echo "<div class='alert alert-danger'>Ürün bulunamadı.</div>";
    include '../includes/footer.php';
    exit();
}
?>

<div style="max-width:600px; margin:0 auto;">
    <div style="margin-bottom:16px;">
        <a href="/eticaret/admin/urun_listesi.php" class="text-muted" style="font-size:.88rem;">← Ürün Listesine Dön</a>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:36px;">
        <h2 style="margin-bottom:4px;">✏️ Ürün Düzenle</h2>
        <p class="text-muted" style="font-size:.88rem; margin-bottom:28px;">#<?php echo $urun['UrunID']; ?></p>

        <?php echo $mesaj; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="id" value="<?php echo $urun['UrunID']; ?>">

            <div class="form-group">
                <label class="form-label">Ürün Adı *</label>
                <input type="text" name="urun_adi" class="form-control"
                       value="<?php echo htmlspecialchars($urun['UrunAdi']); ?>" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Fiyat (TL) *</label>
                    <input type="number" step="0.01" min="0" name="fiyat" class="form-control"
                           value="<?php echo $urun['Fiyat']; ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok</label>
                    <input type="number" min="0" name="stok" class="form-control"
                           value="<?php echo $urun['Stok'] ?? 0; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Kategori</label>
                <input type="text" name="kategori" class="form-control"
                       value="<?php echo htmlspecialchars($urun['Kategori'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Görsel URL</label>
                <input type="url" name="gorsel_url" class="form-control"
                       value="<?php echo htmlspecialchars($urun['GorselURL'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="4"
                          style="resize:vertical;"><?php echo htmlspecialchars($urun['Aciklama'] ?? ''); ?></textarea>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary btn-full btn-lg">💾 Kaydet</button>
                <a href="/eticaret/admin/urun_listesi.php" class="btn btn-ghost btn-lg">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
