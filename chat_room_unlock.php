<?php

/**
 * chat_room_unlock.php
 * Vérifie le mot de passe d’un salon privé et ouvre l’accès pour la session.
 * Entrée :
 *  - POST[room_id], POST[password], POST[csrf]
 * Effet :
 *  - Marque la session : $_SESSION['rooms_ok'][room_id] = true
 * Sortie :
 *  - { ok:true } ou { ok:false, error:'bad_password' | 'locked' | 'csrf' }
 * Défense :
 *  - 3 tentatives/minute par room_id (exemple simple côté session)
 */
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

// Vérifie le mot de passe et mémorise l’accès en session.

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }

$room_id = (int)($_POST['room_id'] ?? 0);
$pwd = (string)($_POST['password'] ?? '');

$st = $mysqli->prepare("SELECT is_private, password_hash FROM chat_rooms WHERE id=?");
$st->bind_param('i', $room_id);
$st->execute();
$st->bind_result($is_private, $hash);
if (!$st->fetch()) { $st->close(); http_response_code(404); echo json_encode(['ok'=>false,'error'=>'notfound']); exit; }
$st->close();

if ((int)$is_private !== 1) { echo json_encode(['ok'=>true]); exit; } // pas protégé

// anti-bruteforce simple: 5 essais / 10 min / salon + IP
$k = 'unlock_'.$room_id.'_'.($_SERVER['REMOTE_ADDR'] ?? 'na');
$_SESSION['tries'][$k] = $_SESSION['tries'][$k] ?? ['n'=>0,'t'=>time()];
$w = 600; // 10 min
if (time() - $_SESSION['tries'][$k]['t'] > $w) $_SESSION['tries'][$k] = ['n'=>0,'t'=>time()];
if ($_SESSION['tries'][$k]['n'] >= 5) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'too_many']); exit; }

if (!$hash || !password_verify($pwd, $hash)) {
  $_SESSION['tries'][$k]['n']++;
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'bad_password']);
  exit;
}

// OK: marque l’accès en session
$_SESSION['rooms_ok'] = $_SESSION['rooms_ok'] ?? [];
$_SESSION['rooms_ok'][$room_id] = true;
echo json_encode(['ok'=>true]);
