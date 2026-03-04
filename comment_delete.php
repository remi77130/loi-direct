<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: feed.php'); exit; }
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
  $_SESSION['flash_errors'] = ['Session expirée.'];
  header('Location: feed.php'); exit;
}

$cid = (int)($_POST['comment_id'] ?? 0);
$pid = (int)($_POST['project_id'] ?? 0);
$uid = (int)$_SESSION['user_id'];

// Vérifie l'appartenance
$chk = $mysqli->prepare('SELECT author_id FROM comments WHERE id=? AND project_id=?');
$chk->bind_param('ii', $cid, $pid);
$chk->execute();
$chk->bind_result($authorId);
$found = $chk->fetch();
$chk->close();

if (!$found) {
  $_SESSION['flash_errors'] = ['Commentaire introuvable.'];
} elseif ($authorId !== $uid) {
  $_SESSION['flash_errors'] = ['Action non autorisée.'];
} else {
  $del = $mysqli->prepare('DELETE FROM comments WHERE id=?');
  $del->bind_param('i', $cid);
  $del->execute();
  $del->close();
  $_SESSION['flash_success'] = 'Commentaire supprimé.';
}

header('Location: project.php?id=' . $pid . '#comments');
exit;
