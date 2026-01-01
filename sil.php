<?php
session_start();
require 'db.php';

if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }
if (!isset($_GET['id']) || !isset($_GET['tur'])) { die("Geçersiz istek."); }

$id = $_GET['id'];
$tur = $_GET['tur'];

// 1. FİŞ SİLME (Komple fişi ve içindeki ürünleri siler)
if ($tur == 'fis') {
    // Önce resim dosyasını bul ve sil
    $stmt = $pdo->prepare("SELECT dosya_yolu FROM fisler WHERE id = ?");
    $stmt->execute([$id]);
    $dosya = $stmt->fetchColumn();
    
    if ($dosya && file_exists("uploads/$dosya")) {
        unlink("uploads/$dosya");
    }

    // Veritabanından sil (Cascade olduğu için ürünler de silinir ama biz garantiye alalım)
    $pdo->prepare("DELETE FROM fis_urunleri WHERE fis_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM fisler WHERE id = ?")->execute([$id]);

    header("Location: fislerim.php?durum=silindi");
    exit;
}

// 2. TEK ÜRÜN SİLME (Fişin toplam tutarını günceller!)
if ($tur == 'urun') {
    // Önce silinecek ürünün fiyatını ve hangi fişe ait olduğunu bul
    $stmt = $pdo->prepare("SELECT fiyat, fis_id FROM fis_urunleri WHERE id = ?");
    $stmt->execute([$id]);
    $urun = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($urun) {
        $fiyat = $urun['fiyat'];
        $fis_id = $urun['fis_id'];

        // 1. Ürünü Sil
        $pdo->prepare("DELETE FROM fis_urunleri WHERE id = ?")->execute([$id]);

        // 2. Fişin Toplam Tutarını Düşür (Matematik işlemi)
        $pdo->prepare("UPDATE fisler SET toplam_tutar = toplam_tutar - ? WHERE id = ?")->execute([$fiyat, $fis_id]);
        
        // Geri Dön
        header("Location: fis_detay.php?id=$fis_id&durum=urun_silindi");
        exit;
    }
}
?>