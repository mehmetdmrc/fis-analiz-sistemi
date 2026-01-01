<?php
session_start();
require 'db.php';

$mesaj = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kadi = trim($_POST['kadi']);
    $sifre = trim($_POST['sifre']);

    if ($kadi && $sifre) {
        // Kullanıcı adı var mı kontrol et
        $stmt = $pdo->prepare("SELECT id FROM kullanicilar WHERE kadi = ?");
        $stmt->execute([$kadi]);
        
        if ($stmt->rowCount() > 0) {
            $mesaj = "Bu kullanıcı adı zaten alınmış.";
        } else {
            // Şifreyi güvenli hale getir (Hash)
            $hashliSifre = password_hash($sifre, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO kullanicilar (kadi, sifre) VALUES (?, ?)";
            if ($pdo->prepare($sql)->execute([$kadi, $hashliSifre])) {
                header("Location: giris.php?durum=kayit_ok");
                exit;
            } else {
                $mesaj = "Bir hata oluştu.";
            }
        }
    } else {
        $mesaj = "Lütfen tüm alanları doldurun.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - FinansPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card p-4" style="width: 400px;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-4 text-primary">Kayıt Ol</h3>
            
            <?php if($mesaj): ?>
                <div class="alert alert-danger"><?php echo $mesaj; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="text" name="kadi" class="form-control" required>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="sifre" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Hemen Başla</button>
            </form>
            <div class="mt-3">
                <small>Zaten hesabın var mı? <a href="giris.php">Giriş Yap</a></small>
            </div>
        </div>
    </div>
</body>
</html>