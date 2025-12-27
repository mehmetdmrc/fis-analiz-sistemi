<?php
// Hataları göster
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php'; // Gizli dosyayı çağır
$apiKey = $gizliApiKey; // Oradaki değişkeni kullan

// SENİN LİSTENDEKİ EN GARANTİ MODEL:
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

$data = []; 

if (isset($_FILES['fis_gorseli'])) {

    // Dosya yükleme kontrolü
    if ($_FILES['fis_gorseli']['error'] !== 0) {
        die("Resim yüklenemedi. Hata kodu: " . $_FILES['fis_gorseli']['error']);
    }

    $resimYolu = $_FILES['fis_gorseli']['tmp_name'];
    $resimData = base64_encode(file_get_contents($resimYolu));

    $promptMetni = "Bu bir alışveriş fişidir. Fişi analiz et. Şu JSON formatında çıktı ver: " .
                   "{'market_adi': 'String', 'tarih': 'YYYY-MM-DD', 'toplam_tutar': Number, " .
                   "'urunler': [{'urun_adi': 'String', 'fiyat': Number, 'kategori': 'String'}]}. " .
                   "Market adını bulamazsan 'Bilinmiyor' yaz. Kategorileri ürüne göre tahmin et. " .
                   "Sadece saf JSON ver, markdown kullanma.";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $promptMetni],
                    ["inline_data" => ["mime_type" => "image/jpeg", "data" => $resimData]]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // SSL Hatasını iptal et (XAMPP için şart)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    
    if(curl_errno($ch)){
        die('Bağlantı Hatası: ' . curl_error($ch));
    }
    curl_close($ch);
    
    $sonuc = json_decode($response, true);

    // Hata kontrolü
    if (isset($sonuc['error'])) {
        die("API Hatası: " . $sonuc['error']['message']);
    }
    
    // Veriyi Çek
    if(isset($sonuc['candidates'][0]['content']['parts'][0]['text'])) {
        $aiMetni = $sonuc['candidates'][0]['content']['parts'][0]['text'];
        $aiMetni = preg_replace('/```json|```/', '', $aiMetni); // Temizlik
        $data = json_decode($aiMetni, true);
    } else {
        die("Boş cevap geldi. Google Yanıtı: " . htmlspecialchars($response));
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sonuçları Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 pb-5">
    <form action="kaydet.php" method="POST">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Fiş Bilgileri</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Market Adı</label>
                        <input type="text" name="market_adi" class="form-control" value="<?php echo $data['market_adi'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tarih</label>
                        <input type="date" name="tarih" class="form-control" value="<?php echo $data['tarih'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Toplam Tutar</label>
                        <input type="text" name="toplam_tutar" class="form-control" value="<?php echo $data['toplam_tutar'] ?? ''; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-secondary text-white">Ürünler</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($data['urunler']) && is_array($data['urunler'])): ?>
                            <?php foreach ($data['urunler'] as $index => $urun): ?>
                            <tr>
                                <td>
                                    <input type="text" name="urunler[<?php echo $index; ?>][urun_adi]" class="form-control" value="<?php echo $urun['urun_adi']; ?>">
                                </td>
                                <td>
                                    <input type="text" name="urunler[<?php echo $index; ?>][kategori]" class="form-control" value="<?php echo $urun['kategori']; ?>">
                                </td>
                                <td>
                                    <input type="text" name="urunler[<?php echo $index; ?>][fiyat]" class="form-control" value="<?php echo abs($urun['fiyat']); ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Veri yok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Kaydet</button>
    </form>
</body>
</html>