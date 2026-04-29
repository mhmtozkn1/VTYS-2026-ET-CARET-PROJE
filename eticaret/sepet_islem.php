<?php
session_start();
include 'baglanti.php';

$islem = $_GET['islem'] ?? '';
$id    = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Sepet yapısı: ['urunID' => miktar]
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

switch ($islem) {
    case 'ekle':
        if ($id) {
            // Ürün gerçekten var mı kontrol et
            $sorgu = $db->prepare("SELECT UrunID FROM Urunler WHERE UrunID = ?");
            $sorgu->execute([$id]);
            if ($sorgu->fetch()) {
                $_SESSION['sepet'][$id] = ($_SESSION['sepet'][$id] ?? 0) + 1;
            }
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/eticaret/urunler.php'));
        exit();

    case 'azalt':
        if ($id && isset($_SESSION['sepet'][$id])) {
            $_SESSION['sepet'][$id]--;
            if ($_SESSION['sepet'][$id] <= 0) {
                unset($_SESSION['sepet'][$id]);
            }
        }
        header("Location: /eticaret/sepet.php");
        exit();

    case 'sil':
        if ($id) {
            unset($_SESSION['sepet'][$id]);
        }
        header("Location: /eticaret/sepet.php");
        exit();

    case 'bosalt':
        $_SESSION['sepet'] = [];
        header("Location: /eticaret/sepet.php");
        exit();
}

header("Location: /eticaret/sepet.php");
exit();
