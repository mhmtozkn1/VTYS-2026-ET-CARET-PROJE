<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: /eticaret/giris.php");
    exit();
}

// Sepet boşsa geri gönder
if (empty($_SESSION['sepet'])) {
    header("Location: /eticaret/sepet.php");
    exit();
}

$kullaniciID = $_SESSION['kullanici_id'];
$sepet       = $_SESSION['sepet']; // [urunID => miktar]
$hata        = "";
$siparisID   = null;

// ── Sipariş oluştur ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['onayla'])) {

    // Ürünleri DB'den çek ve toplam hesapla
    $idler   = array_keys($sepet);
    $soru    = implode(',', array_fill(0, count($idler), '?'));
    $sorgu   = $db->prepare("SELECT * FROM Urunler WHERE UrunID IN ($soru)");
    $sorgu->execute($idler);
    $urunMap = [];
    foreach ($sorgu->fetchAll() as $u) {
        $urunMap[$u['UrunID']] = $u;
    }

    $toplam = 0;
    foreach ($sepet as $uid => $miktar) {
        if (isset($urunMap[$uid])) {
            $toplam += $urunMap[$uid]['Fiyat'] * $miktar;
        }
    }

    try {
        $db->beginTransaction();

        // Ana sipariş kaydı
        $db->prepare(
            "INSERT INTO Siparisler (KullaniciID, ToplamTutar, Durum) VALUES (?, ?, N'Beklemede')"
        )->execute([$kullaniciID, $toplam]);

        $siparisID = $db->lastInsertId();

        // Sipariş detayları
        $detaySorgu = $db->prepare(
            "INSERT INTO SiparisDetay (SiparisID, UrunID, Miktar, BirimFiyat) VALUES (?, ?, ?, ?)"
        );
        foreach ($sepet as $uid => $miktar) {
            if (isset($urunMap[$uid])) {
                $detaySorgu->execute([
                    $siparisID,
                    $uid,
                    $miktar,
                    $urunMap[$uid]['Fiyat']
                ]);
            }
        }

        $db->commit();

        // Sepeti temizle
        $_SESSION['sepet'] = [];

    } catch (PDOException $e) {
        $db->rollBack();
        $hata = "Sipariş oluşturulurken bir hata oluştu: " . $e->getMessage();
        $siparisID = null;
    }
}

// ── Sepet önizlemesi için ürünleri çek (GET - onay sayfası) ─
$kalemler = [];
$toplam   = 0;

if (!empty($_SESSION['sepet'])) {
    $sepetGuncel = $_SESSION['sepet'];
    $idler  = array_keys($sepetGuncel);
    $soru   = implode(',', array_fill(0, count($idler), '?'));
    $sorgu  = $db->prepare("SELECT * FROM Urunler WHERE UrunID IN ($soru)");
    $sorgu->execute($idler);
    $urunMap = [];
    foreach ($sorgu->fetchAll() as $u) {
        $urunMap[$u['UrunID']] = $u;
    }
    foreach ($sepetGuncel as $uid => $miktar) {
        if (isset($urunMap[$uid])) {
            $kalemler[] = ['urun' => $urunMap[$uid], 'miktar' => $miktar];
            $toplam += $urunMap[$uid]['Fiyat'] * $miktar;
        }
    }
}
?>

<?php if ($siparisID): ?>
<!-- ── Başarılı Sipariş ─────────────────────────────────── -->
<div style="max-width:560px; margin:60px auto; text-align:center;">
    <div style="font-size:4rem; margin-bottom:20px;">✅</div>
    <h2 style="font-size:1.8rem; margin-bottom:10px;">Siparişin Alındı!</h2>
    <p class="text-muted" style="margin-bottom:28px;">
        Siparişin başarıyla oluşturuldu. Aşağıda sipariş numaranı görebilirsin.
    </p>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:32px; margin-bottom:28px;">
        <p class="text-muted" style="font-size:.85rem; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">Sipariş Numarası</p>
        <div style="font-family:'Syne',sans-serif; font-size:3rem; font-weight:800; color:var(--accent);">
            #<?php echo $siparisID; ?>
        </div>
    </div>

    <a href="/eticaret/siparislerim.php" class="btn btn-primary btn-lg" style="margin-right:10px;">📦 Siparişlerimi Gör</a>
    <a href="/eticaret/urunler.php" class="btn btn-ghost btn-lg">Alışverişe Devam Et</a>
</div>

<?php elseif ($hata): ?>
<!-- ── Hata ─────────────────────────────────────────────── -->
<div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
<a href="/eticaret/sepet.php" class="btn btn-ghost">← Sepete Dön</a>

<?php else: ?>
<!-- ── Sipariş Onay Sayfası ──────────────────────────────── -->
<div style="max-width:640px; margin:0 auto;">

    <div style="margin-bottom:20px;">
        <a href="/eticaret/sepet.php" class="text-muted" style="font-size:.88rem;">← Sepete Dön</a>
    </div>

    <h2 style="margin-bottom:24px;">📋 Siparişi Onayla</h2>

    <!-- Ürün Özeti -->
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; margin-bottom:20px;">
        <h3 style="font-size:1rem; margin-bottom:18px; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Sipariş Özeti</h3>

        <?php foreach ($kalemler as $k):
            $urun   = $k['urun'];
            $miktar = $k['miktar'];
        ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:44px;height:44px;background:var(--surface2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                    <?php echo !empty($urun['GorselURL'])
                        ? '<img src="'.htmlspecialchars($urun['GorselURL']).'" style="width:44px;height:44px;object-fit:cover;border-radius:8px;">'
                        : '📦'; ?>
                </div>
                <div>
                    <div style="font-weight:600; font-size:.95rem;"><?php echo htmlspecialchars($urun['UrunAdi']); ?></div>
                    <div class="text-muted" style="font-size:.82rem;"><?php echo $miktar; ?> adet × <?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL</div>
                </div>
            </div>
            <div style="font-weight:700; color:var(--success);">
                <?php echo number_format($urun['Fiyat'] * $miktar, 2, ',', '.'); ?> TL
            </div>
        </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px;">
            <span style="font-family:'Syne',sans-serif; font-weight:700; font-size:1.1rem;">Toplam</span>
            <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:1.8rem; color:var(--success);">
                <?php echo number_format($toplam, 2, ',', '.'); ?> TL
            </span>
        </div>
    </div>

    <!-- Kullanıcı Bilgisi -->
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; margin-bottom:24px;">
        <h3 style="font-size:1rem; margin-bottom:14px; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">Hesap Bilgisi</h3>
        <p style="font-weight:600;">👤 <?php echo htmlspecialchars($_SESSION['ad_soyad']); ?></p>
    </div>

    <!-- Onay Butonu -->
    <form method="POST">
        <button type="submit" name="onayla" class="btn btn-success btn-full btn-lg">
            ✅ Siparişi Onayla ve Tamamla
        </button>
    </form>

    <p class="text-muted text-center" style="margin-top:14px; font-size:.82rem;">
        Onayladıktan sonra sipariş numarası verilecek ve sepetiniz temizlenecek.
    </p>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
