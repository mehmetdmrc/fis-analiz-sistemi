<?php
session_start();
require 'db.php';

$hata = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kadi = trim($_POST['kadi']);
    $sifre = trim($_POST['sifre']);

    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE kadi = ?");
    $stmt->execute([$kadi]);
    $uye = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($uye && password_verify($sifre, $uye['sifre'])) {
        // GİRİŞ BAŞARILI
        $_SESSION['oturum'] = true;
        $_SESSION['id'] = $uye['id']; // Kullanıcı ID'sini sakla (ÇOK ÖNEMLİ)
        $_SESSION['kadi'] = $uye['kadi'];
        
        header("Location: index.php");
        exit;
    } else {
        $hata = "Kullanıcı adı veya şifre hatalı.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - FinansPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card p-4" style="width: 400px;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-2 text-primary">Hoşgeldiniz</h3>
            <p class="text-muted mb-4">Lütfen hesabınıza giriş yapın.</p>
            
            <?php if(isset($_GET['durum']) && $_GET['durum'] == 'kayit_ok'): ?>
                <div class="alert alert-success">Kayıt başarılı! Şimdi giriş yapabilirsin.</div>
            <?php endif; ?>

            <?php if($hata): ?>
                <div class="alert alert-danger"><?php echo $hata; ?></div>
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
                <button type="submit" class="btn btn-primary w-100 py-2">Giriş Yap</button>
            </form>
            <div class="mt-3">
                <small>Hesabın yok mu? <a href="kayit.php">Kayıt Ol</a></small>
            </div>
        </div>
    </div>
</body>
</html>