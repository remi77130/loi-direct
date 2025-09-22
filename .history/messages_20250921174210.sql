CREATE TABLE messages (
  id           BIGINT AUTO_INCREMENT PRIMARY KEY,
  sender_id    INT NOT NULL,
  recipient_id INT NOT NULL,
  body         TEXT NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at      DATETIME DEFAULT NULL,
  INDEX (recipient_id, read_at),
  INDEX (sender_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
