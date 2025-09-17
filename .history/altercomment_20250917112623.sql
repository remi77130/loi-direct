ALTER TABLE comments     ADD CONSTRAINT fk_comments_project
  FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE;

ALTER TABLE likes        ADD CONSTRAINT fk_likes_project
  FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE;

ALTER TABLE project_tags ADD CONSTRAINT fk_pt_project
  FOREIGN KEY (project_id) REFERENCES law_projects(id) ON DELETE CASCADE;
