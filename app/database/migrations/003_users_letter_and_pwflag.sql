ALTER TABLE users
  ADD COLUMN letter VARCHAR(64) NULL AFTER name,
  ADD COLUMN must_set_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash;

-- existing users already have passwords
UPDATE users SET must_set_password=0 WHERE must_set_password IS NULL;
