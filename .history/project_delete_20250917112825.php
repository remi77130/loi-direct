<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  $_SESSION['flash_errors'] = ["Session expirée. Réessaie."];
  header('Location: index.php');
  exit;
}

$projectId = (int)($_POST['project_id'] ?? 0);
if ($projectId <= 0) {
  $_SESSION['flash_errors'] = ["Projet invalide."];
  header('Location: index.php');
  exit;
}

// Vérifier propriétaire
$stmt = $mysqli->prepare('SELECT author_id FROM law_projects WHERE id=? LIMIT 1');
$stmt->bind_param('i', $projectId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  $_SESSION['flash_errors'] = ["Projet introuvable."];
  header('Location: index.php');
  exit;
}
if ((int)$row['author_id'] !== (int)$_SESSION['user_id']) {
  $_SESSION['flash_errors'] = ["Action non autorisée."];
  header('Location: index.php');
  exit;
}

$mysqli->begin_transaction();
try {
  // Si pas de FK en cascade, on nettoie manuellement (sans erreur si 0 lignes)
  $stmt = $mysqli->prepare('DELETE FROM comments WHERE project_id=?');
  $stmt->bind_param('i', $projectId); $stmt->execute(); $stmt->close();

  $stmt = $mysqli->prepare('DELETE FROM likes WHERE project_id=?');
  $stmt->bind_param('i', $projectId); $stmt->execute(); $stmt->close();

  $stmt = $mysqli->prepare('DELETE FROM project_tags WHERE project_id=?');
  $stmt->bind_param('i', $projectId); $stmt->execute(); $stmt->close();

  // Supprimer le projet
  $stmt = $mysqli->prepare('DELETE FROM law_projects WHERE id=? AND author_id=? LIMIT 1');
  $uid  = (int)$_SESSION['user_id'];
  $stmt->bind_param('ii', $projectId, $uid);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
  $_SESSION['flash_success'] = "Projet supprimé.";
} catch (Throwable $e) {
  $mysqli->rollback();
  $_SESSION['flash_errors'] = ["Suppression impossible."];
}

$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header('Location: ' . $base . '/index.php?mine=1');
exit;
