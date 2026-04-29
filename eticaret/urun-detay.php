<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo "<div class='alert alert-danger'>Geçersiz ürün ID'si.</div>";
    include 'includes/footer.php';
    exit();
}

$sorgu = $db->prepare("SELECT * FROM Urunler WHERE UrunID = ?");
$sorgu->execute([$id]);
$urun = $sorgu->fetch();

if (!$urun) {
    echo "<div class='alert alert-danger'>Ürün bulunamadı.</div>";
    include 'includes/footer.php';
    exit();
}

$gorsel = !empty($urun['GorselURL']) ? $urun['GorselURL'] : null;
?>

<div style="margin-bottom:16px;">
    <a href="/eticaret/urunler.php" class="text-muted" style="font-size:.88rem;">← Ürünlere Dön</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:start;">

    <!-- Görsel -->
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; min-height:360px; display:flex; align-items:center; justify-content:center; font-size:5rem;">
        <?php if ($gorsel): ?>
            <img src="<?php echo htmlspecialchars($gorsel); ?>"
                 alt="<?php echo htmlspecialchars($urun['UrunAdi']); ?>"
                 style="width:100%; object-fit:cover;">
        <?php else: ?>
            📦
        <?php endif; ?>
    </div>

    <!-- Bilgi -->
    <div>
        <?php if (!empty($urun['Kategori'])): ?>
            <span class="tag tag-blue" style="margin-bottom:12px; display:inline-block;">
                <?php echo htmlspecialchars($urun['Kategori']); ?>
            </span>
        <?php endif; ?>

        <h1 style="font-size:1.8rem; margin-bottom:16px;">
            <?php echo htmlspecialchars($urun['UrunAdi']); ?>
        </h1>

        <?php if (!empty($urun['Aciklama'])): ?>
            <p style="color:var(--muted); margin-bottom:24px; line-height:1.7;">
                <?php echo nl2br(htmlspecialchars($urun['Aciklama'])); ?>
            </p>
        <?php endif; ?>

        <div style="font-family:'Syne',sans-serif; font-size:2.2rem; font-weight:800; color:var(--success); margin-bottom:28px;">
            <?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL
        </div>

        <?php if (isset($urun['Stok']) && $urun['Stok'] <= 0): ?>
            <div class="alert alert-danger">Stokta yok</div>
        <?php else: ?>
            <?php if (isset($urun['Stok'])): ?>
                <p class="text-muted" style="margin-bottom:16px; font-size:.88rem;">
                    Stok: <span class="text-success fw-700"><?php echo $urun['Stok']; ?> adet</span>
                </p>
            <?php endif; ?>
            <a href="/eticaret/sepet_islem.php?islem=ekle&id=<?php echo $urun['UrunID']; ?>"
               class="btn btn-primary btn-lg btn-full">🛒 Sepete Ekle</a>
        <?php endif; ?>

        <a href="/eticaret/urunler.php" class="btn btn-ghost btn-full" style="margin-top:12px;">← Alışverişe Devam Et</a>
    </div>
</div>

@media (max-width: 768px) {
    .detay-grid { grid-template-columns: 1fr !important; }
}

<?php include 'includes/footer.php'; ?>
