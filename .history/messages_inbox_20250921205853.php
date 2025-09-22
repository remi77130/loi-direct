<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

$uid = (int)$_SESSION['user_id'];

// marquer lu (optionnel: quand on ouvre la page)
$upd = $mysqli->prepare('UPDATE messages SET read_at=NOW() WHERE recipient_id=? AND read_at IS NULL');
$upd->bind_param('i', $uid); $upd->execute(); $upd->close();

$res = $mysqli->prepare('
  SELECT m.id, m.sender_id, u.pseudo AS sender, m.body, m.created_at
  FROM messages m JOIN users u ON u.id=m.sender_id
  WHERE m.recipient_id=?
  ORDER BY m.created_at DESC
  LIMIT 100
');
$res->bind_param('i', $uid);
$res->execute();
$rows = $res->get_result()->fetch_all(MYSQLI_ASSOC);
$res->close();
?>
<!doctype html><meta charset="utf-8">
<title>Mes messages</title>
<body style="background:#0f172a;color:#e5e7eb;font-family:system-ui">
  <div style="max-width:800px;margin:20px auto">
    <h1>Messages reçus</h1>
    <?php if (!$rows): ?>
      <p>Aucun message.</p>
    <?php else: foreach ($rows as $m): ?>
      <div style="border:1px solid #334155;border-radius:10px;padding:12px;margin:10px 0;background:#111827">
        <div style="font-size:12px;color:#94a3b8">
          De <strong><?= htmlspecialchars($m['sender'],ENT_QUOTES) ?></strong> — <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at'])),ENT_QUOTES) ?>
        </div>
        <div style="white-space:pre-wrap;margin-top:6px"><?= htmlspecialchars($m['body'],ENT_QUOTES) ?></div>
      </div>
    <?php endforeach; endif; ?>
    <p><a href="<?= APP_BASE ?>/index.php" style="color:#93c5fd">← Retour</a></p>
  </div>
</body>
