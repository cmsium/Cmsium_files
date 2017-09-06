<?php
$files = [
    '/home/nick/cmsium/Study_Nikita_BAEV/zend_tests_and_notes/tests/memcached_VS_hdd/files/5a4432139876a985a76374aca6124742.png',
    '/home/nick/cmsium/Study_Nikita_BAEV/zend_tests_and_notes/tests/memcached_VS_hdd/files/test_file_txt.txt',
    '/home/nick/Изображения/test_image_1.png',
];
$file = $files[array_rand($files)];
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="asdasd"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
ob_start();
echo file_get_contents($file);
ob_flush();