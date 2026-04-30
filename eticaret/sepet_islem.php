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
            // Ürün var mı ve stokta mı kontrol et
            $sorgu = $db->prepare("SELECT UrunID, Stok FROM Urunler WHERE UrunID = ?");
            $sorgu->execute([$id]);
            $urun = $sorgu->fetch();
            if ($urun) {
                $stok = isset($urun['Stok']) ? (int)$urun['Stok'] : 999999;
                $mevcut = (int)($_SESSION['sepet'][$id] ?? 0);
                if ($stok > $mevcut) {
                    $_SESSION['sepet'][$id] = $mevcut + 1;
                    unset($_SESSION['sepet_mesaj']);
                } else {
                    $_SESSION['sepet_mesaj'] = 'Bu ürün stokta tükendi veya maksimum stok adedine ulaşıldı.';
                }
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
