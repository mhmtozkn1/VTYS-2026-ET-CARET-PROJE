<?php
session_start();

if (isset($_SESSION['kullanici_id'])) {
    header("Location: /index.php");
    exit();
}

include 'baglanti.php';
include 'includes/header.php';

$hata = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $adsoyad = trim($_POST['adsoyad'] ?? '');
    $eposta  = trim($_POST['eposta']  ?? '');
    $sifre   = $_POST['sifre']  ?? '';
    $sifre2  = $_POST['sifre2'] ?? '';

    // Doğrulamalar
    if (empty($adsoyad) || empty($eposta) || empty($sifre)) {
        $hata = "Tüm alanlar zorunludur.";
    } elseif (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
        $hata = "Geçerli bir e-posta adresi girin.";
    } elseif (strlen($sifre) < 6) {
        $hata = "Şifre en az 6 karakter olmalıdır.";
    } elseif ($sifre !== $sifre2) {
        $hata = "Şifreler eşleşmiyor.";
    } else {
        // E-posta daha önce alınmış mı?
        $kontrol = $db->prepare("SELECT KullaniciID FROM Kullanicilar WHERE Eposta = ?");
        $kontrol->execute([$eposta]);
        if ($kontrol->fetch()) {
            $hata = "Bu e-posta adresi zaten kayıtlı.";
        } else {
            $hashSifre = password_hash($sifre, PASSWORD_BCRYPT);
            $sorgu = $db->prepare("INSERT INTO Kullanicilar (KullaniciAdi, Sifre, Eposta, AdSoyad) VALUES (?, ?, ?, ?)");
            $sorgu->execute([$adsoyad, $hashSifre, $eposta, $adsoyad]);

            header("Location: /eticaret/giris.php?durum=basarili");
            exit();
        }
    }
}
?>

<div style="display:flex; justify-content:center; align-items:center; min-height:60vh;">
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:40px; width:100%; max-width:440px; box-shadow:var(--shadow);">

        <h2 style="text-align:center; margin-bottom:8px;">Hesap Oluştur</h2>
        <p class="text-muted text-center" style="margin-bottom:28px; font-size:.9rem;">Ücretsiz kayıt ol, alışverişe başla</p>

        <?php if ($hata): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label">Adınız Soyadınız</label>
                <input type="text" name="adsoyad" class="form-control"
                       placeholder="Atakan Yılmaz"
                       value="<?php echo htmlspecialchars($_POST['adsoyad'] ?? ''); ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">E-Posta</label>
                <input type="email" name="eposta" class="form-control"
                       placeholder="ornek@mail.com"
                       value="<?php echo htmlspecialchars($_POST['eposta'] ?? ''); ?>"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Şifre <span class="text-muted" style="font-size:.78rem; text-transform:none;">(min. 6 karakter)</span></label>
                <input type="password" name="sifre" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label class="form-label">Şifre Tekrar</label>
                <input type="password" name="sifre2" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Ücretsiz Kayıt Ol</button>
        </form>

        <p class="text-center text-muted" style="margin-top:20px; font-size:.88rem;">
            Hesabın var mı?
            <a href="/eticaret/giris.php" style="color:var(--accent); font-weight:600;">Giriş Yap</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
