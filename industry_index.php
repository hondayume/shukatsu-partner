<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$current_user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_industry'])) {
    $industry_name = $_POST['industry_name'];
    if (!empty($industry_name)) {
        $sql = "INSERT INTO industries (user_id, industry_name) VALUES (:user_id, :industry_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
        $stmt->bindValue(':industry_name', $industry_name, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: industry_index.php");
        exit;
    }
}

$sql = "SELECT i.*, GROUP_CONCAT(c.company_name SEPARATOR '、') as related_companies 
        FROM industries i 
        LEFT JOIN companies c ON i.id = c.industry_id 
        WHERE i.user_id = :user_id 
        GROUP BY i.id 
        ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
$stmt->execute();
$industries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>業界研究一覧 - 就活パートナー</title>
    <style>
        :root { --bg-color: #fafafa; --card-bg: #ffffff; --border-color: #edeef0; --text-color: #333; --text-muted: #666; --primary-color: #4a90e2; }
        body { font-family: -apple-system, sans-serif; background: var(--bg-color); color: var(--text-color); padding: 40px 20px; display: flex; justify-content: center; }
        .container { width: 100%; max-width: 800px; }
        .nav-links { margin-bottom: 20px; }
        .nav-links a { text-decoration: none; color: var(--primary-color); font-weight: bold; font-size: 14px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 20px; background: #e8f0ec; padding: 10px; border-radius: 6px; display: inline-block; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .industry-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); text-decoration: none; color: inherit; transition: transform 0.2s; display: flex; flex-direction: column; }
        .industry-card:hover { transform: translateY(-2px); }
        .card-img { height: 120px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; font-size: 24px; font-weight: bold; }
        .card-content { padding: 16px; flex: 1; }
        .card-title { font-size: 16px; font-weight: bold; margin: 0 0 8px 0; }
        .card-companies { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }

        .add-box { background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 24px; }
        .form-group { display: flex; gap: 10px; }
        input[type="text"] { padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; flex: 1; font-size: 14px; }
        .btn { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links">
        <a href="index.php">➔ 企業管理ダッシュボードに戻る</a>
    </div>
    
    <div class="title">📁 業界研究</div>

    <div class="add-box">
        <h3 style="margin-top:0; font-size:14px; color:var(--text-muted);">➕ 新しい業界を追加</h3>
        <form action="industry_index.php" method="POST" class="form-group">
            <input type="text" name="industry_name" required placeholder="業界名（例: IT業界、人材業界）">
            <button type="submit" name="add_industry" class="btn">追加</button>
        </form>
    </div>

    <div class="grid">
        <?php foreach ($industries as $ind): ?>
            <a href="industry_detail.php?id=<?php echo $ind['id']; ?>" class="industry-card">
                <div class="card-img" style="background-color: #f1f3f5; color: #666;">
                    💼
                </div>
                <div class="card-content">
                    <h4 class="card-title"><?php echo htmlspecialchars($ind['industry_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="card-companies">
                        📄 <?php echo $ind['related_companies'] ? htmlspecialchars($ind['related_companies'], ENT_QUOTES, 'UTF-8') : '登録企業なし'; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>