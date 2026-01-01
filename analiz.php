<?php
// Hataları gizle
error_reporting(0); 

session_start();
require 'db.php';
require 'config.php'; 

if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }
if (!isset($_FILES['fis_gorseli'])) { header("Location: index.php"); exit; }

// Uzun süren işlemler için süre sınırı yok
set_time_limit(600); 

if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }

$apiKey = $gizliApiKey;
$analizSonuclari = [];
$hataMesajlari = [];

// --- ADIM 1: SENİN HESABINDAKİ GEÇERLİ MODELİ BULALIM ---
// Tahmin etmiyoruz, Google'a soruyoruz.
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$modeller = json_decode($response, true);
$secilenModel = "";

// Listeden 'generateContent' destekleyen ve 'flash' (hızlı) olanı bul
if (isset($modeller['models'])) {
    foreach ($modeller['models'] as $m) {
        // Öncelik 1: Flash modeller (Hızlı ve kotası geniş)
        if (strpos($m['name'], 'flash') !== false && in_array("generateContent", $m['supportedGenerationMethods'])) {
            $secilenModel = $m['name']; // Örn: models/gemini-1.5-flash-latest
            break;
        }
    }
    
    // Flash bulamazsa 'pro' modeline bak
    if (empty($secilenModel)) {
        foreach ($modeller['models'] as $m) {
            if (strpos($m['name'], 'pro') !== false && in_array("generateContent", $m['supportedGenerationMethods'])) {
                $secilenModel = $m['name'];
                break;
            }
        }
    }
}

// Eğer hala model yoksa varsayılanı zorla (Son çare)
if (empty($secilenModel)) {
    $secilenModel = "models/gemini-1.5-flash";
}

// --- ADIM 2: SEÇİLEN MODEL İLE ANALİZ ---

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/$secilenModel:generateContent?key=" . $apiKey;
$dosyaSayisi = count($_FILES['fis_gorseli']['name']);

for ($i = 0; $i < $dosyaSayisi; $i++) {
    
    if ($_FILES['fis_gorseli']['error'][$i] !== 0) continue;

    $tmpYol = $_FILES['fis_gorseli']['tmp_name'][$i];
    $orijinalAd = $_FILES['fis_gorseli']['name'][$i];
    $uzanti = pathinfo($orijinalAd, PATHINFO_EXTENSION);
    $yeniDosyaAdi = uniqid() . "." . $uzanti;
    $hedefYol = "uploads/" . $yeniDosyaAdi;
    
    if (move_uploaded_file($tmpYol, $hedefYol)) {
        
        $resimData = base64_encode(file_get_contents($hedefYol));

        $promptMetni = "Bu bir alışveriş fişidir. Analiz et ve JSON ver: " .
                       "{'market_adi': 'String', 'tarih': 'YYYY-MM-DD', 'toplam_tutar': 'Number (Nokta kullan)', " .
                       "'urunler': [{'urun_adi': 'String', 'fiyat': 'Number (Nokta kullan)', 'kategori': 'String'}]}. " .
                       "KURALLAR: 1. Market adı yoksa 'Bilinmiyor'. 2. İndirim varsa fiyatı NEGATİF yaz. 3. Kategori TEK KELİME. 4. Fiyatlarda nokta kullan (15.50). 5. Saf JSON.";

        $payload = ["contents" => [["parts" => [["text" => $promptMetni], ["inline_data" => ["mime_type" => "image/jpeg", "data" => $resimData]]]]]];

        // --- KOTA HATASINA KARŞI DİRENÇLİ DÖNGÜ ---
        $deneme = 0;
        $basarili = false;
        
        while($deneme < 2 && !$basarili) {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $jsonTemiz = json_decode($response, true);
            
            // Kota hatası varsa (429) bekle ve tekrar dene
            if (isset($jsonTemiz['error']) && (strpos($jsonTemiz['error']['message'], 'Quota') !== false || $jsonTemiz['error']['code'] == 429)) {
                $deneme++;
                sleep(20); // 20 saniye bekle
                continue;
            }

            // Başarılıysa
            if (isset($jsonTemiz['candidates'][0]['content']['parts'][0]['text'])) {
                $hamMetin = $jsonTemiz['candidates'][0]['content']['parts'][0]['text'];
                $temizJson = str_replace(['```json', '```', 'json'], '', $hamMetin);
                $veri = json_decode($temizJson, true);
                
                if ($veri) {
                    $veri['id'] = $i;
                    $veri['dosya_yolu'] = $yeniDosyaAdi; 
                    $analizSonuclari[] = $veri;
                    $basarili = true;
                }
            } else {
                // Diğer hatalar (örn: resim okunamadı) için döngüyü kır
                if(isset($jsonTemiz['error'])) {
                    $hataMesajlari[] = "Hata (" . $secilenModel . "): " . $jsonTemiz['error']['message'];
                }
                break;
            }
        }
        
        // Her fiş arasında 5 saniye mola (Kota dostu)
        sleep(5); 
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Analiz Sonuçları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light container mt-5 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fa-solid fa-wand-magic-sparkles"></i> Analiz Sonuçları</h2>
            <small class="text-muted">Kullanılan Yapay Zeka Modeli: <strong><?php echo str_replace('models/', '', $secilenModel); ?></strong></small>
        </div>
        <a href="index.php" class="btn btn-secondary">İptal</a>
    </div>

    <?php if (!empty($hataMesajlari)): ?>
        <div class="alert alert-warning shadow-sm">
            <strong>Bilgi:</strong>
            <ul class="mb-0"><?php foreach($hataMesajlari as $h) echo "<li>$h</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($analizSonuclari)): ?>
    <form action="kaydet.php" method="POST">
        <?php foreach ($analizSonuclari as $k => $fis): ?>
        <div class="card mb-4 shadow border-0">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Fiş #<?php echo $k + 1; ?> - <?php echo htmlspecialchars($fis['market_adi']); ?></span>
                <a href="uploads/<?php echo $fis['dosya_yolu']; ?>" target="_blank" class="btn btn-sm btn-light text-success fw-bold">Görsel</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold text-secondary">Market</label>
                        <input type="text" name="market_adi[<?php echo $k; ?>]" class="form-control" value="<?php echo htmlspecialchars($fis['market_adi']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold text-secondary">Tarih</label>
                        <input type="date" name="tarih[<?php echo $k; ?>]" class="form-control" value="<?php echo $fis['tarih']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold text-secondary">Toplam Tutar</label>
                        <input type="text" name="toplam_tutar[<?php echo $k; ?>]" class="form-control fw-bold text-success" value="<?php echo $fis['toplam_tutar']; ?>">
                    </div>
                    <input type="hidden" name="dosya_yolu[<?php echo $k; ?>]" value="<?php echo $fis['dosya_yolu']; ?>">
                </div>

                <h5>Ürün Listesi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light"><tr><th>Ürün Adı</th><th>Kategori</th><th>Fiyat</th><th class="text-center">Sil</th></tr></thead>
                        <tbody>
                            <?php if(isset($fis['urunler'])): foreach($fis['urunler'] as $uk => $urun): ?>
                            <tr>
                                <td><input type="text" name="urunler[<?php echo $k; ?>][<?php echo $uk; ?>][urun_adi]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($urun['urun_adi']); ?>"></td>
                                <td><input type="text" name="urunler[<?php echo $k; ?>][<?php echo $uk; ?>][kategori]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($urun['kategori']); ?>"></td>
                                <td><input type="text" name="urunler[<?php echo $k; ?>][<?php echo $uk; ?>][fiyat]" class="form-control form-control-sm" value="<?php echo $urun['fiyat']; ?>"></td>
                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-success btn-lg w-100 mb-5">✅ Verileri Onayla ve Kaydet</button>
    </form>
    <?php endif; ?>

</body>
</html>