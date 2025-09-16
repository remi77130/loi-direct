USE loi_direct;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_project_created (project_id, created_at),
  CONSTRAINT fk_comment_project FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_comment_user    FOREIGN KEY (author_id)    REFERENCES users(id)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
