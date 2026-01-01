<?php
require 'db.php';
include 'includes/header.php';

// Oturum Kontrolü
if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }
$uye_id = $_SESSION['id'];

// --- İSTATİSTİK SORGULARI ---

// 1. Bu Ay Toplam
$buAySorgu = $pdo->prepare("SELECT SUM(toplam_tutar) FROM fisler WHERE kullanici_id = ? AND MONTH(tarih) = MONTH(CURRENT_DATE()) AND YEAR(tarih) = YEAR(CURRENT_DATE())");
$buAySorgu->execute([$uye_id]);
$buAy = $buAySorgu->fetchColumn() ?: 0;

// 2. Geçen Ay Toplam
$gecenAySorgu = $pdo->prepare("SELECT SUM(toplam_tutar) FROM fisler WHERE kullanici_id = ? AND MONTH(tarih) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)");
$gecenAySorgu->execute([$uye_id]);
$gecenAy = $gecenAySorgu->fetchColumn() ?: 0;

// 3. Toplam Fiş Sayısı
$toplamFisSorgu = $pdo->prepare("SELECT COUNT(*) FROM fisler WHERE kullanici_id = ?");
$toplamFisSorgu->execute([$uye_id]);
$toplamFis = $toplamFisSorgu->fetchColumn();

// 4. Kategori Bazlı Harcama (Pasta Grafik İçin)
// DİKKAT: Sadece fiyatı 0'dan büyük olanları alıyoruz ki grafik bozulmasın
$katSql = "SELECT u.kategori, SUM(u.fiyat) as tutar 
           FROM fis_urunleri u 
           JOIN fisler f ON u.fis_id = f.id 
           WHERE f.kullanici_id = ? AND u.fiyat > 0
           GROUP BY u.kategori 
           ORDER BY tutar DESC LIMIT 6";
$katSorgu = $pdo->prepare($katSql);
$katSorgu->execute([$uye_id]);

$kategoriler = [];
$katTutarlar = [];
while($row = $katSorgu->fetch(PDO::FETCH_ASSOC)) {
    // Kategori ismi çok uzunsa kısalt (Grafiği bozmasın)
    $kisaAd = strlen($row['kategori']) > 15 ? substr($row['kategori'], 0, 15).'...' : $row['kategori'];
    $kategoriler[] = $kisaAd;
    $katTutarlar[] = $row['tutar'];
}

// 5. Son 5 Harcama
$sonFislerSorgu = $pdo->prepare("SELECT * FROM fisler WHERE kullanici_id = ? ORDER BY tarih DESC LIMIT 5");
$sonFislerSorgu->execute([$uye_id]);
$sonFisler = $sonFislerSorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark m-0">Genel Bakış</h2>
        <p class="text-muted m-0">Finansal durumunun özeti.</p>
    </div>
    <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fa-solid fa-plus-circle"></i> <span class="d-none d-md-inline ms-2">Yeni Fiş Ekle</span>
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3 rounded p-3">
                    <i class="fa-solid fa-wallet fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Bu Ay Harcanan</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($buAy, 2); ?> ₺</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3 rounded p-3">
                    <i class="fa-solid fa-calendar-check fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Geçen Ay</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($gecenAy, 2); ?> ₺</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3 rounded p-3">
                    <i class="fa-solid fa-receipt fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Toplam Fiş</h6>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $toplamFis; ?> Adet</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 border-bottom-0">
                <i class="fa-solid fa-chart-pie me-2 text-primary"></i> Harcamaların Nereye Gidiyor?
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                
                <?php if(empty($kategoriler)): ?>
                    <div class="text-center text-muted">
                        <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fa-solid fa-chart-simple fa-3x text-secondary opacity-25"></i>
                        </div>
                        <h6 class="fw-bold">Henüz Veri Yok</h6>
                        <p class="small mb-3">Grafik oluşması için en az bir fiş yüklemelisin.</p>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            Fiş Yükle
                        </button>
                    </div>
                <?php else: ?>
                    <div style="width: 100%; height: 300px;">
                        <canvas id="kategoriChart"></canvas>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Son İşlemler</span>
                <a href="fislerim.php" class="btn btn-sm btn-light text-primary fw-bold">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                
                <?php if(empty($sonFisler)): ?>
                     <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-receipt fa-2x mb-2 opacity-25"></i>
                        <p class="small">Henüz hiç fiş eklenmemiş.</p>
                     </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light small text-muted">
                                <tr><th class="ps-4">Market</th><th>Tarih</th><th class="text-end pe-4">Tutar</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($sonFisler as $fis): ?>
                                <tr style="cursor: pointer;" onclick="window.location='fis_detay.php?id=<?php echo $fis['id']; ?>'">
                                    <td class="fw-bold ps-4 text-primary">
                                        <?php echo htmlspecialchars($fis['market_adi']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date("d.m.Y", strtotime($fis['tarih'])); ?>
                                    </td>
                                    <td class="text-end fw-bold text-dark pe-4">
                                        <?php echo number_format($fis['toplam_tutar'], 2); ?> ₺
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php if(!empty($kategoriler)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('kategoriChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($kategoriler); ?>,
                    datasets: [{
                        data: <?php echo json_encode($katTutarlar); ?>,
                        backgroundColor: [
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                            '#858796', '#5a5c69', '#f8f9fc'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    layout: { padding: 20 }
                }
            });
        }
    });
</script>
<?php endif; ?>