<?php
// auth_page.php — page unique Connexion / Inscription (toggle sans changer de page)
// - Reprend la logique de login.php + register.php
// - Garde brute-force DB (ip_bans + login_attempts)
// - Auth sur users.pseudo + users.password_hash
// - CSRF: $_SESSION['csrf']
// - next= redirection safe

declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // APP_BASE

/* =========================
   Brute-force config
========================= */
const IP_WINDOW_SECONDS = 15 * 60;  // fenêtre 15 min
const IP_MAX_FAILS      = 10;       // seuil échecs
const IP_BAN_SECONDS    = 15 * 60;  // durée ban

/* =========================
   Helpers
========================= */
function app_base(): string {
  return defined('APP_BASE') ? rtrim((string)APP_BASE, '/') : '';
}

function safe_next(string $next): string {
  $next = trim($next);
  if ($next === '' || $next[0] !== '/') return '/chat_rooms.php';
  if (str_starts_with($next, '//')) return '/chat_rooms.php';
  if (preg_match('~^\s*https?://~i', $next)) return '/chat_rooms.php';
  return $next;
}

function get_client_ip(): string {
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($parts[0]);
    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
  }
  if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    return $_SERVER['REMOTE_ADDR'];
  }
  return '0.0.0.0';
}

function ip_is_banned(mysqli $db, string $ip): bool {
  $q = $db->prepare('SELECT 1 FROM ip_bans WHERE ip=? AND `until` > NOW() LIMIT 1');
  if (!$q) { error_log("ip_is_banned prepare failed: ".$db->error); return false; }
  $q->bind_param('s', $ip);
  $q->execute(); $q->store_result();
  $banned = $q->num_rows > 0;
  $q->close();
  return $banned;
}

function ban_ip(mysqli $db, string $ip, int $seconds, string $reason='too_many_failures'): void {
  $q = $db->prepare("
    INSERT INTO ip_bans (ip, `until`, reason)
    VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)
    ON DUPLICATE KEY UPDATE `until` = GREATEST(`until`, VALUES(`until`)), reason = VALUES(reason)
  ");
  if (!$q) { error_log("ban_ip prepare failed: ".$db->error); return; }
  $q->bind_param('sis', $ip, $seconds, $reason);
  $q->execute(); $q->close();
}

function count_recent_fails(mysqli $db, string $ip, int $windowSeconds): int {
  $q = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip=? AND success=0 AND created_at >= (NOW() - INTERVAL ? SECOND)');
  if (!$q) { error_log("count_recent_fails prepare failed: ".$db->error); return 0; }
  $q->bind_param('si', $ip, $windowSeconds);
  $q->execute(); $q->bind_result($c); $c = 0; $q->fetch(); $q->close();
  return (int)$c;
}

function log_attempt(mysqli $db, string $ip, ?string $pseudo, bool $success): void {
  $pseudo = $pseudo !== null ? mb_substr($pseudo, 0, 30) : null;
  $s = $success ? 1 : 0;
  $q = $db->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?,?,?)');
  if (!$q) { error_log("log_attempt prepare failed: ".$db->error); return; }
  $q->bind_param('ssi', $ip, $pseudo, $s);
  $q->execute(); $q->close();
}

// Fallback si random_punchline n'existe pas chez toi
if (!function_exists('random_punchline')) {
  function random_punchline(string $pseudo): string {
    $arr = [
      "Content de te revoir, $pseudo.",
      "Connexion OK $pseudo. Allez, on tchat ?",
      "$pseudo est de retour.",
    ];
    return $arr[array_rand($arr)];
  }
}

/* =========================
   CSRF
========================= */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* =========================
   Déjà connecté ?
========================= */
if (!empty($_SESSION['user_id'])) {
  header('Location: '.app_base().'/index.php', true, 303);
  exit;
}

/* =========================
   next + view
========================= */
$next = safe_next((string)($_GET['next'] ?? ($_POST['next'] ?? '/chat_rooms.php')));
$view = (string)($_GET['view'] ?? ($_POST['view'] ?? 'login'));
$active = ($view === 'register') ? 'register' : 'login';

/* =========================
   State
========================= */
$errors = ['login' => [], 'register' => []];
$blocked = false;
$ip = get_client_ip();

/* =========================
   Pré-vérifs brute-force (uniquement pour LOGIN)
========================= */
if ($active === 'login') {
  if (ip_is_banned($mysqli, $ip)) {
    $blocked = true;
    $errors['login'][] = "Trop de tentatives. Réessaie plus tard.";
  } else {
    $recentFails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
    if ($recentFails >= IP_MAX_FAILS) {
      ban_ip($mysqli, $ip, IP_BAN_SECONDS);
      $blocked = true;
      $errors['login'][] = "Trop de tentatives. Réessaie plus tard.";
    }
  }
}

/* =========================
   POST handling
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? 'login');
  $active = ($action === 'register') ? 'register' : 'login';

  $csrf = (string)($_POST['csrf'] ?? '');
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[$active][] = "Session expirée. Recharge la page.";
  } else {

    /* ---------- REGISTER ---------- */
    if ($active === 'register') {
      $pseudo = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 20);
      $pass1  = (string)($_POST['password'] ?? '');
      $pass2  = (string)($_POST['password_confirm'] ?? '');

      if (!preg_match('/^[\p{L}0-9_.-]{3,20}$/u', $pseudo)) {
        $errors['register'][] = "Le pseudo doit faire 3 à 20 caractères (lettres avec accents, chiffres, . _ -, pas d’espace).";
      }

      $len = mb_strlen($pass1);
      if ($len < 8 || $len > 128) {
        $errors['register'][] = "Mot de passe : 8 à 128 caractères.";
      }
      if ($pass1 !== $pass2) {
        $errors['register'][] = "Les mots de passe ne correspondent pas.";
      }

      // Pseudo disponible ?
      if (!$errors['register']) {
        $chk = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo=? LIMIT 1');
        if (!$chk) {
          $errors['register'][] = "Erreur serveur.";
          error_log('register check prepare: '.$mysqli->error);
        } else {
          $chk->bind_param('s', $pseudo);
          if (!$chk->execute()) {
            $errors['register'][] = "Erreur serveur.";
            error_log('register check execute: '.$chk->error);
          } else {
            $chk->store_result();
            if ($chk->num_rows > 0) {
              $errors['register'][] = "Ce pseudo est déjà pris.";
            }
          }
          $chk->close();
        }
      }

      // Création
      if (!$errors['register']) {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $ins = $mysqli->prepare('INSERT INTO users (pseudo, password_hash) VALUES (?, ?)');
        if (!$ins) {
          $errors['register'][] = "Erreur serveur.";
          error_log('register insert prepare: '.$mysqli->error);
        } else {
          $ins->bind_param('ss', $pseudo, $hash);
          if ($ins->execute()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$ins->insert_id;
            $_SESSION['pseudo']  = $pseudo;
            $_SESSION['flash_success'] = random_punchline($pseudo);

            header('Location: '.app_base().$next, true, 303);
            exit;
          } else {
            $errors['register'][] = "Erreur serveur.";
            error_log('register insert execute: '.$ins->error);
          }
          $ins->close();
        }
      }
    }

    /* ---------- LOGIN ---------- */
    if ($active === 'login') {
      // brute-force gate
      if (ip_is_banned($mysqli, $ip)) {
        $blocked = true;
        $errors['login'][] = "Trop de tentatives. Réessaie plus tard.";
      } else {
        $recentFails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
        if ($recentFails >= IP_MAX_FAILS) {
          ban_ip($mysqli, $ip, IP_BAN_SECONDS);
          $blocked = true;
          $errors['login'][] = "Trop de tentatives. Réessaie plus tard.";
        }
      }

      if (!$blocked) {
        $pseudo_raw   = (string)($_POST['pseudo'] ?? '');
        $password_raw = (string)($_POST['password'] ?? '');

        $pseudo   = mb_substr(trim($pseudo_raw), 0, 100);
        $password = $password_raw;

        if (!preg_match('/^[\p{L}0-9_.-]{3,20}$/u', $pseudo)) {
          $errors['login'][] = 'Identifiants invalides.';
        }

        $pwLen = mb_strlen($password);
        if ($pwLen < 8 || $pwLen > 128) {
          $errors['login'][] = 'Identifiants invalides.';
        }

        if (!$errors['login']) {
          $st = $mysqli->prepare('SELECT id, password_hash FROM users WHERE pseudo=? LIMIT 1');
          if (!$st) {
            error_log('login prepare failed: '.$mysqli->error);
            $errors['login'][] = 'Erreur serveur. Réessaie plus tard.';
          } else {
            $st->bind_param('s', $pseudo);

            if (!$st->execute()) {
              error_log('login execute failed: '.$st->error);
              $errors['login'][] = 'Erreur serveur. Réessaie plus tard.';
              $st->close();
            } else {
              $res = null;
              if (method_exists($st, 'get_result')) {
                $res = $st->get_result();
                if ($res === false) {
                  error_log('login get_result returned false: '.$st->error);
                  $errors['login'][] = 'Erreur serveur. Réessaie plus tard.';
                }
              }

              $row = null;
              if (!$errors['login']) {
                if ($res !== null) {
                  $row = $res->fetch_assoc();
                } else {
                  $st->bind_result($tmp_id, $tmp_hash);
                  if ($st->fetch()) {
                    $row = ['id' => $tmp_id, 'password_hash' => $tmp_hash];
                  }
                }
              }

              $st->close();

              if (!$errors['login']) {
                $ok = $row && is_string($row['password_hash']) && password_verify($password, $row['password_hash']);

                log_attempt($mysqli, $ip, $pseudo, $ok);

                if ($ok) {
                  if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                    $new = password_hash($password, PASSWORD_DEFAULT);
                    $up  = $mysqli->prepare('UPDATE users SET password_hash=? WHERE id=?');
                    if (!$up) {
                      error_log('password rehash prepare failed: '.$mysqli->error);
                    } else {
                      $uid = (int)$row['id'];
                      $up->bind_param('si', $new, $uid);
                      if (!$up->execute()) {
                        error_log('password rehash execute failed: '.$up->error);
                      }
                      $up->close();
                    }
                  }

                  session_regenerate_id(true);
                  $_SESSION['user_id'] = (int)$row['id'];
                  $_SESSION['pseudo']  = $pseudo;
                  $_SESSION['flash_success'] = random_punchline($pseudo);

                  header('Location: '.app_base().$next, true, 303);
                  exit;
                }

                $fails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
                if ($fails >= IP_MAX_FAILS) {
                  ban_ip($mysqli, $ip, IP_BAN_SECONDS);
                }

                $errors['login'][] = 'Pseudo ou mot de passe invalide.';
                usleep(200000);
              }
            }
          }
        }
      }
    }
  }
}

/* =========================
   GC probabiliste (comme login.php)
========================= */
const LOGIN_GC_NUM = 1;
const LOGIN_GC_DEN = 100;

if (random_int(1, LOGIN_GC_DEN) <= LOGIN_GC_NUM) {
  $sql = "DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 30 DAY)";
  if (!$mysqli->query($sql)) {
    error_log('login GC failed: '.$mysqli->error);
  }
}

/* =========================
   Valeurs affichage
========================= */
$postedPseudo = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 20);
?>