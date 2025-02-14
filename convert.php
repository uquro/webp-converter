<?php

use Intervention\Image\ImageManagerStatic as Image;

// Autoload dosyasını dahil ediyoruz.
require __DIR__ . '/vendor/autoload.php';

// Load translations
$translations = require 'lang/tr.php';

// Dosya adlarını tanımlayalım.
$logFile    = 'conversion_errors.log';
$statusFile = 'conversion_status.json';

// Tüm dosyaları yeniden oluşturma isteği geldiğinde JSON dosyasını sıfırla
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    file_put_contents($statusFile, json_encode(array(
        'totalFiles' => 0,
        'convertedFiles' => 0,
        'convertedSize' => 0,
        'savedSpace' => 0,
        'errors' => array(),
        'currentIndex' => 0
    )));
    echo json_encode(array('status' => 'reset', 'message' => $translations['reset_success']));
    exit;
}

// Durum JSON dosyasını oku ya da varsayılan diziyi oluştur.
if (file_exists($statusFile)) {
    $statusData = json_decode(file_get_contents($statusFile), true);
} else {
    $statusData = [
        'totalFiles'      => 0,
        'convertedFiles'  => 0,
        'convertedSize'   => 0,
        'savedSpace'      => 0,
        'errors'          => [],
        'currentIndex'    => 0 // İşlenen son dosya indeksi
    ];
}

// Log dosyasını temizle.
if (file_exists($logFile)) {
    file_put_contents($logFile, '');
}

/**
 * Intervention Image kullanarak WebP'ye dönüştürme işlemi yapar.
 *
 * @param string $source Kaynak dosya yolu.
 * @param string $destination Hedef (WebP) dosya yolu.
 * @param int $quality WebP kalite oranı.
 * @return bool Dönüştürme başarılı ise true, aksi halde false.
 */
function convertToWebP($source, $destination, $quality = 80) {
    global $translations;
    try {
        $img = Image::make($source);
        // Resmi WebP formatına çevirip kaydediyoruz.
        $img->encode('webp', $quality)->save($destination);
        return true;
    } catch (Exception $e) {
        // Hata durumunda log dosyasına yaz
        file_put_contents(
            'conversion_errors.log',
            date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL,
            FILE_APPEND
        );
        return false;
    }
}

$sourceDir = __DIR__ . '/images/';
$destinationDir = __DIR__ . '/webp/';

// Eğer webp dizini yoksa oluştur
if (!file_exists($destinationDir)) {
    mkdir($destinationDir, 0777, true);
}

// İşlenecek dosyaları al
$files = glob($sourceDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

// İşlenen dosya indeksini al (varsayılan: 0)
$index = isset($_GET['index']) ? intval($_GET['index']) : 0;

// Toplam dosya sayısı
$totalFiles = count($files);

// Eğer tüm dosyalar işlendiyse sonuç döndür
if ($index >= $totalFiles) {
    echo json_encode(array(
        'status' => 'done',
        'message' => $translations['conversion_completed']
    ));
    exit;
}
$response = array();

// Şu anki dosyayı işle
$currentFile = $files[$index];
$fileName = basename($currentFile);
$destinationFile = $destinationDir . pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

// Dosyayı WebP'ye dönüştür
if (convertToWebP($currentFile, $destinationFile)) {
    $convertedSize = filesize($destinationFile);
    $originalSize = filesize($currentFile);
    $savedSpace = $originalSize - $convertedSize;
} else {
    $convertedSize = 0;
    $savedSpace = 0;
    $response['errors'][] = sprintf($translations['conversion_failed'], $fileName);
}

// Sonuçları döndür
$response['status'] = 'processing';
$response['currentFile'] = $fileName;
$response['totalFiles'] = $totalFiles;
$response['convertedFiles'] = $index + 1;
$response['convertedSize'] = $convertedSize;
$response['savedSpace'] = $savedSpace;
$response['progress'] = round((($index + 1) / $totalFiles * 100), 2);

echo json_encode($response);

// İşlem sonuçlarını JSON dosyasına kaydet
$statusData['totalFiles'] = $totalFiles;
$statusData['convertedFiles'] = $index + 1;
$statusData['convertedSize'] = $convertedSize;
$statusData['savedSpace'] = $savedSpace;
$statusData['currentIndex'] = $index; // İşlenen son dosyanın indeksi
if (isset($response['errors'])) {
    $statusData['errors'] = array_merge($statusData['errors'], $response['errors']);
}

file_put_contents($statusFile, json_encode($statusData));