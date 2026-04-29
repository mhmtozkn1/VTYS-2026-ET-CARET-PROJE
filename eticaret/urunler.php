<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

// Arama & sıralama
$arama    = trim($_GET['ara']    ?? '');
$siralama = $_GET['siralama']    ?? 'yeni';

// SQL oluştur
$where  = '';
$params = [];

if ($arama !== '') {
    $where    = "WHERE UrunAdi LIKE ?";
    $params[] = '%' . $arama . '%';
}

$orderBy = match($siralama) {
    'ucuz'   => "Fiyat ASC",
    'pahali' => "Fiyat DESC",
    default  => "UrunID DESC",
};

$sql    = "SELECT * FROM Urunler $where ORDER BY $orderBy";
$sorgu  = $db->prepare($sql);
$sorgu->execute($params);
$urunler = $sorgu->fetchAll();

$toplamAdet = count($urunler);
?>

<!-- Başlık + Arama -->
<div class="section-header mb-24">
    <div>
        <h2>Tüm Ürünler</h2>
        <p class="text-muted" style="font-size:.88rem; margin-top:4px;"><?php echo $toplamAdet; ?> ürün listeleniyor</p>
    </div>
</div>

<div style="display:flex; gap:12px; margin-bottom:28px; flex-wrap:wrap;">
    <form method="GET" action="/eticaret/urunler.php" class="search-bar" style="flex:1; margin-bottom:0; min-width:200px;">
        <input type="text" name="ara" class="form-control"
               placeholder="Ürün ara…"
               value="<?php echo htmlspecialchars($arama); ?>">
        <input type="hidden" name="siralama" value="<?php echo htmlspecialchars($siralama); ?>">
        <button type="submit" class="btn btn-primary">🔍</button>
        <?php if ($arama): ?>
            <a href="/eticaret/urunler.php" class="btn btn-ghost">✕</a>
        <?php endif; ?>
    </form>

    <form method="GET" action="/eticaret/urunler.php">
        <input type="hidden" name="ara" value="<?php echo htmlspecialchars($arama); ?>">
        <select name="siralama" class="form-control" onchange="this.form.submit()" style="width:auto;">
            <option value="yeni"   <?php if($siralama==='yeni')   echo 'selected'; ?>>En Yeni</option>
            <option value="ucuz"   <?php if($siralama==='ucuz')   echo 'selected'; ?>>Önce Ucuz</option>
            <option value="pahali" <?php if($siralama==='pahali') echo 'selected'; ?>>Önce Pahalı</option>
        </select>
    </form>
</div>

<?php if (empty($urunler)): ?>
    <div style="text-align:center; padding:80px 0;">
        <div style="font-size:3rem; margin-bottom:16px;">🔍</div>
        <h3 style="color:var(--muted);">Ürün bulunamadı</h3>
        <p class="text-muted" style="margin-top:8px;">Farklı bir arama terimi deneyin.</p>
        <a href="/eticaret/urunler.php" class="btn btn-primary" style="margin-top:20px;">Tümünü Göster</a>
    </div>
<?php else: ?>
    <div class="grid grid-auto">
        <?php foreach ($urunler as $urun):
            $gorsel = !empty($urun['GorselURL']) ? $urun['GorselURL'] : null;
        ?>
        <div class="card">
            <a href="/eticaret/urun-detay.php?id=<?php echo $urun['UrunID']; ?>" style="display:block;">
                <div class="card__img">
                    <?php if ($gorsel): ?>
                        <img src="<?php echo htmlspecialchars($gorsel); ?>"
                             alt="<?php echo htmlspecialchars($urun['UrunAdi']); ?>"
                             style="width:100%;height:200px;object-fit:cover;">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>
            </a>
            <div class="card__body">
                <div class="card__title"><?php echo htmlspecialchars($urun['UrunAdi']); ?></div>
                <?php if (!empty($urun['Kategori'])): ?>
                    <span class="tag tag-blue" style="margin-bottom:10px; display:inline-block;">
                        <?php echo htmlspecialchars($urun['Kategori']); ?>
                    </span>
                <?php endif; ?>
                <div class="card__price"><?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL</div>
                <a href="/eticaret/sepet_islem.php?islem=ekle&id=<?php echo $urun['UrunID']; ?>"
                   class="btn btn-primary btn-full">🛒 Sepete Ekle</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
