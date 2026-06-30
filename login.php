<?php
session_start();
require_once 'db_connect.php';
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $sql = "SELECT * FROM user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_email"] = $user["eamil"];
        header("Location: index.php");
        exit;
    } else {
        $error_message = "メールアドレスまたはパスワードが違います。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>就活パートナー - ログイン</title>
</head>
<body>
    <h1>ログイン</h1>
    <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?php echo $error_mssage; ?></p>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <label>メールアドレス: <input type="email" name="email" required></label><br><br>
        <label>パスワード: <input type="password" name="password" required></label><br><br>
        <input type="submit" value="ログイン">
    </form>
    <br>
    <a href="register.php">新しくアカウントを作る（新規登録へ）</a>
</body>
</html>