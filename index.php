<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SSEION["user_id"];

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_company"])) {
    $company_name = $_POST["company_name"];
    $deadline_date = $_POST["deadline_date"];
    $interview_date = $_POST["interview_date"];
    $status = $_POST["status"];
    
    $sql = "INSERT INTO companies (user_id, company_name, deadline_date, interview_date, status, memo)
            VALUES (:user_id, :company_name, :deadline_date, :interview_date, :status, '')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':company_name', $company_name, PDO::PARAM_STR);
    $stmt->bindValue(':deadline_date', !empty($deadline_date) ? $deadline_date : null, PDO::PARAM_STR);
    $stmt->bindValue(':interview_date', !empty($interview_date) ? $interview_date : null, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->execute();
    
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM companies WHERE user_id = :user_id ORDER BY deadline_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
$stmt->execute();
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>就活パートナー - エントリー企業一覧</title>
</head>
<bode>
    <div style="text-align: right;">
        ログイン中: <?php echo htmlspecialchars($_SESSION["user_email"], ENT_QUOTES, 'UTF-8'); ?>
        | <a href="logout.php">ログアウト</a>
    </div>
    
    <h1>就活パートナー（メイン画面）</h1>
    
    <h2>+ 新規企業登録フォーム</h2>
    <form action="index.php" method="POST">
        <label>企業名: <input type="text" name="company_name" required></label><br><br>
        <label>ES締切日: <input type="date" name="deadline_date"></label><br><br>
        <label>面接日: <input type="date" name="interview_date"></label><br><br>
        
        <label>選考ステータス:
            <select name="status">
                <option value="未エントリー">未エントリー</option>
                <option value="ES提出済">ES提出済</option>
                <option value="1次選考中">1次選考中</option>
                <option value="2次選考中">2次選考中</option>
                <option value="最終選考中">最終選考中</option>
                <option value="内定">内定</option>
            </select>
        </label><br><br>
        <input type="submit" name="add_company" value="企業を登録する">
    </form>
    
    <hr>
    
    <h2>エントリー企業リスト（締切順）</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>企業名（クリックで詳細・メモ編集へ）</th>
            <th>ES締切日</th>
            <th>面接日</th>
            <th>ステータス</th>
        </tr>
        <?php foreach ($companies as $company): ?>
            <tr>
                <td>
                    <a href="detail.php?id=<?php echo $company['id']; ?>">
                        <?php echo htmlspecialchars($company['company_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($company['deadline_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($company['interview_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($company['status'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</bode>
</html>