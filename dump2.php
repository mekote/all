<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = $_POST['database'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $host = $_POST['host'] ?? 'localhost';

    if (empty($database) || empty($user) || empty($pass)) {
        // Eğer gerekli bilgiler boşsa hata mesajı döner
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Lütfen veritabanı adı, kullanıcı adı ve parola alanlarını doldurunuz.'
        ]);
        exit;
    }

    // Geçici bir dosyaya SQL dump yap
    $outputFile = tempnam(sys_get_temp_dir(), 'dump_') . ".sql";

    // mysqldump komutunu çalıştır ve çıktıyı geçici dosyaya kaydet
    exec("mysqldump --user={$user} --password='{$pass}' --host={$host} --no-tablespaces {$database} > {$outputFile} 2>&1", $output, $result_code);

    // Eğer mysqldump başarılı olduysa, dosyayı indir
    if ($result_code === 0) {
        // SQL dump işlemi başarılı, dosya indirme başlıkları ayarla
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $database . '_backup.sql"');
        header('Content-Length: ' . filesize($outputFile));
        header('Pragma: public');
        
        // Tamponları temizle
        ob_clean();
        flush();
        
        // Dosyayı gönder
        readfile($outputFile);

        // Geçici dosyayı sil
        unlink($outputFile);
        exit;
    } else {
        // Hata mesajını döndür
        unlink($outputFile); // Geçici dosyayı sil
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'SQL dump işlemi başarısız oldu: ' . implode("\n", $output)
        ]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup</title>
    <script>
        function backupDatabase() {
            // Form verilerini oku
            var formData = new FormData();
            formData.append('database', document.getElementById('database').value);
            formData.append('user', document.getElementById('user').value);
            formData.append('pass', document.getElementById('pass').value);
            formData.append('host', document.getElementById('host').value);

            // Ajax isteği yap
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.responseType = 'blob'; // Başarılı olduğunda dosyayı blob olarak al

            xhr.onload = function() {
                if (xhr.status === 200 && xhr.getResponseHeader('Content-Disposition')) {
                    // SQL dosyasını indir
                    var blob = new Blob([xhr.response], { type: 'application/octet-stream' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = document.getElementById('database').value + '_backup.sql';
                    link.click();
                } else {
                    // Hata mesajı varsa, bunu göster
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var response = JSON.parse(e.target.result);
                        document.getElementById('output').innerHTML = response.message;
                    };
                    reader.readAsText(xhr.response);
                }
            };

            xhr.onerror = function() {
                document.getElementById('output').innerHTML = 'Bir hata oluştu.';
            };

            xhr.send(formData);
        }
    </script>
</head>

<body>
    <h3>Veritabanı Yedekleme</h3>

    <!-- Bilgi giriş inputları -->
    <label for="database">Veritabanı Adı:</label>
    <input type="text" id="database" placeholder="Veritabanı adını girin"><br><br>

    <label for="user">Kullanıcı Adı:</label>
    <input type="text" id="user" placeholder="Kullanıcı adını girin"><br><br>

    <label for="pass">Parola:</label>
    <input type="text" id="pass" placeholder="Parolanızı girin"><br><br>

    <label for="host">Host:</label>
    <input type="text" id="host" value="localhost"><br><br>

    <!-- SQL Dump butonu -->
    <button onclick="backupDatabase()">SQL Dump Yap</button>

    <!-- Çıktılar -->
    <div id="output"></div>
</body>

</html>
