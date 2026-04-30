<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

if (!isset($_SESSION['kullanici_id']) || empty($_SESSION['admin'])) { header("Location: /eticaret/giris.php"); exit(); }

$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ad       = trim($_POST['urun_adi'] ?? '');
    $fiyat    = $_POST['fiyat']     ?? '';
    $kategori = trim($_POST['kategori']  ?? '');
    $gorsel   = trim($_POST['gorsel_url'] ?? '');
    $stok     = (int)($_POST['stok']   ?? 0);
    $aciklama = trim($_POST['aciklama'] ?? '');

    if (empty($ad) || empty($fiyat)) {
        $mesaj = "<div class='alert alert-danger'>Ürün adı ve fiyat zorunludur.</div>";
    } else {
        try {
            // Tabloda GorselURL, Kategori, Stok, Aciklama sütunları varsa kullan
            $sorgu = $db->prepare(
                "INSERT INTO Urunler (UrunAdi, Fiyat, Kategori, GorselURL, Stok, Aciklama)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $sorgu->execute([$ad, $fiyat, $kategori, $gorsel, $stok, $aciklama]);
            $mesaj = "<div class='alert alert-success'>✅ Ürün başarıyla eklendi!</div>";
        } catch (PDOException $e) {
            // Eski şema (sadece UrunAdi + Fiyat)
            try {
                $sorgu = $db->prepare("INSERT INTO Urunler (UrunAdi, Fiyat) VALUES (?, ?)");
                $sorgu->execute([$ad, $fiyat]);
                $mesaj = "<div class='alert alert-success'>✅ Ürün eklendi (sadece ad/fiyat — ek sütunlar bulunamadı).</div>";
            } catch (PDOException $e2) {
                $mesaj = "<div class='alert alert-danger'>❌ Hata: " . htmlspecialchars($e2->getMessage()) . "</div>";
            }
        }
    }
}
?>

<div style="max-width:600px; margin:0 auto;">
    <div style="margin-bottom:16px;">
        <a href="/eticaret/admin/index.php" class="text-muted" style="font-size:.88rem;">← Panele Dön</a>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:36px;">
        <h2 style="margin-bottom:4px;">➕ Yeni Ürün Ekle</h2>
        <p class="text-muted" style="font-size:.88rem; margin-bottom:28px;">Mağazana yeni ürün ekle</p>

        <?php echo $mesaj; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label">Ürün Adı *</label>
                <input type="text" name="urun_adi" class="form-control"
                       placeholder="Örn: Logitech MX Master 3" required autofocus>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Fiyat (TL) *</label>
                    <input type="number" step="0.01" min="0" name="fiyat" class="form-control"
                           placeholder="1299.90" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok Adedi</label>
                    <input type="number" min="0" name="stok" class="form-control"
                           placeholder="50" value="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Kategori</label>
                <input type="text" name="kategori" class="form-control"
                       placeholder="Örn: Mouse, Laptop, Klavye…">
            </div>

            <div class="form-group">
                <label class="form-label">Görsel URL</label>
                <input type="url" name="gorsel_url" class="form-control"
                       placeholder="https://example.com/gorsel.jpg">
            </div>

            <div class="form-group">
                <label class="form-label">Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="4"
                          placeholder="Ürün hakkında kısa bir açıklama…"
                          style="resize:vertical;"></textarea>
            </div>

            <button type="submit" class="btn btn-success btn-full btn-lg">Ürünü Kaydet</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
