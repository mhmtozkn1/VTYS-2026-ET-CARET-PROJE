<?php
// ============================================================
//  Veritabanı Bağlantısı
// ============================================================

$serverName = "DESKTOP-ELEOOQT";
$database   = "EticaretDB";

try {
    // Windows Authentication (şifresiz)
    $db = new PDO("sqlsrv:server=$serverName;Database=$database;TrustServerCertificate=true");

    // SQL Server Auth kullanıyorsan üsttekini sil, bunu aç:
    // $db = new PDO("sqlsrv:server=$serverName;Database=$database", "sa", "SifreniziYazin");

    $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);

} catch (PDOException $e) {
    error_log("DB Bağlantı Hatası: " . $e->getMessage());
    die("<div style='font-family:sans-serif;padding:40px;text-align:center;'>
            <h2>⚠️ Sistem Geçici Olarak Kullanılamıyor</h2>
            <p>Veritabanına bağlanılamadı. Lütfen daha sonra tekrar deneyin.</p>
         </div>");
}

// Beni hatirla: aktif oturum yoksa kalici cookie ile tekrar giris yap.
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['kullanici_id']) && !empty($_COOKIE['remember_token'])) {
    $parcalar = explode(':', $_COOKIE['remember_token'], 2);
    if (count($parcalar) === 2) {
        [$selector, $validator] = $parcalar;
        if ($selector !== '' && $validator !== '') {
            try {
                $q = $db->prepare(
                    "SELECT rt.KullaniciID, rt.ValidatorHash, rt.SonKullanma, k.KullaniciAdi, k.AdSoyad
                     FROM RememberTokens rt
                     JOIN Kullanicilar k ON k.KullaniciID = rt.KullaniciID
                     WHERE rt.Selector = ?"
                );
                $q->execute([$selector]);
                $token = $q->fetch();

                if ($token && strtotime($token['SonKullanma']) > time() && password_verify($validator, $token['ValidatorHash'])) {
                    session_regenerate_id(true);
                    $_SESSION['kullanici_id'] = (int)$token['KullaniciID'];
                    $_SESSION['ad_soyad'] = !empty($token['AdSoyad']) ? $token['AdSoyad'] : $token['KullaniciAdi'];
                    $_SESSION['admin'] = ($token['KullaniciAdi'] === 'admin');
                } else {
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            } catch (PDOException $e) {
                // RememberTokens tablosu yoksa sessizce gec.
            }
        }
    }
}
