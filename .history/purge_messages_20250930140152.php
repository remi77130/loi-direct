<?php
declare(strict_types=1);
require __DIR__.'/db.php';
$mysqli->query("DELETE FROM message_trash WHERE deleted_at < (NOW() - INTERVAL 1 DAY)");
echo "ok\n";
