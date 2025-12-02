<?php
declare(strict_types=1);

/**
 * Snippet réutilisable :
 * - inclus dans login.php / register.php
 * - appelé en AJAX (fetch) pour rafraîchir les rooms
 */

// On ne relance pas la session si elle existe déjà
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// On ne recharge db.php / config.php que si nécessaire
if (!isset($mysqli)) {
    require __DIR__ . '/db.php';
}
if (!defined('APP_BASE')) {
    require __DIR__ . '/config.php';
}

// --- Récup rooms publiques ---
$rooms = [];

$sql = "
  SELECT id, name
  FROM chat_rooms
  WHERE is_private = 0
  ORDER BY created_at DESC
  LIMIT 8
";

if ($res = $mysqli->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $roomId = (int)$row['id'];
        $rooms[$roomId] = [
            'id'       => $roomId,
            'name'     => $row['name'],
            'messages' => [],
        ];
    }
    $res->free();
}

if ($rooms) {
    $stmt = $mysqli->prepare("
        SELECT 
            m.room_id,
            m.body,
            m.created_at,
            m.color,
            m.like_count,
            m.file_url,
            m.file_mime,
            m.file_w,
            m.file_h,
            u.pseudo
        FROM chat_messages m
        LEFT JOIN users u ON u.id = m.sender_id
        WHERE m.room_id = ?
        ORDER BY m.id DESC
        LIMIT 5
    ");

    if ($stmt) {
        foreach ($rooms as $roomId => &$room) {
            $stmt->bind_param('i', $roomId);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                $msgList = [];
                while ($row = $res->fetch_assoc()) {
                    $msgList[] = $row;
                }
                // ancien -> récent
                $room['messages'] = array_reverse($msgList);

                // garder seulement les salons qui ont au moins 1 message
                if (empty($msgList)) {
                    unset($rooms[$roomId]);
                }
            }
        }
        unset($room);
        $stmt->close();
    }
}
?>

<section class="public-rooms">
  <h2>Rooms actifs</h2>
  <p>
    Découvre quelques salons publics de Tchat-Direct.
    La lecture est libre, pour participer il suffit de te connecter ou de créer un compte gratuit.
  </p>

  <?php if (empty($rooms)): ?>
    <p class="public-rooms__empty">
      Aucun salon public disponible pour le moment.
    </p>
  <?php else: ?>
    <?php foreach ($rooms as $room): ?>

      <?php      // 1) description courte = premier message tronqué
        // 1) Infos de base à partir du premier message de la room
      $firstMsg     = '';
      $firstAuthor  = '';
      $firstDate    = '';

      if (!empty($room['messages'])) {
          $first          = $room['messages'][0];
          $firstMsg       = mb_substr(strip_tags($first['body'] ?? ''), 0, 300); // résumé
          $firstAuthor    = $first['pseudo'] ?? 'Anonyme';
          $firstDate      = $first['created_at'] ?? ''; // ex: 2025-11-25 15:30:00
      }

      // 2) URL "canonique" de la room
      $roomSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $room['name']), '-'));
      $roomUrl  = rtrim(APP_BASE, '/') . '/room.php?id=' . $room['id'] . '&slug=' . $roomSlug;

      // 3) date du dernier message (pour dateModified)
      $lastDate = '';
      if (!empty($room['messages'])) {
          $last = end($room['messages']);
          $lastDate = $last['created_at'] ?? ''; // tu peux garder que la date si tu veux
      }

      // 4) JSON-LD pour cette room (DiscussionForumPosting complet)
      $ld = [
          '@context'  => 'https://schema.org',
          '@type'     => 'DiscussionForumPosting',
          'headline'  => 'Room - ' . $room['name'],
          'name'      => $room['name'],
          'url'       => $roomUrl,
          'publisher' => [
              '@type' => 'Organization',
              'name'  => 'Tchat-Direct',
          ],
      ];

      // Contenu principal du post
      if ($firstMsg !== '') {
          $ld['text']        = $firstMsg;
          $ld['description'] = $firstMsg;
      }

      // Auteur du sujet
      if ($firstAuthor !== '') {
          $ld['author'] = [
              '@type' => 'Person',
              'name'  => $firstAuthor,
          ];
      }

      // Date de publication (premier message)
      if ($firstDate !== '') {
          // Idéalement au format ISO 8601, mais GSC acceptera déjà ça
          $ld['datePublished'] = $firstDate;
      }

      // Date de dernière activité
      if ($lastDate !== '') {
          $ld['dateModified'] = $lastDate;
      }

      ?>

      <article class="public-room">

        <!-- JSON-LD spécifique à cette room -->
        <script type="application/ld+json">
<?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>

        </script>

        <h2 class="public-room__title">
          Room - <?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <div class="public-room__messages">
          <?php if (empty($room['messages'])): ?>
            <p class="public-room__empty-msg">
              Pas encore de messages dans ce salon.
            </p>
          <?php else: ?>
            <ul class="public-room__list">
              <?php foreach ($room['messages'] as $msg): ?>
                <?php
                  $color = $msg['color'] ?? '';
                  $style = '';
                  if ($color !== '') {
                      $style = ' style="color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"';
                  }

                  $likes = isset($msg['like_count']) ? (int)$msg['like_count'] : 0;

             $fileUrl  = $msg['file_url']  ?? '';
$fileMime = $msg['file_mime'] ?? '';
$fileW    = isset($msg['file_w']) ? (int)$msg['file_w'] : 0;
$fileH    = isset($msg['file_h']) ? (int)$msg['file_h'] : 0;
$isImage  = $fileUrl !== '' && (
    $fileMime === '' || strpos($fileMime, 'image/') === 0
);

                ?>
                <li class="public-room__item">
                  <div class="public-room__meta">
                    <strong class="public-room__author">
                      <?= htmlspecialchars($msg['pseudo'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                    <span class="public-room__sep">·</span>
                    <time datetime="<?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?>
                    </time>

                    <?php if ($likes > 0): ?>
                      <span class="public-room__likes">
                        ❤️ <?= $likes ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="public-room__body"<?= $style ?>>
                    <?= nl2br(htmlspecialchars($msg['body'], ENT_QUOTES, 'UTF-8')) ?>
                  </div>

                  <?php if ($isImage): ?>
                    <figure class="public-room__image-wrap">
                      <img
                        src="<?= htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Image envoyée dans le salon"
                        class="public-room__image"
                        <?php if ($fileW > 0 && $fileH > 0): ?>
                          width="<?= $fileW ?>" height="<?= $fileH ?>"
                        <?php endif; ?>
                      >
                    </figure>

                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

                            <a href="#header">Répondre </a>


      </article>

      <div class="public-room__cta">
  <a href="<?= htmlspecialchars($roomUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
    Voir le salon complet
  </a>
</div>

    <?php endforeach; ?>
  <?php endif; ?>
</section>
