<?php
require 'db.php';
// Header gelmeden kontrolleri yap
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }

// ...
$fis_id = $_GET['id'];
$uye_id = $_SESSION['id']; // Oturumdaki üye

// SORGUSUNA "AND kullanici_id = ?" ekliyoruz
$stmt = $pdo->prepare("SELECT * FROM fisler WHERE id = ? AND kullanici_id = ?");
$stmt->execute([$fis_id, $uye_id]);
$fis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fis) {
    // Fiş yoksa VEYA başkasınınsa buraya düşer
    die("Fiş bulunamadı veya bu fişi görüntüleme yetkiniz yok.");
}
// ...

include 'includes/header.php';
?>

<?php if(isset($_GET['durum']) && $_GET['durum'] == 'urun_silindi'): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i> Ürün silindi ve fiş toplamı güncellendi!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-file-invoice me-2 text-primary"></i> Fiş Detayı</span>
                <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($fis['market_adi']); ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-4 p-3 bg-light rounded mx-1 border">
                    <div class="col-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Tarih</small>
                        <div class="fw-bold fs-5"><?php echo date("d.m.Y", strtotime($fis['tarih'])); ?></div>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Toplam Tutar</small>
                        <div class="fw-bold fs-4 text-success"><?php echo number_format($fis['toplam_tutar'], 2); ?> ₺</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 px-1">🛒 Ürün Listesi</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Kategori</th>
                                <th class="text-end">Fiyat</th>
                                <th class="text-center" width="50">Sil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $urunler = $pdo->prepare("SELECT * FROM fis_urunleri WHERE fis_id = ?");
                            $urunler->execute([$fis_id]);
                            foreach ($urunler as $urun): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($urun['urun_adi']); ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo htmlspecialchars($urun['kategori']); ?></span></td>
                                <td class="text-end fw-bold"><?php echo number_format($urun['fiyat'], 2); ?></td>
                                <td class="text-center">
                                    <a href="sil.php?tur=urun&id=<?php echo $urun['id']; ?>" 
                                       onclick="return confirm('Sadece bu ürünü silmek istediğine emin misin? Fiş tutarı düşecek.')" 
                                       class="btn btn-sm btn-outline-danger border-0">
                                       <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 text-center pb-3">
                 <a href="fislerim.php" class="btn btn-outline-secondary w-100"><i class="fa-solid fa-arrow-left"></i> Listeye Dön</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Orijinal Fiş Görseli</div>
            <div class="card-body text-center bg-light">
                <?php if (!empty($fis['dosya_yolu'])): ?>
                    <img src="uploads/<?php echo $fis['dosya_yolu']; ?>" class="img-fluid rounded shadow-sm mb-2" style="max-height: 400px; object-fit: contain;">
                    <a href="uploads/<?php echo $fis['dosya_yolu']; ?>" target="_blank" class="btn btn-primary btn-sm w-100">
                        <i class="fa-solid fa-magnifying-glass-plus"></i> Resmi Büyüt
                    </a>
                <?php else: ?>
                    <p class="text-muted py-4">Görsel bulunamadı.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-danger border-1 shadow-sm">
            <div class="card-body">
                <h6 class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i>DİKKAT!</h6>
                <p class="small text-muted mb-2">Bu fişi tamamen silerseniz geri getiremezsiniz.</p>
                <a href="sil.php?tur=fis&id=<?php echo $fis_id; ?>" 
                   onclick="return confirm('Tüm fişi silmek istediğine emin misin?')" 
                   class="btn btn-danger w-100 btn-sm">
                   <i class="fa-solid fa-trash"></i> Fişi Tamamen Sil
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>