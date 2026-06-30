<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location; index.php");
    exit;
}
$company_id = $_GET["id"];
$current_user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_company"])) {
    $status = $_POST["status"];
    $memo = $_POST["memo"];
    
    $sql = "UPDATE companies SET status = :status, memo = :memo
            WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':memo', $memo, PDO::PARAM_STR);
    $stmt->bindValue(':id', $company_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_company"])) {
    $sql = "DELETE FROM companies WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $company_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM companies WHERE id = :id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $company_id, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
$stmt->execute();
$company = $stmt->fetch();

if (!$company) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>就活パートナー - 企業詳細・メモ編集</title>
    <script>
        function confirmDelete() {
            return confirm("本当にこの企業データを削除しますか？取り消しはできません。");
        }
    </script>
</head>
<body>
    <p><a href="index.php">← エントリー企業一覧に戻る</a></p>
    
    <h1>企業詳細:<?php echo htmlspecialchars($company["company_name"], ENT_QUOTES, 'UTF-8'); ?></h1>
    
    <form action="detail.php?id=<?php echo $company_id; ?>" method="POST">
        <p><strong>ES締切日:</strong> <?php echo htmlspecialchars($company['deadline_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>面接日程:</strong> <?php echo htmlspecialchars($company['interview_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></p>
        
        <label><strong>現在の選考ステータス:</strong>
            <select name="status">
                <option value="未エントリー" <?php if($company['status'] === '未エントリー') echo 'selected'; ?>>未エントリー</option>
                <option value="ES提出済" <?php if($company['status'] === 'ES提出済') echo 'selected'; ?>>ES提出済</option>
                <option value="1次選考中" <?php if($company['status'] === '1次選考中') echo 'selected'; ?>>1次選考中</option>
                <option value="2次選考中" <?php if($company['status'] === '2次選考中') echo 'selected'; ?>>2次選考中</option>
                <option value="最終選考中" <?php if($company['stastus'] === '最終選考中') echo 'selected'; ?>>最終選考中</option>
                <option value="内定" <?php if($company['status'] === '内定') echo 'selected'; ?>>内定</option>
            </select>
        </label>
        <br><br>
        
        <label><strong>ES内容・面接対策メモ:</strong><br>
            <textarea name="memo" rows="10" cols="60" placeholder="志望動機や面接で聞かれたこと、振り返りを自由に記入してください。"><?php echo htmlspecialchars($company["memo"], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </label>
        <br><br>
        
        <input type="submit" name="update_company" value="内容を更新する。">
    </form>
    
    <hr style="margin-top: 30px;">
    
    <form action="detail.php?id=<?php echo $company_id; ?>" method="POST" onsubmit="return confirmDelete();">
        <input type="submit" name="delete_company" value="この企業データを完全に削除する" style="color: red">
    </form>
</body>
</html>


























