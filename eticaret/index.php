<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <h1>Teknoloji Dünyasına<br><span>Hoş Geldiniz</span></h1>
    <p>En yeni laptoplar, mouse ve klavyeler bir tık uzağınızda. Binlerce ürün, hızlı teslimat.</p>
    <a href="/eticaret/urunler.php" class="btn btn-primary btn-lg">Alışverişe Başla →</a>
</section>

<!-- Öne Çıkan Ürünler -->
<section>
    <div class="section-header">
        <h2>⚡ Öne Çıkan Ürünler</h2>
        <a href="/eticaret/urunler.php" class="btn btn-ghost btn-sm">Tümünü Gör →</a>
    </div>

    <div class="grid grid-auto">
        <?php
        $sorgu = $db->query("SELECT TOP 4 * FROM Urunler ORDER BY UrunID DESC");
        while ($urun = $sorgu->fetch()):
            $gorsel = !empty($urun['GorselURL']) ? $urun['GorselURL'] : null;
        ?>
        <div class="card">
            <div class="card__img">
                <?php if ($gorsel): ?>
                    <img src="<?php echo htmlspecialchars($gorsel); ?>"
                         alt="<?php echo htmlspecialchars($urun['UrunAdi']); ?>"
                         style="width:100%;height:200px;object-fit:cover;">
                <?php else: ?>
                    📦
                <?php endif; ?>
            </div>
            <div class="card__body">
                <div class="card__title"><?php echo htmlspecialchars($urun['UrunAdi']); ?></div>
                <div class="card__price"><?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL</div>
                <a href="/eticaret/sepet_islem.php?islem=ekle&id=<?php echo $urun['UrunID']; ?>"
                   class="btn btn-primary btn-full">🛒 Sepete Ekle</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Neden Biz? -->
<section style="margin-top:64px;">
    <div class="grid grid-3" style="gap:20px;">
        <?php
        $ozellikler = [
            ['🚀','Hızlı Teslimat','Siparişleriniz en kısa sürede kapınıza ulaşır.'],
            ['🔒','Güvenli Ödeme','Tüm işlemler SSL ile şifrelidir.'],
            ['🔄','Kolay İade','30 gün içinde ücretsiz iade imkânı.'],
        ];
        foreach ($ozellikler as $o):
        ?>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:28px; text-align:center;">
            <div style="font-size:2rem; margin-bottom:14px;"><?php echo $o[0]; ?></div>
            <h3 style="font-size:1rem; margin-bottom:8px;"><?php echo $o[1]; ?></h3>
            <p style="color:var(--muted); font-size:.88rem;"><?php echo $o[2]; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
