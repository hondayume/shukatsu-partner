<?php
$dsn = 'mysql:dbname=あなたのデータベース名;host=localhost;charset=utf8';
$user = 'あなたのユーザー名';
$password = 'あなたのパスワード';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('データベース接続エラー: ' . $e->getMessage());
}
?>