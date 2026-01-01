<?php
$host = 'localhost';
$dbname = 'fistakip'; // Veritabanı adının bu olduğundan emin ol
$username = 'root';
$password = '';

try {
    // Değişken adımız $pdo. Diğer dosyalarda bunu kullanıyoruz.
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Hata modunu açıyoruz
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>