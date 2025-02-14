<?php
$translations = require 'lang/tr.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $translations['webp_converter_title'] ?? 'WebP Dönüştürücü'; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $translations['webp_converter_title'] ?? 'WebP Dönüştürücü'; ?></h1>
        <button id="startConversion" class="btn btn-primary">
            <?php echo $translations['start_conversion'] ?? 'Dönüştürmeyi Başlat'; ?>
        </button>
        <button id="stopConversion" class="btn btn-danger" style="display: none;">
            <?php echo $translations['stop_conversion'] ?? 'Dönüştürmeyi Durdur'; ?>
        </button>
        <div id="progress" class="progress" style="margin-top: 20px;">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                0%
            </div>
        </div>
        <div id="status" style="margin-top: 20px;">
            <p><?php echo $translations['current_file'] ?? 'İşlenen Dosya'; ?>: <span id="currentFile">-</span></p>
            <p><?php echo $translations['total_files'] ?? 'Toplam Dosya Sayısı'; ?>: <span id="totalFiles">0</span></p>
            <p><?php echo $translations['converted_files'] ?? 'Çevrilen Dosya Sayısı'; ?>: <span id="convertedFiles">0</span></p>
            <p><?php echo $translations['converted_size'] ?? 'Çevrilen Dosya Boyutu'; ?>: <span id="convertedSize">0 KB</span></p>
            <p><?php echo $translations['saved_space'] ?? 'Kazanılan Yer'; ?>: <span id="savedSpace">0 KB</span></p>
            <p><?php echo $translations['saved_percentage'] ?? 'Kazanç Yüzdesi'; ?>: <span id="savedPercentage">0%</span></p>
            <p><a href="conversion_errors.log" id="downloadLog" style="display: none;">
                <?php echo $translations['download_logs'] ?? 'Hata Loglarını İndir'; ?>
            </a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>