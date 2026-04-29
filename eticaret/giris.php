<?php
session_start();

// Zaten giriş yapmışsa ana sayfaya gönder
if (isset($_SESSION['kullanici_id'])) {
    header("Location: /eticaret/index.php");
    exit();
}

include 'baglanti.php';
include 'includes/header.php';

$hata = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eposta = trim($_POST['eposta'] ?? '');
    $sifre  = $_POST['sifre'] ?? '';

    if (empty($eposta) || empty($sifre)) {
        $hata = "E-posta ve şifre alanları boş bırakılamaz.";
    } else {
        // Şifreyi hash ile karşılaştır (eski plain-text şifreler için de çalışır)
        $sorgu = $db->prepare("SELECT * FROM Kullanicilar WHERE Eposta = ?");
        $sorgu->execute([$eposta]);
        $kullanici = $sorgu->fetch();

        $sifreGecerli = false;
        if ($kullanici) {
            // Hash'li şifre mi yoksa düz mü?
            if (password_verify($sifre, $kullanici['Sifre'])) {
                $sifreGecerli = true;
            } elseif ($kullanici['Sifre'] === $sifre) {
                // Eski düz metin şifre — otomatik hash'le ve kaydet
                $yeniHash = password_hash($sifre, PASSWORD_BCRYPT);
                $db->prepare("UPDATE Kullanicilar SET Sifre = ? WHERE KullaniciID = ?")
                   ->execute([$yeniHash, $kullanici['KullaniciID']]);
                $sifreGecerli = true;
            }
        }

        if ($sifreGecerli) {
            session_regenerate_id(true); // Session fixation koruması
            $_SESSION['kullanici_id'] = $kullanici['KullaniciID'];
            $_SESSION['ad_soyad']     = !empty($kullanici['AdSoyad'])
                                          ? $kullanici['AdSoyad']
                                          : $kullanici['KullaniciAdi'];
            // Admin kontrolü (KullaniciAdi == "admin" ya da ayrı kolon varsa)
            $_SESSION['admin'] = ($kullanici['KullaniciAdi'] === 'admin');

            header("Location: /eticaret/index.php");
            exit();
        } else {
            $hata = "E-posta veya şifre hatalı.";
        }
    }
}
?>

<div style="display:flex; justify-content:center; align-items:center; min-height:60vh;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:40px; width:100%; max-width:420px; box-shadow:var(--shadow);">

        <h2 style="text-align:center; margin-bottom:8px;">Tekrar Hoş Geldin</h2>
        <p class="text-muted text-center" style="margin-bottom:28px; font-size:.9rem;">Hesabına giriş yap</p>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['durum']) && $_GET['durum'] === 'basarili'): ?>
            <div class="alert alert-success">✅ Kayıt başarılı! Şimdi giriş yapabilirsin.</div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label">E-Posta</label>
                <input type="email" name="eposta" class="form-control"
                       placeholder="ornek@mail.com"
                       value="<?php echo htmlspecialchars($_POST['eposta'] ?? ''); ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Şifre</label>
                <input type="password" name="sifre" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Giriş Yap</button>
        </form>

        <p class="text-center text-muted" style="margin-top:20px; font-size:.88rem;">
            Hesabın yok mu?
            <a href="/eticaret/kayit.php" style="color:var(--accent); font-weight:600;">Üye Ol</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
