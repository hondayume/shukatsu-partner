<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$current_user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_option'])) {
    $type = $_POST['type']; // 'status' または 'exam'
    $option_name = trim($_POST['option_name']);

    if (!empty($option_name)) {
        if ($type === 'status') {
            $stmt = $pdo->prepare("INSERT INTO status_options (user_id, option_name) VALUES (:u, :name)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO exam_options (user_id, option_name) VALUES (:u, :name)");
        }
        $stmt->execute([':u' => $current_user_id, ':name' => $option_name]);
    }
    header("Location: settings.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_option'])) {
    $type = $_POST['type'];
    $option_id = $_POST['option_id'];

    if ($type === 'status') {
        $stmt = $pdo->prepare("DELETE FROM status_options WHERE id = :id AND user_id = :u");
    } else {
        $stmt = $pdo->prepare("DELETE FROM exam_options WHERE id = :id AND user_id = :u");
    }
    $stmt->execute([':id' => $option_id, ':u' => $current_user_id]);
    header("Location: settings.php");
    exit;
}

$status_stmt = $pdo->prepare("SELECT * FROM status_options WHERE user_id = :u ORDER BY id ASC");
$status_stmt->execute([':u' => $current_user_id]);
$status_options = $status_stmt->fetchAll();

$exam_stmt = $pdo->prepare("SELECT * FROM exam_options WHERE user_id = :u ORDER BY id ASC");
$exam_stmt->execute([':u' => $current_user_id]);
$exam_options = $exam_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>就活パートナー - 設定画面</title>
    <style>
        :root { --bg-color: #fcfcfc; --card-bg: #ffffff; --text-color: #222; --text-muted: #64748b; --border-color: #e2e8f0; --primary-color: #4f46e5; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg-color); color: var(--text-color); margin: 0; padding: 0; display: flex; }
        
        .sidebar { width: 240px; background: #f8fafc; border-right: 1px solid var(--border-color); height: 100vh; position: fixed; padding: 24px 16px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar-logo { font-size: 18px; font-weight: bold; margin-bottom: 32px; display: flex; align-items: center; gap: 8px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-item a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #334155; text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 14px; margin-bottom: 4px; }
        .sidebar-item.active a { background: #fee2e2; color: #b91c1c; font-weight: bold; }
        .sidebar-item.room a { background: #dcfce7; color: #15803d; font-weight: bold; }
        .sidebar-item a:hover { opacity: 0.8; }

        .main-layout { margin-left: 240px; padding: 40px; width: 100%; box-sizing: border-box; }
        .card { background: var(--card-bg); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); border: 1px solid var(--border-color); margin-bottom: 24px; }
        
        .flex-container { display: flex; gap: 24px; }
        .flex-child { flex: 1; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; }
        
        h3 { margin-top: 0; font-size: 15px; color: #1e293b; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; }
        
        input[type="text"] { border: 1px solid var(--border-color); background: #f8fafc; font-size: 13px; padding: 6px 8px; border-radius: 6px; box-sizing: border-box; width: 70%; }
        input:focus { border-color: var(--primary-color); background: #fff; outline: none; }
        
        .add-btn { background: var(--primary-color); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; }
        
        .option-list { list-style: none; padding: 0; margin: 16px 0 0 0; }
        .option-item { display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .option-item:hover { background: #f8fafc; }
        
        .delete-btn { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 12px; padding: 4px 8px; border-radius: 4px; }
        .delete-btn:hover { color: #ef4444; background: #fee2e2; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="sidebar-logo">🚀 就活パートナー</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item"><a href="index.php">🏠 企業管理ホーム</a></li>
            <li class="sidebar-item room"><a href="industry_index.php">📁 業界研究ルーム</a></li>
            <li class="sidebar-item active"><a href="settings.php">⚙️ 選択肢カスタム設定</a></li>
        </ul>
    </div>
    <div class="sidebar-menu">
        <li class="sidebar-item"><a href="logout.php" style="color:var(--text-muted);">➔ ログアウト</a></li>
    </div>
</div>

<div class="main-layout">
    <div class="card">
        <h2 style="margin: 0 0 8px 0; font-size: 18px; color: #1e293b;">⚙️ 選択肢のカスタム設定</h2>
        <p style="margin: 0; font-size: 13px; color: var(--text-muted);">メイン画面の「選考状況」と「適性検査」のドロップダウン項目を自由にカスタマイズできます。</p>
    </div>

    <div class="flex-container">
        <div class="flex-child">
            <h3>📋 選考状況の項目</h3>
            <form action="settings.php" method="POST" style="display: flex; gap: 8px;">
                <input type="hidden" name="type" value="status">
                <input type="text" name="option_name" required placeholder="例：3次選考中...">
                <button type="submit" name="add_option" class="add-btn">追加</button>
            </form>
            
            <ul class="option-list">
                <?php if (empty($status_options)): ?>
                    <li style="color: var(--text-muted); font-size: 13px; padding: 8px;">項目がありません。</li>
                <?php else: ?>
                    <?php foreach ($status_options as $opt): ?>
                        <li class="option-item">
                            <span><?php echo htmlspecialchars($opt['option_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <form action="settings.php" method="POST" onsubmit="return confirm('この項目を削除しますか？');" style="margin:0;">
                                <input type="hidden" name="type" value="status">
                                <input type="hidden" name="option_id" value="<?php echo $opt['id']; ?>">
                                <button type="submit" name="delete_option" class="delete-btn">削除</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="flex-child">
            <h3>📝 適性検査の項目</h3>
            <form action="settings.php" method="POST" style="display: flex; gap: 8px;">
                <input type="hidden" name="type" value="exam">
                <input type="text" name="option_name" required placeholder="例：SCOA、面談...">
                <button type="submit" name="add_option" class="add-btn">追加</button>
            </form>
            
            <ul class="option-list">
                <?php if (empty($exam_options)): ?>
                    <li style="color: var(--text-muted); font-size: 13px; padding: 8px;">項目がありません。</li>
                <?php else: ?>
                    <?php foreach ($exam_options as $opt): ?>
                        <li class="option-item">
                            <span><?php echo htmlspecialchars($opt['option_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <form action="settings.php" method="POST" onsubmit="return confirm('この項目を削除しますか？');" style="margin:0;">
                                <input type="hidden" name="type" value="exam">
                                <input type="hidden" name="option_id" value="<?php echo $opt['id']; ?>">
                                <button type="submit" name="delete_option" class="delete-btn">削除</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="index.php" style="color: var(--primary-color); font-size: 14px; text-decoration: none; font-weight: bold;">➔ ホーム（企業管理）に戻る</a>
    </div>
</div>

</body>
</html>