<?php
require 'db.php';
include 'includes/header.php';

// Güvenlik: Oturum açık mı?
if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }

// Sadece giriş yapan kullanıcının fişlerini getir
$uye_id = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT * FROM fisler WHERE kullanici_id = ? ORDER BY tarih DESC");
$stmt->execute([$uye_id]);
$fisler = $stmt->fetchAll(PDO::FETCH_ASSOC); // Veriyi diziye çeviriyoruz
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold m-0">Tüm Fişler</h2>
    <span class="badge bg-primary fs-6"><?php echo count($fisler); ?> Kayıt</span>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 datatable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Market</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th class="text-end pe-4">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($fisler as $fis): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($fis['market_adi']); ?></td>
                        <td data-sort="<?php echo $fis['tarih']; ?>">
                            <?php echo date("d.m.Y", strtotime($fis['tarih'])); ?>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <?php echo number_format($fis['toplam_tutar'], 2); ?> ₺
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="fis_detay.php?id=<?php echo $fis['id']; ?>" class="btn btn-sm btn-outline-primary" title="Detay"><i class="fa-solid fa-eye"></i></a>
                            <a href="sil.php?tur=fis&id=<?php echo $fis['id']; ?>" onclick="return confirm('Silmek istediğine emin misin?')" class="btn btn-sm btn-outline-danger" title="Sil"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>