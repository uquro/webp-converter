$(document).ready(function() {

    var index = 0; // İşlenecek dosya indeksi
    var totalFiles = 0; // Toplam dosya sayısı
    var totalSavedSpace = 0; // Toplam kazanılan yer
    var totalConvertedSize = 0; // Toplam çevrilen dosya boyutu

    // Sayfa açıldığında önceki işlemleri yükle
    $.ajax({
        url: 'conversion_status.json',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.totalFiles > 0) {
                index = data.currentIndex + 1; // Kaldığı yerden devam et
                totalFiles = data.totalFiles;
                totalSavedSpace = data.savedSpace;
                totalConvertedSize = data.convertedSize;

                // Önceki işlemleri göster
                $('#totalFiles').text(data.totalFiles);
                $('#convertedFiles').text(data.convertedFiles);
                $('#convertedSize').text((data.convertedSize / 1024).toFixed(2) + ' KB');
                $('#savedSpace').text((data.savedSpace / 1024).toFixed(2) + ' KB');
                $('#savedPercentage').text((data.savedSpace > 0 && data.totalFiles > 0) ?
                    ((data.savedSpace / (data.totalFiles * data.convertedSize)) * 100).toFixed(2) + '%' : '0%');
                $('.progress-bar').css('width', ((data.convertedFiles / data.totalFiles) * 100) + '%')
                    .attr('aria-valuenow', ((data.convertedFiles / data.totalFiles) * 100))
                    .text(((data.convertedFiles / data.totalFiles) * 100).toFixed(2) + '%');

                // Hataları göster
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(function(error) {
                        $('#status').append('<p class="text-danger">' + error + '</p>');
                    });
                    $('#downloadLog').show(); // Hata loglarını indirme bağlantısını göster
                }
            }
        },
        error: function() {
            console.log('Önceki işlemler bulunamadı.');
        }
    });

    // Dönüştürme işlemini başlat
    $('#startConversion').click(function() {
        $('#startConversion').hide(); // Başlat butonunu gizle
        $('#stopConversion').show(); // Durdur butonunu göster
        processNextFile();
    });

    // Dönüştürme işlemini durdur
    $('#stopConversion').click(function() {
        if (currentRequest) {
            currentRequest.abort(); // Devam eden AJAX isteğini iptal et
            currentRequest = null;
            $('#status').append('<p class="text-warning">Dönüştürme işlemi durduruldu.</p>');
            $('#startConversion').show(); // Başlat butonunu göster
            $('#stopConversion').hide(); // Durdur butonunu gizle
        }
    });

    function processNextFile() {
        $.ajax({
            url: 'convert.php',
            method: 'GET',
            data: { index: index }, // İşlenecek dosya indeksini gönder
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.status === 'done') {
                    $('#status').append('<p class="text-success">' + response.message + '</p>');
                    return;
                }
            
                // Hataları göster
                if (response.errors && response.errors.length > 0) {
                    response.errors.forEach(function(error) {
                        $('#status').append('<p class="text-danger">' + error + '</p>');
                    });
                    $('#downloadLog').show(); // Hata loglarını indirme bağlantısını göster
                }
            
                // İlerleme durumunu güncelle
                $('#currentFile').text(response.currentFile);
                $('#totalFiles').text(response.totalFiles);
                $('#convertedFiles').text(response.convertedFiles);
                $('#convertedSize').text((response.convertedSize / 1024).toFixed(2) + ' KB');
                totalSavedSpace += response.savedSpace;
                $('#savedSpace').text((totalSavedSpace / 1024).toFixed(2) + ' KB');
                $('#savedPercentage').text((totalSavedSpace > 0 && response.totalFiles > 0) ?
                    ((totalSavedSpace / (response.totalFiles * response.convertedSize)) * 100).toFixed(2) + '%' : '0%');
                $('.progress-bar').css('width', response.progress + '%').attr('aria-valuenow', response.progress).text(response.progress + '%');
            
                // Bir sonraki dosyayı işle
                index++;
                if (index < response.totalFiles) {
                    processNextFile();
                }
            },
            error: function(xhr, status, error) {
                $('#status').append('<p class="text-danger">Hata: ' + error + '</p>');
            }
        });
    }
});