<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Fişi Kaydet
    $stmt = $db->prepare("INSERT INTO fisler (market_adi, tarih, toplam_tutar) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['market_adi'],
        $_POST['tarih'],
        $_POST['toplam_tutar']
    ]);
    
    $fis_id = $db->lastInsertId();

    // Ürünleri Kaydet
    if (isset($_POST['urunler'])) {
        $stmtUrun = $db->prepare("INSERT INTO urunler (fis_id, urun_adi, fiyat, kategori) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['urunler'] as $urun) {
            $stmtUrun->execute([
                $fis_id,
                $urun['urun_adi'],
                $urun['fiyat'],
                $urun['kategori']
            ]);
        }
    }

    // İşlem bitince direkt listeye at
    header("Location: fislerim.php");
    exit;
}
?>