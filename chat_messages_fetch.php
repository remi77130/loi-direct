<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';      // Connexion MySQLi ($mysqli)
require __DIR__.'/auth.php';    // Gestion de session et accès
require_login();                // Redirige ou bloque si utilisateur non connecté

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* ============================================================
 *   1. VALIDATION DES PARAMÈTRES D’ENTRÉE
 * ============================================================
 *  - room_id : identifiant du salon (obligatoire)
 *  - after_id : id du dernier message reçu par le client
 *               → permet un rafraîchissement incrémental
 * ============================================================ */
$room_id = max(1, (int)($_GET['room_id'] ?? 0));
$after   = max(0, (int)($_GET['after_id'] ?? 0));

/* ============================================================
 *   2. VALIDATION DU SALON
 * ============================================================
 * Vérifie que le salon existe et que l’utilisateur y a accès.
 * (Ici, seuls les salons publics sont retournés ; on bloque les
 *  salons privés pour éviter une fuite d’informations.)
 * ============================================================ */
$chk = $mysqli->prepare("
  SELECT id, name, is_private
  FROM chat_rooms
  WHERE id = ?
  LIMIT 1
");
$chk->bind_param('i', $room_id);
$chk->execute();
$room = $chk->get_result()->fetch_assoc();
$chk->close();

/* Si le salon n’existe pas → 404 */
if (!$room) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'room']);
  exit;
}

/* Si le salon est privé → 403 (interdit) */
if ((int)$room['is_private'] === 1) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'private']);
  exit;
}

/* ============================================================
 *   3. RÉCUPÉRATION DES MESSAGES
 * ============================================================
 * Deux cas :
 *   - (a) Rechargement incrémental (after_id > 0)
 *          → on renvoie seulement les messages récents
 *   - (b) Première ouverture (after_id = 0)
 *          → on renvoie les 50 derniers messages du salon
 * ============================================================ */
if ($after > 0) {
  // Cas (a) : fetch incrémental
  $sql = "
    SELECT
      m.id,
      m.body,
      m.created_at,
      m.file_url,
      m.file_mime,
      u.pseudo AS sender
    FROM chat_messages m
      JOIN users u ON u.id = m.sender_id
    WHERE m.room_id = ? AND m.id > ?
    ORDER BY m.id ASC
    LIMIT 200
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $room_id, $after);
} else {
  // Cas (b) : première récupération complète (50 derniers messages)
  $sql = "
    SELECT
      m.id,
      m.body,
      m.created_at,
      m.file_url,
      m.file_mime,
      u.pseudo AS sender
    FROM chat_messages m
      JOIN users u ON u.id = m.sender_id
    WHERE m.room_id = ?
    ORDER BY m.id DESC
    LIMIT 50
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $room_id);
}

/* Exécution et récupération */
$stmt->execute();
$res  = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Si c’est une première requête, on renvoie les messages
   du plus ancien au plus récent pour un affichage logique. */
if ($after === 0) {
  $rows = array_reverse($rows);
}

/* ============================================================
 *   4. STRUCTURE DE SORTIE JSON
 * ============================================================
 * Exemple :
 * {
 *   "ok": true,
 *   "messages": [
 *     {
 *       "id": 52,
 *       "sender": "Alice",
 *       "body": "Salut tout le monde",
 *       "created_at": "2025-10-31 14:22:41",
 *       "file_url": "https://...",
 *       "file_mime": "image/jpeg"
 *     }
 *   ]
 * }
 * ============================================================ */
echo json_encode(['ok'=>true, 'messages'=>$rows], JSON_UNESCAPED_UNICODE);
