<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['oturum'])) { header("Location: giris.php"); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinansPro Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
    
    /* --- DATATABLES MODERN TASARIM (Gelişmiş) --- */

    /* --- DATATABLES MODERN TASARIM (Optimize Edilmiş) --- */

    /* 1. GENEL YAPI VE BOŞLUKLAR */
    /* Tablonun üstündeki (Arama ve Seçim) alan */
    .dataTables_wrapper .row:first-child {
        padding: 20px 20px 10px 20px;
        margin-bottom: 15px;
        align-items: center;
    }

    /* Tablonun altındaki (Sayfalama) alan */
    .dataTables_wrapper .row:last-child {
        padding: 10px 20px 20px 20px;
        border-top: 1px solid #f1f5f9;
        margin-top: 10px;
        align-items: center;
    }

    /* Genel Etiket Yazıları (Ara, Kayıt Göster vb.) */
    .dataTables_wrapper label {
        color: #64748b;
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* 2. ARAMA KUTUSU (SEARCH) */
    .dataTables_filter label {
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .dataTables_filter input {
        border: 2px solid #e2e8f0 !important;
        border-radius: 50px !important; /* Tam yuvarlak (Hap şeklinde) */
        padding: 8px 20px !important;
        margin-left: 10px !important;
        outline: none !important;
        transition: all 0.3s ease;
        background-color: #fff;
        font-size: 0.9rem;
        min-width: 250px; /* Kutuyu geniş tutuyoruz */
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    /* Arama kutusuna tıklayınca */
    .dataTables_filter input:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15) !important;
        background-color: #f8faff;
    }

    /* 3. SAYFA UZUNLUĞU SEÇİMİ (SHOW ENTRIES) */
    .dataTables_length select {
        border: 2px solid #e2e8f0 !important;
        border-radius: 50px !important;
        padding: 6px 30px 6px 15px !important;
        margin: 0 5px !important;
        cursor: pointer;
        background-color: white;
        font-weight: 600;
        color: #333;
        outline: none !important;
    }

    .dataTables_length select:focus {
        border-color: var(--primary-color) !important;
    }

    /* 4. BİLGİ METNİ (Showing 1 to 10...) */
    .dataTables_info {
        color: #94a3b8 !important;
        font-size: 0.9rem;
        padding-top: 10px !important;
    }

    /* 5. SAYFALAMA BUTONLARI (PAGINATION) */
    .dataTables_paginate {
        margin-top: 5px !important;
        display: flex;
        justify-content: flex-end;
        gap: 5px;
    }

    .page-item .page-link {
        border: none !important;
        border-radius: 50% !important; /* Yuvarlak butonlar */
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-weight: 600;
        margin: 0 2px;
        transition: all 0.2s ease;
        background-color: transparent;
    }

    /* Butonun üzerine gelince */
    .page-item .page-link:hover {
        background-color: #e2e8f0 !important;
        color: var(--primary-color) !important;
        transform: translateY(-2px); /* Hafif yukarı zıplama efekti */
    }

    /* Aktif Sayfa Butonu (Seçili Olan) */
    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
    }

    /* Pasif Butonlar (Önceki/Sonraki kilitli hali) */
    .page-item.disabled .page-link {
        background-color: transparent !important;
        color: #cbd5e1 !important;
        cursor: not-allowed;
        transform: none;
    }
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --bg-color: #f8f9fa;
        }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-color); color: #2b2d42; }
        
        .navbar { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3); padding: 1rem 0; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); transition: all 0.3s ease; overflow: hidden; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
        .card-header { background-color: white; border-bottom: 1px solid #eee; padding: 1.2rem; font-weight: 600; }
        .table-hover tbody tr:hover { background-color: #f1f4ff; }

        /* UPLOAD ALANI TASARIMI */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 3rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8fafc;
        }
        .upload-zone:hover {
            border-color: var(--primary-color);
            background-color: #eff6ff;
        }
        .upload-zone.active {
            border-style: solid;
            border-color: #10b981;
            background-color: #ecfdf5;
        }
        
        @media (max-width: 768px) {
            .navbar-collapse { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); margin-top: 10px; border-radius: 10px; padding: 10px; }
            .display-6 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fa-solid fa-chart-simple me-2"></i> FinansPro</a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mobileMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fa-solid fa-home me-1"></i> Ana Sayfa</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="fislerim.php"><i class="fa-solid fa-receipt me-1"></i> Tüm Fişler</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fa-solid fa-plus-circle me-1"></i> Fiş Ekle</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <div class="text-white d-none d-lg-block">
                    <small style="opacity:0.8;">Merhaba,</small><br>
                    <strong><?php echo $_SESSION['kadi']; ?></strong>
                </div>
                <a href="cikis.php" class="btn btn-light btn-sm rounded-pill px-3 text-primary fw-bold">Çıkış</a>
            </div>
        </div>
    </div>
</nav>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Fiş Yükle & Analiz Et</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <form id="analizFormu" action="analiz.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <div id="uploadContent">
                            <i class="fa-solid fa-cloud-arrow-up fa-3x text-secondary mb-3" id="mainIcon"></i>
                            <h6 class="fw-bold text-dark" id="mainText">Fiş Fotoğrafı Seç</h6>
                            <p class="text-muted small mb-0" id="subText">veya buraya sürükle bırak</p>
                        </div>
                        <input type="file" name="fis_gorseli[]" id="fileInput" class="d-none" multiple accept="image/*">
                    </div>

                    <div id="actionArea" class="mt-3 d-none">
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold" id="submitBtn">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Analizi Başlat
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<div class="container py-4">

<script>
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const actionArea = document.getElementById('actionArea');
    const mainIcon = document.getElementById('mainIcon');
    const mainText = document.getElementById('mainText');
    const subText = document.getElementById('subText');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('analizFormu');

    // Dosya seçildiğinde çalışır
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            // Tasarımı güncelle
            dropZone.classList.add('active'); // Yeşil kenarlık
            mainIcon.className = 'fa-solid fa-check-circle fa-3x text-success mb-3';
            
            // Kaç dosya seçildiğini yaz
            if (this.files.length === 1) {
                mainText.innerText = this.files[0].name;
            } else {
                mainText.innerText = this.files.length + " Adet Fiş Seçildi";
            }
            
            subText.innerText = "Değiştirmek için tekrar tıkla";
            
            // Analiz butonunu göster
            actionArea.classList.remove('d-none');
        }
    });

    // Form gönderilince butonu yükleniyor moduna al
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-2"></i> Analiz Ediliyor...';
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-dark'); // Rengi koyulaştır
    });
</script>