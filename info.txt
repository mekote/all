<?php

echo "<h1>PHP Bilgileri</h1>";
phpinfo();


echo "<h1>Sunucu Bilgileri</h1>";
echo "<pre>";
echo "Sunucu Yazılımı: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Sunucu Adı: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Sunucu Portu: " . $_SERVER['SERVER_PORT'] . "\n";
echo "Sunucu IP Adresi: " . $_SERVER['SERVER_ADDR'] . "\n";
echo "İstemci IP Adresi: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "Döküman Kökü: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Kök URL: " . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "\n";
echo "Yüksek Verimli İşlem: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "</pre>";


echo "<h1>İşletim Sistemi Bilgileri</h1>";
echo "<pre>";
echo "İşletim Sistemi: " . php_uname() . "\n";
echo "İşletim Sistemi Türü: " . PHP_OS . "\n";
echo "İşletim Sistemi Versiyonu: " . PHP_VERSION . "\n";
echo "</pre>";
?>
