</div> <footer class="bg-white text-center py-4 mt-auto border-top">
    <div class="container text-muted small">
        &copy; <?php echo date("Y"); ?> FinansPro - Akıllı Fiş Takip Sistemi | <i class="fa-solid fa-code"></i> ile geliştirildi.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function () {
        $('.datatable').DataTable({
            // --- TÜRKÇE DİL AYARLARI ---
            language: {
                "emptyTable": "Tabloda herhangi bir veri mevcut değil",
                "info": "Toplam _TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
                "infoEmpty": "Kayıt yok",
                "infoFiltered": "(_MAX_ kayıt içerisinden bulunan)",
                "lengthMenu": "Sayfada _MENU_ kayıt göster",
                "loadingRecords": "Yükleniyor...",
                "processing": "İşleniyor...",
                "search": "Ara:",
                "zeroRecords": "Eşleşen kayıt bulunamadı",
                "paginate": {
                    "first": "İlk",
                    "last": "Son",
                    "next": "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sonraki",
                    "previous": "Önceki&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
                },
                "aria": {
                    "sortAscending": ": artan sütun sıralamasını aktifleştir",
                    "sortDescending": ": azalan sütun sıralamasını aktifleştir"
                }
            },
            // Varsayılan sıralama: 2. Sütun (Tarih) - Yeniden Eskiye
            order: [[1, 'desc']] 
        });
    });
</script>

</body>
</html>