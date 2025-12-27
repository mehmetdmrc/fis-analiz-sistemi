<?php 
require 'db.php'; 

// Toplam harcamayı hesaplayalım
$toplamSorgu = $db->query("SELECT SUM(toplam_tutar) as genel_toplam FROM fisler");
$genelToplam = $toplamSorgu->fetch(PDO::FETCH_ASSOC)['genel_toplam'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Fişlerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-1 opacity-75">Toplam Harcama</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($genelToplam, 2, ',', '.'); ?> ₺</h2>
                        </div>
                        <i class="fa-solid fa-wallet fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Geçmiş Alışverişler</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Market Adı</th>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sorgu = $db->query("SELECT * FROM fisler ORDER BY tarih DESC");
                        while ($fis = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                            // Tarihi güzelleştir (2025-10-15 -> 15.10.2025)
                            $tarih = date("d.m.Y", strtotime($fis['tarih']));
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?php echo $fis['market_adi']; ?></td>
                                <td class="text-secondary"><i class="fa-regular fa-calendar me-1"></i><?php echo $tarih; ?></td>
                                <td class="text-success fw-bold"><?php echo number_format($fis['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                <td class="text-end pe-4">
                                    <a href="fis_detay.php?id=<?php echo $fis['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-eye me-1"></i> Detay
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>