<?php
require_once 'db_connect.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    if (!empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':password', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $hasued_password, PDO::PARAM_STR);
            $stmt->execute();
            
            $message = "会員登録が完了しました！ログインしてください。";
        } catch (PDOException $e) {
            $message = "このメールアドレスは既に登録されています。";
        }
    }else {
        $message = "すべての項目を入力してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>就活パートナー - 新規会員登録</title>
</head>
<body>
    <h1>新規会員登録</h1>
    <?php if (!empty($message)): ?>
        <p style="color: blue;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <form action="register.php" method="POST">
        <label>メールアドレス: <input type="emeil" name="email" required></label><br><br>
        <label>パスワード: <input type="password" name="password" required></label><br><br>
        <input type="submit" value="アカウントを作成する">
    </form>
    <br>
    <a href="login.php>"すでにアカウントをお持ちの方（ログインへ）</a>
</body>
</html>