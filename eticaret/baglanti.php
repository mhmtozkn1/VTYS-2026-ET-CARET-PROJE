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
