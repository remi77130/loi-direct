CREATE TABLE chat_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
  user_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  room_id INT UNSIGNED DEFAULT NULL,
  message_id BIGINT UNSIGNED DEFAULT NULL,
  type ENUM('mention','dm') NOT NULL DEFAULT 'mention',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  KEY idx_user_read (user_id, is_read, created_at),
  KEY idx_room (room_id),
  KEY idx_message (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;