<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION["user_id"];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>就活パートナー - エントリー企業一覧</title>
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
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 650px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .user-info {
            font-size: 13px;
            color: var(--text-muted);
        }

        .logout-btn {
            color: #d9534f;
            text-decoration: none;
            font-weight: 600;
            margin-left: 8px;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
        }

        h2 {
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--text-color);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-muted);
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #fafafa;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.2s;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
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
            margin-top: 8px;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
        }

        .company-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .company-item:last-child {
            border-bottom: none;
        }

        .company-info a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: color 0.2s;
        }
        .company-info a:hover {
            color: var(--primary-color);
        }

        .company-meta {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
            display: flex;
            gap: 12px;
        }

        .status-tag {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .tag-default { background-color: #e9ecef; color: #495057; }
        .tag-progress { background-color: #fff3cd; color: #856404; }
        .tag-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body> <div class="container">
    <div class="header">
        <h1>就活パートナー</h1>
        <div class="user-info">
            ログイン中: <strong><?php echo htmlspecialchars($_SESSION["user_email"], ENT_QUOTES, 'UTF-8'); ?></strong>
            | <a href="logout.php" class="logout-btn">ログアウト</a>
        </div>
    </div>
    
    <div class="card">
        <h2>＋ 新規企業登録フォーム</h2>
        <form action="index.php" method="POST">
            <div class="form-group">
                <label>企業名</label>
                <input type="text" name="company_name" required placeholder="例：株式会社ワンキャリア">
            </div>
            
            <div class="form-group">
                <label>ES締切日</label>
                <input type="date" name="deadline_date">
            </div>
            
            <div class="form-group">
                <label>面接日</label>
                <input type="date" name="interview_date">
            </div>
            
            <div class="form-group">
                <label>選考ステータス</label>
                <select name="status">
                    <option value="未エントリー">未エントリー</option>
                    <option value="ES提出済">ES提出済</option>
                    <option value="1次選考中">1次選考中</option>
                    <option value="2次選考中">2次選考中</option>
                    <option value="最終選考中">最終選考中</option>
                    <option value="内定">内定</option>
                </select>
            </div>
            
            <button type="submit" name="add_company" class="submit-btn">企業を登録する</button>
        </form>
    </div>
    
    <div class="card">
        <h2>📝 エントリー企業リスト（締切順）</h2>
        
        <?php if (empty($companies)): ?>
            <p style="color: var(--text-muted); font-size: 14px; text-align: center; margin: 20px 0;">まだ登録された企業はありません。</p>
        <?php else: ?>
            <?php foreach ($companies as $company): 
                $status = $company['status'];
                $tag_class = 'tag-default';
                if (strpos($status, '選考中') !== false || $status === 'ES提出済') {
                    $tag_class = 'tag-progress';
                } elseif ($status === '内定') {
                    $tag_class = 'tag-success';
                }
            ?>
                <div class="company-item">
                    <div class="company-info">
                        <a href="detail.php?id=<?php echo $company['id']; ?>">
                            <?php echo htmlspecialchars($company['company_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <div class="company-meta">
                            <span>📅 ES締切: <?php echo htmlspecialchars($company['deadline_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>🗣️ 面接日: <?php echo htmlspecialchars($company['interview_date'] ?? '未定', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <span class="status-tag <?php echo $tag_class; ?>">
                        <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>