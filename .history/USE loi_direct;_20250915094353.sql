USE loi_direct;

CREATE TABLE IF NOT EXISTS law_projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  summary VARCHAR(280) NOT NULL,
  body_markdown MEDIUMTEXT NOT NULL,
  status ENUM('draft','published','removed') NOT NULL DEFAULT 'published',
  published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_published (status, published_at DESC),
  INDEX idx_author (author_id),
  CONSTRAINT fk_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- (Optionnel pour tester le feed tout de suite)
INSERT INTO law_projects (author_id, title, summary, body_markdown, status)
SELECT u.id, 'Exemple : Encadrement des frais bancaires',
       'Plafond annuel des frais bancaires pour les ménages modestes.',
       '## Objet\nLimiter les frais...\n\n### Articles\n1. ...\n',
       'published'
FROM users u ORDER BY u.id ASC LIMIT 1;
