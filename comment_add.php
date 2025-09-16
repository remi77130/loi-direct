<?php
// comment_add.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
  $_SESSION['flash_errors'] = ['Session expirée, réessaie.'];
  header('Location: index.php');
  exit;
}

$projectId = (int)($_POST['project_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
$userId = (int)$_SESSION['user_id'];

// Anti-spam tout simple (5s mini entre 2 posts)
$now = time();
if (!empty($_SESSION['last_comment_at']) && ($now - (int)$_SESSION['last_comment_at'] < 5)) {
  $_SESSION['flash_errors'] = ['Trop rapide 😅 Patiente 5 secondes fdp entre deux commentaires.'];
  header('Location: project.php?id=' . $projectId . '#comments');
  exit;
}

$errors = [];
if ($projectId <= 0) $errors[] = 'Projet invalide.';
if ($body === '') $errors[] = 'Message vide.';
if (mb_strlen($body) > 2000) $errors[] = 'Message trop long (2000 caractères max).';

// Vérifie que le projet existe et est publié
$chk = $mysqli->prepare('SELECT 1 FROM law_projects WHERE id=? AND status="published" LIMIT 1');
$chk->bind_param('i', $projectId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) $errors[] = 'Projet introuvable.';
$chk->close();

if ($errors) {
  $_SESSION['flash_errors'] = $errors;
  $_SESSION['comment_draft'] = $body;
  header('Location: project.php?id=' . $projectId . '#comments');
  exit;
}

$stmt = $mysqli->prepare('INSERT INTO comments (project_id, author_id, body) VALUES (?,?,?)');
$stmt->bind_param('iis', $projectId, $userId, $body);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
  $_SESSION['flash_errors'] = ['Échec d’enregistrement.'];
  $_SESSION['comment_draft'] = $body;
} else {
  $_SESSION['flash_success'] = 'Commentaire publié ✅';
  $_SESSION['last_comment_at'] = $now;
}

header('Location: project.php?id=' . $projectId . '#comments');
exit;
