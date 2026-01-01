<?php
session_start();
require 'db.php';

// 1. GÜVENLİK KONTROLÜ
if (!isset($_SESSION['oturum'])) { 
    header("Location: giris.php"); 
    exit; 
}

// Sadece POST isteği gelirse çalışır
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Formdan gelen verileri alıyoruz
    $marketler = $_POST['market_adi'] ?? [];
    $tarihler = $_POST['tarih'] ?? [];
    $tutarlar = $_POST['toplam_tutar'] ?? [];
    $dosyalar = $_POST['dosya_yolu'] ?? [];
    $urunler = $_POST['urunler'] ?? []; 

    try {
        // İşlemi başlat (Bir hata olursa hepsini iptal etmek için)
        $pdo->beginTransaction();

        foreach ($marketler as $index => $market) {
            
            // --- VERİ DÜZELTME BÖLÜMÜ ---
            
            // Tarih boşsa bugünün tarihini at
            $tarih = !empty($tarihler[$index]) ? $tarihler[$index] : date('Y-m-d');
            
            // Tutar Temizliği (Örn: "1.250,50 TL" -> 1250.50)
            $hamTutar = $tutarlar[$index];
            $tutar = preg_replace('/[^0-9,.-]/', '', $hamTutar); // Sadece sayı, nokta, virgül, eksi kalır
            $tutar = str_replace(',', '.', $tutar); // Virgülü noktaya çevir
            
            $dosya = $dosyalar[$index];
            $uye_id = $_SESSION['id']; // Giriş yapan kullanıcının ID'si

            // 1. FİŞİ KAYDET
            $sql = "INSERT INTO fisler (kullanici_id, market_adi, tarih, toplam_tutar, dosya_yolu) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$uye_id, $market, $tarih, $tutar, $dosya]);
            
            // Eklenen fişin ID'sini al (Ürünleri buna bağlayacağız)
            $fis_id = $pdo->lastInsertId();

            // 2. ÜRÜNLERİ KAYDET (Eğer ürün varsa)
            if (isset($urunler[$index]) && is_array($urunler[$index])) {
                foreach ($urunler[$index] as $urun) {
                    $uAd = $urun['urun_adi'];
                    $uKat = $urun['kategori'];
                    
                    // Ürün fiyatını temizle
                    $uFiyat = preg_replace('/[^0-9,.-]/', '', $urun['fiyat']);
                    $uFiyat = str_replace(',', '.', $uFiyat);

                    $sqlUrun = "INSERT INTO fis_urunleri (fis_id, urun_adi, fiyat, kategori) VALUES (?, ?, ?, ?)";
                    $pdo->prepare($sqlUrun)->execute([$fis_id, $uAd, $uFiyat, $uKat]);
                }
            }
        }

        // Hata olmadıysa işlemi onayla
        $pdo->commit();

        // Başarılı olursa Fişlerim sayfasına gönder
        header("Location: fislerim.php?durum=ok");
        exit;

    } catch (Exception $e) {
        // Hata olursa yapılanları geri al
        $pdo->rollBack();
        die("<h3>Kayıt sırasında bir hata oluştu:</h3> " . $e->getMessage());
    }

} else {
    // Eğer sayfaya direkt girmeye çalışırsa ana sayfaya at
    header("Location: index.php");
    exit;
}
?>