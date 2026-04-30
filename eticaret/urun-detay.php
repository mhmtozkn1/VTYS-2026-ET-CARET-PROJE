<?php
session_start();
include 'baglanti.php';
include 'includes/header.php';

$urunID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$urunID) {
    header("Location: /eticaret/urunler.php");
    exit();
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_gonder']) && isset($_SESSION['kullanici_id'])) {
    $puan = filter_input(INPUT_POST, 'puan', FILTER_VALIDATE_INT);
    $yorum = trim($_POST['yorum'] ?? '');

    if (!$puan || $puan < 1 || $puan > 5 || $yorum === '') {
        $mesaj = "<div class='alert alert-danger'>Puan ve yorum alanlarını doğru doldurun.</div>";
    } else {
        try {
            $db->prepare(
                "MERGE UrunYorumlari AS hedef
                 USING (SELECT ? AS UrunID, ? AS KullaniciID) AS kaynak
                 ON (hedef.UrunID = kaynak.UrunID AND hedef.KullaniciID = kaynak.KullaniciID)
                 WHEN MATCHED THEN
                    UPDATE SET Puan = ?, Yorum = ?, Tarih = GETDATE()
                 WHEN NOT MATCHED THEN
                    INSERT (UrunID, KullaniciID, Puan, Yorum) VALUES (?, ?, ?, ?);"
            )->execute([
                $urunID,
                $_SESSION['kullanici_id'],
                $puan,
                $yorum,
                $urunID,
                $_SESSION['kullanici_id'],
                $puan,
                $yorum
            ]);
            $mesaj = "<div class='alert alert-success'>Yorumunuz kaydedildi.</div>";
        } catch (PDOException $e) {
            $mesaj = "<div class='alert alert-danger'>Yorum kaydedilemedi.</div>";
        }
    }
}

try {
    $urunSorgu = $db->prepare(
        "SELECT u.*,
                COALESCE(y.OrtalamaPuan, 0) AS OrtalamaPuan,
                COALESCE(y.YorumSayisi, 0) AS YorumSayisi
         FROM Urunler u
         LEFT JOIN (
             SELECT UrunID, AVG(CAST(Puan AS DECIMAL(10,2))) AS OrtalamaPuan, COUNT(*) AS YorumSayisi
             FROM UrunYorumlari
             GROUP BY UrunID
         ) y ON y.UrunID = u.UrunID
         WHERE u.UrunID = ?"
    );
    $urunSorgu->execute([$urunID]);
    $urun = $urunSorgu->fetch();
} catch (PDOException $e) {
    $urunSorgu = $db->prepare(
        "SELECT u.*, 0 AS OrtalamaPuan, 0 AS YorumSayisi FROM Urunler u WHERE u.UrunID = ?"
    );
    $urunSorgu->execute([$urunID]);
    $urun = $urunSorgu->fetch();
}

if (!$urun) {
    echo "<div class='alert alert-danger'>Ürün bulunamadı.</div>";
    include 'includes/footer.php';
    exit();
}

$yorumlar = [];
try {
    $yorumSorgu = $db->prepare(
        "SELECT y.*, k.AdSoyad, k.KullaniciAdi
         FROM UrunYorumlari y
         JOIN Kullanicilar k ON k.KullaniciID = y.KullaniciID
         WHERE y.UrunID = ?
         ORDER BY y.Tarih DESC"
    );
    $yorumSorgu->execute([$urunID]);
    $yorumlar = $yorumSorgu->fetchAll();
} catch (PDOException $e) {
    $yorumlar = [];
}

$stok = isset($urun['Stok']) ? (int)$urun['Stok'] : null;
$tukendi = ($stok !== null && $stok <= 0);
?>

<div style="margin-bottom:16px;">
    <a href="/eticaret/urunler.php" class="text-muted" style="font-size:.88rem;">← Ürünlere Dön</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1.1fr; gap:28px; align-items:start;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden;">
        <div class="card__img" style="height:420px;">
            <?php if (!empty($urun['GorselURL'])): ?>
                <img src="<?php echo htmlspecialchars($urun['GorselURL']); ?>"
                     alt="<?php echo htmlspecialchars($urun['UrunAdi']); ?>"
                     style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
                📦
            <?php endif; ?>
        </div>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px;">
        <h2 style="margin-bottom:8px;"><?php echo htmlspecialchars($urun['UrunAdi']); ?></h2>
        <div style="margin-bottom:8px; color:var(--muted); font-size:.9rem;">
            ⭐ <?php echo number_format((float)$urun['OrtalamaPuan'], 1, ',', '.'); ?> / 5
            (<?php echo (int)$urun['YorumSayisi']; ?> yorum)
        </div>
        <div style="font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; color:var(--success); margin-bottom:14px;">
            <?php echo number_format($urun['Fiyat'], 2, ',', '.'); ?> TL
        </div>

        <?php if (!empty($urun['Kategori'])): ?>
            <span class="tag tag-purple" style="margin-bottom:12px;"><?php echo htmlspecialchars($urun['Kategori']); ?></span>
        <?php endif; ?>

        <p class="text-muted" style="margin:14px 0 20px;">
            <?php echo nl2br(htmlspecialchars($urun['Aciklama'] ?? 'Bu ürün için açıklama girilmemiş.')); ?>
        </p>

        <?php if ($tukendi): ?>
            <div class="alert alert-danger">Bu ürün stokta tükendi.</div>
            <button type="button" class="btn btn-dark btn-full btn-lg" disabled style="opacity:.6; cursor:not-allowed;">Stokta Yok</button>
        <?php else: ?>
            <div class="text-muted" style="margin-bottom:12px; font-size:.88rem;">Stok: <?php echo $stok ?? 'Sınırsız'; ?></div>
            <a href="/eticaret/sepet_islem.php?islem=ekle&id=<?php echo $urun['UrunID']; ?>" class="btn btn-primary btn-full btn-lg">🛒 Sepete Ekle</a>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top:30px; display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px;">
        <h3 style="font-size:1rem; margin-bottom:14px;">Yorum / Puan Sistemi</h3>
        <?php echo $mesaj; ?>
        <?php if (!isset($_SESSION['kullanici_id'])): ?>
            <div class="alert alert-info">Yorum yapmak için giriş yapmalısın.</div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Puan</label>
                    <select name="puan" class="form-control" required>
                        <option value="">Seçiniz</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> yıldız</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Yorum</label>
                    <textarea name="yorum" rows="4" class="form-control" required></textarea>
                </div>
                <button type="submit" name="yorum_gonder" class="btn btn-success">Yorumu Kaydet</button>
            </form>
        <?php endif; ?>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px;">
        <h3 style="font-size:1rem; margin-bottom:14px;">Kullanıcı Yorumları</h3>
        <?php if (empty($yorumlar)): ?>
            <p class="text-muted">Henüz yorum yapılmamış.</p>
        <?php else: ?>
            <?php foreach ($yorumlar as $y): ?>
                <div style="border-bottom:1px solid var(--border); padding:12px 0;">
                    <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:6px;">
                        <strong><?php echo htmlspecialchars($y['AdSoyad'] ?: $y['KullaniciAdi']); ?></strong>
                        <span class="text-muted" style="font-size:.82rem;"><?php echo date('d.m.Y H:i', strtotime($y['Tarih'])); ?></span>
                    </div>
                    <div style="color:#fbbf24; margin-bottom:6px;"><?php echo str_repeat('★', (int)$y['Puan']) . str_repeat('☆', 5 - (int)$y['Puan']); ?></div>
                    <p style="font-size:.9rem;"><?php echo nl2br(htmlspecialchars($y['Yorum'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>