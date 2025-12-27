<?php
require 'db.php';

if (!isset($_GET['id'])) die("Fiş ID'si yok.");
$fis_id = $_GET['id'];

// Fişi ve Ürünleri Çek
$stmt = $db->prepare("SELECT * FROM fisler WHERE id = ?");
$stmt->execute([$fis_id]);
$fis = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtUrun = $db->prepare("SELECT * FROM urunler WHERE fis_id = ?");
$stmtUrun->execute([$fis_id]);
$urunler = $stmtUrun->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Fiş Detayı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .receipt-card {
            background: #fff;
            border-top: 5px solid #0d6efd; /* Mavi üst çizgi */
            border-bottom: 5px jagged #ccc; /* Süsleme amacı */
        }
    </style>
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <a href="fislerim.php" class="btn btn-link text-decoration-none text-muted mb-3">
                    <i class="fa-solid fa-arrow-left"></i> Listeye Dön
                </a>

                <div class="card receipt-card shadow rounded-3">
                    <div class="card-body p-4">
                        
                        <div class="text-center border-bottom pb-3 mb-3">
                            <h3 class="fw-bold text-uppercase"><?php echo $fis['market_adi']; ?></h3>
                            <p class="text-muted mb-0"><i class="fa-regular fa-clock me-1"></i> <?php echo date("d.m.Y", strtotime($fis['tarih'])); ?></p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead class="text-muted small border-bottom">
                                    <tr>
                                        <th>ÜRÜN</th>
                                        <th>KATEGORİ</th>
                                        <th class="text-end">FİYAT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urunler as $urun): ?>
                                    <tr>
                                        <td class="fw-medium"><?php echo $urun['urun_adi']; ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo $urun['kategori']; ?></span></td>
                                        <td class="text-end"><?php echo number_format($urun['fiyat'], 2, ',', '.'); ?> ₺</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="border-top pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-muted">TOPLAM TUTAR</span>
                                <span class="h3 mb-0 fw-bold text-primary"><?php echo number_format($fis['toplam_tutar'], 2, ',', '.'); ?> ₺</span>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer bg-light text-center small text-muted">
                        Bu fiş dijital olarak arşivlenmiştir.
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>