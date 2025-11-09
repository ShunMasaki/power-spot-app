<?php
$host = 'powerspot-db.c85u62qosbmn.us-east-1.rds.amazonaws.com';
$dbname = 'powerspot';
$username = 'admin';
$password = 'PowerSpot2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ データベース接続成功！\n";
} catch (PDOException $e) {
    echo "❌ データベース接続エラー: " . $e->getMessage() . "\n";
}
?>
