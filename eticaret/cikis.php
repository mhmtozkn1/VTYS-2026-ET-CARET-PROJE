<?php
session_start();
include 'baglanti.php';

if (!empty($_COOKIE['remember_token'])) {
    $parcalar = explode(':', $_COOKIE['remember_token'], 2);
    if (count($parcalar) === 2) {
        try {
            $db->prepare("DELETE FROM RememberTokens WHERE Selector = ?")->execute([$parcalar[0]]);
        } catch (PDOException $e) {
            // Tablo yoksa logout devam etsin.
        }
    }
}

session_unset();
session_destroy();

// Oturum cookie'sini temizle
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]
    );
}

setcookie('remember_token', '', time() - 3600, '/');

header("Location: /eticaret/index.php");
exit();
