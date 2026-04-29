<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeknoShop</title>
    <link rel="stylesheet" href="/eticaret/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar__inner">
        <a href="/eticaret/index.php" class="navbar__logo">Tekno<span>Shop</span></a>

        <ul class="navbar__links">
            <li><a href="/eticaret/index.php">Ana Sayfa</a></li>
            <li><a href="/eticaret/urunler.php">Ürünler</a></li>
        </ul>

        <div class="navbar__right">
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <span style="color: var(--muted); font-size:.88rem;">
                    👤 <?php echo htmlspecialchars($_SESSION['ad_soyad']); ?>
                </span>

                <?php
                  $sepetAdet = isset($_SESSION['sepet']) ? array_sum($_SESSION['sepet']) : 0;
                ?>
                <a href="/eticaret/sepet.php" class="btn btn-dark btn-sm cart-badge">
                    🛒 Sepet
                    <?php if ($sepetAdet > 0): ?>
                        <span class="badge"><?php echo $sepetAdet; ?></span>
                    <?php endif; ?>
                </a>

                <a href="/eticaret/siparislerim.php" class="btn btn-ghost btn-sm">📦 Siparişlerim</a>

                <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                    <a href="/eticaret/admin/index.php" class="btn btn-dark btn-sm">⚙️ Panel</a>
                <?php endif; ?>

                <a href="/eticaret/cikis.php" class="btn btn-ghost btn-sm">Çıkış</a>
            <?php else: ?>
                <a href="/eticaret/giris.php" class="btn btn-ghost btn-sm">Giriş Yap</a>
                <a href="/eticaret/kayit.php" class="btn btn-primary btn-sm">Üye Ol</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container" style="padding-top:36px; padding-bottom:40px;">
