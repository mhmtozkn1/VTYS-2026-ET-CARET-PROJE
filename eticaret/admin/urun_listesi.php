<?php
session_start();
include '../baglanti.php';
include '../includes/header.php';

if (!isset($_SESSION['kullanici_id'])) { header("Location: /giris.php"); exit(); }

// Silme işlemi
if (isset($_GET['sil'])) {
    $silId = filter_input(INPUT_GET, 'sil', FILTER_VALIDATE_INT);
    if ($silId) {
        $db->prepare("DELETE FROM Urunler WHERE UrunID = ?")->execute([$silId]);
    }
    header("Location: /eticaret/admin/urun_listesi.php?durum=silindi");
    exit();
}

$urunler = $db->query("SELECT * FROM Urunler ORDER BY UrunID DESC")->fetchAll();
?>

<!-- Başlık -->
<div class="section-header" style="margin-bottom:24px;">
    <div>
        <h2>📝 Ürün Listesi</h2>
        <p class="text-muted" style="font-size:.88rem; margin-top:4px;"><?php echo count($urunler); ?> ürün</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="/eticaret/admin/urun_ekle.php" class="btn btn-primary btn-sm">➕ Yeni Ürün</a>
        <a href="/eticaret/admin/index.php"     class="btn btn-ghost btn-sm">← Panel</a>
    </div>
</div>

<?php if (isset($_GET['durum'])): ?>
    <?php if ($_GET['durum'] === 'silindi'): ?>
        <div class="alert alert-danger">🗑️ Ürün silindi.</div>
    <?php elseif ($_GET['durum'] === 'guncellendi'): ?>
        <div class="alert alert-success">✅ Ürün güncellendi.</div>
    <?php endif; ?>
<?php endif; ?>

<div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:4px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ürün Adı</th>
                    <th>Kategori</th>
                    <th>Fiyat</th>
                    <th>Stok</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($urunler)): ?>
                <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:40px;">Henüz ürün eklenmemiş.</td></tr>
                <?php else: ?>
                <?php foreach ($urunler as $u): ?>
                <tr>
                    <td><span class="tag tag-blue">#<?php echo $u['UrunID']; ?></span></td>
                    <td style="font-weight:500;"><?php echo htmlspecialchars($u['UrunAdi']); ?></td>
                    <td>
                        <?php if (!empty($u['Kategori'])): ?>
                            <span class="tag tag-purple"><?php echo htmlspecialchars($u['Kategori']); ?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-success fw-700"><?php echo number_format($u['Fiyat'], 2, ',', '.'); ?> TL</td>
                    <td>
                        <?php
                        $stok = $u['Stok'] ?? null;
                        if ($stok !== null):
                            $cls = $stok > 10 ? 'tag-green' : ($stok > 0 ? 'tag-yellow' : 'tag-red');
                        ?>
                            <span class="tag <?php echo $cls; ?>"><?php echo $stok; ?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="/eticaret/admin/urun_guncelle.php?id=<?php echo $u['UrunID']; ?>"
                               class="btn btn-dark btn-sm">✏️ Düzenle</a>
                            <a href="?sil=<?php echo $u['UrunID']; ?>"
                               class="btn btn-ghost btn-sm" style="color:var(--danger);"
                               onclick="return confirm('<?php echo htmlspecialchars($u['UrunAdi']); ?> silinsin mi?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
