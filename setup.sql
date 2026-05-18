-- ============================================================
--  BlogCMS — Task 2 Database Setup
--  Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS blog_platform
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE blog_platform;

-- ── users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(120)  NOT NULL,
  email           VARCHAR(191)  NOT NULL UNIQUE,
  password_hash   VARCHAR(255)  NOT NULL,
  role            ENUM('reader','author','admin') NOT NULL DEFAULT 'reader',
  bio             TEXT,
  profile_pic_path VARCHAR(255),
  social_links    JSON,
  remember_token  VARCHAR(255),
  pending_author  TINYINT(1) NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ── categories ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

-- ── tags ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tags (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

-- ── articles ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS articles (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  author_id           INT NOT NULL,
  category_id         INT,
  title               VARCHAR(255) NOT NULL,
  body                TEXT         NOT NULL,
  featured_image_path VARCHAR(255),
  status              ENUM('draft','published') NOT NULL DEFAULT 'draft',
  publish_at          DATETIME,
  view_count          INT NOT NULL DEFAULT 0,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ── article_tags (pivot) ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS article_tags (
  article_id INT NOT NULL,
  tag_id     INT NOT NULL,
  PRIMARY KEY (article_id, tag_id),
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
);

-- ── likes ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS likes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  article_id INT NOT NULL,
  user_id    INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_like (article_id, user_id),
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ── comments ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS comments (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  article_id INT  NOT NULL,
  user_id    INT  NOT NULL,
  body       TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ── reported_comments ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reported_comments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  comment_id  INT          NOT NULL,
  reported_by INT          NOT NULL,
  reason      VARCHAR(255) NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_report (comment_id, reported_by),
  FOREIGN KEY (comment_id)  REFERENCES comments(id) ON DELETE CASCADE,
  FOREIGN KEY (reported_by) REFERENCES users(id)    ON DELETE CASCADE
);

-- ============================================================
--  Dummy Data for Testing Task 2
-- ============================================================

-- Test users (password = "password123" for all)
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
('Admin User',  'admin@blog.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Alice Author','alice@blog.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author'),
('Bob Reader',  'bob@blog.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader');

-- Categories
INSERT IGNORE INTO categories (name) VALUES
('Technology'), ('Science'), ('Travel'), ('Food'), ('Lifestyle');

-- Tags
INSERT IGNORE INTO tags (name) VALUES
('php'), ('web'), ('tutorial'), ('beginner'), ('advanced'), ('tips');

-- Sample articles (author_id = 2 = Alice)
INSERT IGNORE INTO articles (author_id, category_id, title, body, status, created_at) VALUES
(2, 1, 'Getting Started with PHP',
 'PHP is a widely-used open source general-purpose scripting language that is especially suited for web development.',
 'published', NOW() - INTERVAL 3 DAY),

(2, 1, 'Understanding MVC Architecture',
 'MVC stands for Model-View-Controller. It is a design pattern that separates an application into three main components.',
 'published', NOW() - INTERVAL 2 DAY),

(2, 2, 'Introduction to Databases',
 'A database is an organized collection of structured information or data, typically stored electronically in a computer system.',
 'draft', NOW() - INTERVAL 1 DAY),

(2, 1, 'Scheduled Post Example',
 'This article was scheduled to be published automatically at a future date and time.',
 'draft', NOW() - INTERVAL 5 DAY);

-- Update the 4th article with a future publish_at to test scheduling
UPDATE articles SET publish_at = NOW() - INTERVAL 1 HOUR
WHERE title = 'Scheduled Post Example';

-- Article-tag pivots
INSERT IGNORE INTO article_tags (article_id, tag_id)
SELECT a.id, t.id FROM articles a, tags t
WHERE a.title = 'Getting Started with PHP'   AND t.name IN ('php','web','beginner');

INSERT IGNORE INTO article_tags (article_id, tag_id)
SELECT a.id, t.id FROM articles a, tags t
WHERE a.title = 'Understanding MVC Architecture' AND t.name IN ('php','tutorial','advanced');
