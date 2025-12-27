<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fiş Yükle | Fiş Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mt-5">
                
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="mb-4 text-primary">
                            <i class="fa-solid fa-cloud-arrow-up fa-4x"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Fiş Fotoğrafı Yükle</h2>
                        <p class="text-muted mb-4">Market fişinizin fotoğrafını çekin ve yükleyin. Yapay zeka sizin için analiz etsin.</p>
                        
                        <form action="analiz.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <input type="file" name="fis_gorseli" class="form-control form-control-lg" required accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Analiz Et
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 text-muted small">
                    <i class="fa-solid fa-shield-halved"></i> Verileriniz güvenle saklanmaktadır.
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>