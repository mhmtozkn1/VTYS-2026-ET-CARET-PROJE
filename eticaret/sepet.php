<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

$sepet   = $_SESSION['sepet'] ?? [];
$toplam  = 0;
$kalemler = [];

// Sepet boş değilse ürünleri tek sorguda çek
if (!empty($sepet)) {
    $idler  = array_keys($sepet);
    $soru   = implode(',', array_fill(0, count($idler), '?'));
    $sorgu  = $db->prepare("SELECT * FROM Urunler WHERE UrunID IN ($soru)");
    $sorgu->execute($idler);
    $urunMap = [];
    foreach ($sorgu->fetchAll() as $u) {
        $urunMap[$u['UrunID']] = $u;
    }
    foreach ($sepet as $uid => $miktar) {
        if (isset($urunMap[$uid])) {
            $kalemler[] = ['urun' => $urunMap[$uid], 'miktar' => $miktar];
            $toplam += $urunMap[$uid]['Fiyat'] * $miktar;
        }
    }
}
?>

<h2 style="margin-bottom:28px;">🛒 Alışveriş Sepetim</h2>

<?php if (empty($kalemler)): ?>
    <div style="text-align:center; padding:80px 0; background:var(--surface); border:1px solid var(--border); border-radius:16px;">
        <div style="font-size:3.5rem; margin-bottom:16px;">🛒</div>
        <h3 style="color:var(--muted); font-weight:500;">Sepetiniz şu an boş.</h3>
        <p class="text-muted" style="margin-top:8px; font-size:.9rem;">Ürünlere göz atıp ekleyebilirsiniz.</p>
        <a href="/eticaret/urunler.php" class="btn btn-primary" style="margin-top:24px;">Ürünlere Git</a>
    </div>
<?php else: ?>

    <div style="display:grid; grid-template-columns:1fr 320px; gap:28px; align-items:start;">

        <!-- Ürün Listesi -->
        <div>
            <?php foreach ($kalemler as $kalem):
                $urun   = $kalem['urun'];
                $miktar = $kalem['miktar'];
                $gorsel = $urun['GorselURL'] ?? null;
            ?>
            <div class="sepet-satir">
                <div class="sepet-satir__img">
                    <?php if ($gorsel): ?>
                        <img src="<?php echo htmlspecialchars($gorsel); ?>"
                             style="width:72px;height:72px;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>

                <div class="sepet-satir__info">
                    <div class="sepet-satir__ad"><?php echo htmlspecialchars($urun['UrunAdi']); ?></div>
                    <div class="sepet-satir__fiyat"><?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL / adet</div>
                </div>

                <!-- Miktar Kontrolü -->
                <div class="qty-control">
                    <a href="/eticaret/sepet_islem.php?islem=azalt&id=<?php echo $urun['UrunID']; ?>"
                       class="qty-btn">−</a>
                    <span class="qty-val"><?php echo $miktar; ?></span>
                    <a href="/eticaret/sepet_islem.php?islem=ekle&id=<?php echo $urun['UrunID']; ?>"
                       class="qty-btn">+</a>
                </div>

                <!-- Ara Toplam -->
                <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:1rem; min-width:90px; text-align:right;">
                    <?php echo number_format($urun['Fiyat'] * $miktar, 2, ',', '.'); ?> TL
                </div>

                <a href="/eticaret/sepet_islem.php?islem=sil&id=<?php echo $urun['UrunID']; ?>"
                   class="btn btn-ghost btn-sm"
                   title="Kaldır"
                   style="color:var(--danger); border-color:transparent; padding:6px 10px;">🗑️</a>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:center;">
                <a href="/eticaret/urunler.php" class="btn btn-ghost btn-sm">← Alışverişe Devam Et</a>
                <a href="/eticaret/sepet_islem.php?islem=bosalt"
                   class="btn btn-ghost btn-sm"
                   style="color:var(--danger); border-color:var(--danger);"
                   onclick="return confirm('Sepeti temizlemek istiyor musun?')">
                    Sepeti Boşalt
                </a>
            </div>
        </div>

        <!-- Özet -->
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; position:sticky; top:88px;">
            <h3 style="margin-bottom:20px; font-size:1.1rem;">Sipariş Özeti</h3>

            <?php foreach ($kalemler as $k): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-size:.88rem; color:var(--muted);">
                <span><?php echo htmlspecialchars($k['urun']['UrunAdi']); ?> × <?php echo $k['miktar']; ?></span>
                <span><?php echo number_format($k['urun']['Fiyat'] * $k['miktar'], 2, ',', '.'); ?> TL</span>
            </div>
            <?php endforeach; ?>

            <hr style="border:none; border-top:1px solid var(--border); margin:16px 0;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <span style="font-family:'Syne',sans-serif; font-weight:700; font-size:1.1rem;">Toplam</span>
                <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:1.6rem; color:var(--success);">
                    <?php echo number_format($toplam, 2, ',', '.'); ?> TL
                </span>
            </div>

            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <a href="/eticaret/siparis.php" class="btn btn-success btn-full btn-lg">💳 Siparişi Tamamla</a>
            <?php else: ?>
                <a href="/eticaret/giris.php" class="btn btn-primary btn-full btn-lg">Giriş Yap &amp; Devam Et</a>
                <p class="text-muted text-center" style="margin-top:12px; font-size:.82rem;">
                    Sipariş vermek için giriş yapman gerekiyor.
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
