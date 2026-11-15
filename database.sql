-- Blogify and Post Management System
-- Import this file in phpMyAdmin (local XAMPP or InfinityFree)

-- LOCAL ONLY: on InfinityFree, create the database in the control panel and
-- delete the next two lines before importing.
CREATE DATABASE IF NOT EXISTS blog_db;
USE blog_db;

CREATE TABLE IF NOT EXISTS posts (
    id          INT(11)       NOT NULL AUTO_INCREMENT,
    title       VARCHAR(150)  NOT NULL,
    author      VARCHAR(100)  NOT NULL,
    category    VARCHAR(50)   NOT NULL,
    content     TEXT          NOT NULL,
    status      ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO posts (title, author, category, content, status) VALUES
('Welcome to My Blog', 'Admin', 'General', 'This is my first post. Thanks for visiting!', 'Published'),
('Learning PHP and MySQL', 'Admin', 'Technology', 'CRUD stands for Create, Read, Update, Delete.', 'Draft');
