-- chat_messages
CREATE TABLE IF NOT EXISTS chat_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id    INT UNSIGNED NOT NULL,
  sender_id  INT UNSIGNED NOT NULL,
  body       TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_room_created (room_id, created_at),
  INDEX idx_room_id (room_id),
  CONSTRAINT fk_chat_messages_room   FOREIGN KEY (room_id)   REFERENCES chat_rooms(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_chat_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
