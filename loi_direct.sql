-- table des tags
CREATE TABLE IF NOT EXISTS tags (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  slug VARCHAR(60) NOT NULL,
  UNIQUE KEY uq_slug (slug),
  KEY idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- pivot projet<->tag
CREATE TABLE IF NOT EXISTS project_tags (
  project_id INT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (project_id, tag_id),
  KEY idx_tag (tag_id),
  CONSTRAINT fk_pt_project FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_pt_tag     FOREIGN KEY (tag_id)     REFERENCES tags(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
