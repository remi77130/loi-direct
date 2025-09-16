USE loi_direct;

CREATE TABLE IF NOT EXISTS likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  project_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_project (user_id, project_id),
  KEY idx_project (project_id),
  CONSTRAINT fk_like_user    FOREIGN KEY (user_id)    REFERENCES users(id)        ON DELETE CASCADE,
  CONSTRAINT fk_like_project FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
