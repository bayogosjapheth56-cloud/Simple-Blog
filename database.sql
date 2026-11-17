-- Blogify and Post Management System
-- Import this file in phpMyAdmin (local XAMPP or InfinityFree)

-- LOCAL ONLY: on InfinityFree, create the database in the control panel and
-- delete the next two lines before importing.

-- ------------------------------------------------------------
-- categories: one row per category (referenced by posts)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id    INT(11)      NOT NULL AUTO_INCREMENT,
    name  VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- posts: blog posts; each post belongs to one category
-- (one-to-many relationship: categories 1 --- * posts)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id           INT(11)       NOT NULL AUTO_INCREMENT,
    title        VARCHAR(150)  NOT NULL,
    author       VARCHAR(100)  NOT NULL,
    category_id  INT(11)       NOT NULL,
    content      TEXT          NOT NULL,
    image        VARCHAR(255)  NULL,
    status       ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_posts_category (category_id),
    CONSTRAINT fk_posts_category FOREIGN KEY (category_id)
        REFERENCES categories (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Sample data
-- ------------------------------------------------------------
INSERT INTO categories (id, name) VALUES
(1, 'General'),
(2, 'Technology');

INSERT INTO posts (title, author, category_id, content, status) VALUES
('Welcome to My Blog', 'Admin', 1, 'This is my first post. Thanks for visiting!', 'Published'),
('Learning PHP and MySQL', 'Admin', 2, 'CRUD stands for Create, Read, Update, Delete.', 'Draft');
