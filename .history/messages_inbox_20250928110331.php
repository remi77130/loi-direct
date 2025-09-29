<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require __DIR__.'/config.php'; // needed for APP_BASE
require_login();

$uid  = (int)$_SESSION['user_id'];
$sent = isset($_GET['sent']); // ?sent=1 to see messages you sent

if (!$sent) {
  $upd = $mysqli->prepare('UPDATE messages SET read_at=NOW() WHERE recipient_id=? AND read_at IS NULL');
  $upd->bind_param('i', $uid);
  $upd->execute();
  $upd->close();
}


$sql = $sent
  ? 'SELECT m.id,m.recipient_id,u.pseudo AS other,m.body,m.created_at
     FROM messages m JOIN users u ON u.id=m.recipient_id
     WHERE m.sender_id=? ORDER BY m.created_at DESC LIMIT 100'
  : 'SELECT m.id,m.sender_id,u.pseudo AS other,m.body,m.created_at
     FROM messages m JOIN users u ON u.id=m.sender_id
     WHERE m.recipient_id=? ORDER BY m.created_at DESC LIMIT 100';

$stmt = $mysqli->prepare($sql) ?: exit('Prepare failed: '.$mysqli->error);
$stmt->bind_param('i', $uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html><meta charset="utf-8">
<title>Mes messages</title>
<body style="background:#0f172a;color:#e5e7eb;font-family:system-ui">
  <div style="max-width:800px;margin:20px auto">
    <h1><?= $sent ? 'Messages envoyés' : 'Messages reçus' ?></h1>
    <p>
      <a href="?">Reçus</a> · <a href="?sent=1">Envoyés</a>
    </p>
    <?php if (!$rows): ?>
      <p>Aucun message.</p>
    <?php else: foreach ($rows as $m): ?>
      <div style="border:1px solid #334155;border-radius:10px;padding:12px;margin:10px 0;background:#111827">
        <div style="font-size:12px;color:#94a3b8">
          <?= $sent ? 'À' : 'De' ?> <strong><?= htmlspecialchars($m['other'],ENT_QUOTES) ?></strong>
          — <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at'])),ENT_QUOTES) ?>
        </div>
        <div style="white-space:pre-wrap;margin-top:6px"><?= htmlspecialchars($m['body'],ENT_QUOTES) ?></div>
      </div>
    <?php endforeach; endif; ?>
    <p><a href="<?= APP_BASE ?>/index.php" style="color:#93c5fd">← Retour</a></p>
  </div>
</body>
