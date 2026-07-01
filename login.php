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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>就活パートナー - ログイン</title>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-muted: #666666;
            --border-color: #e9ecef;
            --primary-color: #4a90e2;
            --primary-hover: #357abd;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .brand-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .brand-subtitle {
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-muted);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #fafafa;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.2s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .submit-btn {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .footer-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="brand-title">🚀 就活パートナー</div>
    <div class="brand-subtitle">スケジュールと選考状況をスマートに管理</div>

    <div class="card">
        <form action="login.php" method="POST">
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" required placeholder="example@email.com">
            </div>
            
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <button type="submit" name="login" class="submit-btn">ログイン</button>
        </form>
    </div>

    <div class="footer-link">
        アカウントをお持ちでないですか？ <a href="register.php">新規登録へ</a>
    </div>
</div>

</body>
</html>