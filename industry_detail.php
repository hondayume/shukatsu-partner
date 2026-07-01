<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$industry_id = $_GET['id'] ?? null;
if (!$industry_id) {
    header("Location: industry_index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_industry_detail'])) {
    $sql = "UPDATE industries SET 
                overview = :overview, business_model = :business_model, market_size = :market_size,
                growth = :growth, roles = :roles, products = :products, trends = :trends,
                issues = :issues, future_outlook = :future_outlook, memo = :memo
            WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':overview', $_POST['overview'], PDO::PARAM_STR);
    $stmt->bindValue(':business_model', $_POST['business_model'], PDO::PARAM_STR);
    $stmt->bindValue(':market_size', $_POST['market_size'], PDO::PARAM_STR);
    $stmt->bindValue(':growth', $_POST['growth'], PDO::PARAM_STR);
    $stmt->bindValue(':roles', $_POST['roles'], PDO::PARAM_STR);
    $stmt->bindValue(':products', $_POST['products'], PDO::PARAM_STR);
    $stmt->bindValue(':trends', $_POST['trends'], PDO::PARAM_STR);
    $stmt->bindValue(':issues', $_POST['issues'], PDO::PARAM_STR);
    $stmt->bindValue(':future_outlook', $_POST['future_outlook'], PDO::PARAM_STR);
    $stmt->bindValue(':memo', $_POST['memo'], PDO::PARAM_STR);
    $stmt->bindValue(':id', $industry_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
    header("Location: industry_detail.php?id=" . $industry_id);
    exit;
}

$sql = "SELECT * FROM industries WHERE id = :id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $industry_id, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$industry = $stmt->fetch();

if (!$industry) {
    die("業界が見つかりません。");
}

$sql = "SELECT id, company_name FROM companies WHERE industry_id = :industry_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':industry_id', $industry_id, PDO::PARAM_INT);
$stmt->execute();
$related_companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($industry['industry_name'], ENT_QUOTES, 'UTF-8'); ?> - 業界研究</title>
    <style>
        :root { --bg-color: #ffffff; --side-bg: #f8f9fa; --border-color: #edeef0; --primary-color: #4a90e2; }
        body { font-family: -apple-system, sans-serif; margin: 0; padding: 0; display: flex; background: var(--bg-color); color: #333; }
        
        .sidebar { width: 260px; background: var(--side-bg); padding: 30px 20px; border-right: 1px solid var(--border-color); position: fixed; height: 100vh; overflow-y: auto; box-sizing: border-box; }
        .main-content { margin-left: 260px; padding: 40px 60px; width: 100%; max-width: 800px; box-sizing: border-box; }
        
        .industry-title { font-size: 32px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .meta-info { font-size: 14px; color: #666; margin-bottom: 30px; display: flex; gap: 15px; }
        .meta-info a { color: var(--primary-color); text-decoration: none; }

        .toc-title { font-weight: bold; font-size: 14px; margin-bottom: 12px; color: #555; display: flex; align-items: center; gap: 6px; }
        .toc-list { list-style: none; padding: 0; margin: 0; }
        .toc-list li { margin-bottom: 8px; }
        .toc-list a { text-decoration: none; color: #666; font-size: 13px; transition: color 0.2s; }
        .toc-list a:hover { color: #000; text-decoration: underline; }

        .section-block { background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 24px; margin-bottom: 24px; scroll-margin-top: 20px; }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: #222; }
        
        textarea { width: 100%; height: 80px; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px; resize: vertical; box-sizing: border-box; font-family: inherit; }
        textarea:focus { border-color: var(--primary-color); outline: none; background: #fff; }
        
        .floating-save { position: fixed; bottom: 30px; right: 30px; background: #4bc4aa; color: white; border: none; padding: 14px 28px; border-radius: 30px; font-weight: bold; font-size: 15px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .floating-save:hover { background: #3ba38c; }
    </style>
</head>
<body>

<div class="sidebar">
    <div style="margin-bottom: 24px;"><a href="industry_index.php" style="text-decoration:none; color:var(--primary-color); font-size:13px;">◀ 業界一覧に戻る</a></div>
    
    <div class="toc-title">📋 目次</div>
    <ul class="toc-list">
        <li><a href="#overview">業界概要</a></li>
        <li><a href="#business_model">ビジネスモデル</a></li>
        <li><a href="#market">市場規模・成長性</a></li>
        <li><a href="#roles">業界の役割・主要サービス</a></li>
        <li><a href="#trends">トレンド・課題・将来性</a></li>
        <li><a href="#memo">その他メモ欄</a></li>
    </ul>

    <hr style="border:0; border-top:1px solid var(--border-color); margin:20px 0;">

    <div class="toc-title">↗ 選考企業</div>
    <ul class="toc-list" style="color: #444; font-size: 13px; padding-left: 5px;">
        <?php if(empty($related_companies)): ?>
            <li style="color:#999; font-style:italic;">紐付け企業なし</li>
        <?php else: ?>
            <?php foreach($related_companies as $comp): ?>
                <li>🏢 <a href="detail.php?id=<?php echo $comp['id']; ?>" style="color:var(--primary-color);"><?php echo htmlspecialchars($comp['company_name'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<div class="main-content">
    <h1 class="industry-title"><?php echo htmlspecialchars($industry['industry_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <div class="meta-info">
        <span>ユーザー設定ページ</span>
    </div>

    <form action="industry_detail.php?id=<?php echo $industry_id; ?>" method="POST">
        
        <div id="overview" class="section-block">
            <div class="section-title">🌟 業界概要</div>
            <p style="font-size:13px; color:#666; margin-top:0;">◎ この業界は何をしている業界か</p>
            <textarea name="overview" placeholder="例: インターネットを通じたサービスやシステム開発、インフラ提供を行う業界..."><?php echo htmlspecialchars($industry['overview'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div id="business_model" class="section-block">
            <div class="section-title">📊 ビジネスモデル</div>
            <p style="font-size:13px; color:#666; margin-top:0;">◎ 何を売っているのか / 誰が買うのか / どうやって利益を出すのか</p>
            <textarea name="business_model" placeholder="例: BtoBのSaaS型システム提供。月額サブスクリプションによる安定収益。"><?php echo htmlspecialchars($industry['business_model'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div id="market" class="section-block">
            <div class="section-title">📈 市場規模・成長性</div>
            <div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                <span style="font-size:13px; color:#666; width:80px;">■ 市場規模:</span>
                <input type="text" name="market_size" value="<?php echo htmlspecialchars($industry['market_size'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="padding:6px; border:1px solid var(--border-color); border-radius:4px; flex:1;">
            </div>
            <p style="font-size:13px; color:#666; margin-bottom:4px;">■ 企業の成長性・データ:</p>
            <textarea name="growth" placeholder="例: DX需要の拡大に伴い、市場は年利〇%で急成長中。"><?php echo htmlspecialchars($industry['growth'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div id="roles" class="section-block">
            <div class="section-title">🛠️ 業界の役割・主要サービス</div>
            <p style="font-size:13px; color:#666; margin-top:0;">■ 業界の役割:</p>
            <textarea name="roles" style="margin-bottom:10px;" placeholder="例: 社会のあらゆる手続きや業務効率化をシステムで支える役割。"><?php echo htmlspecialchars($industry['roles'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p style="font-size:13px; color:#666; margin-top:0;">■ 代表的な製品 / サービス:</p>
            <textarea name="products" placeholder="例: 基幹システム、クラウド会計ソフト、受託開発システムなど。"><?php echo htmlspecialchars($industry['products'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div id="trends" class="section-block">
            <div class="section-title">🚀 トレンド・課題・将来性</div>
            <p style="font-size:13px; color:#666; margin-top:0;">■ トレンド:</p>
            <textarea name="trends" style="margin-bottom:10px;" placeholder="例: 生成AIの業務組み込み、ノーコードツールの普及。"><?php echo htmlspecialchars($industry['trends'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p style="font-size:13px; color:#666; margin-top:0;">■ 課題:</p>
            <textarea name="issues" style="margin-bottom:10px;" placeholder="例: IT人材の慢性的な不足、セキュリティリスクの増大。"><?php echo htmlspecialchars($industry['issues'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p style="font-size:13px; color:#666; margin-top:0;">■ 将来性:</p>
            <textarea name="future_outlook" placeholder="例: AIや自動化が進むため形を変えながらも必須の業界であり続ける。"><?php echo htmlspecialchars($industry['future_outlook'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div id="memo" class="section-block">
            <div class="section-title">💡 その他メモ欄</div>
            <textarea name="memo" style="height:120px;" placeholder="志望動機への活かし方や、ニュースのメモなど自由にどうぞ。"><?php echo htmlspecialchars($industry['memo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <button type="submit" name="save_industry_detail" class="floating-save">Changes を保存</button>
    </form>
</div>

</body>
</html>