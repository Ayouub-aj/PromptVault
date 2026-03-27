CREATE DATABASE IF NOT EXISTS prompt_vault;
USE prompt_vault;

DROP TABLE IF EXISTS prompts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

CREATE TABLE users (
    id            INT UNSIGNED         NOT NULL AUTO_INCREMENT, -- PK /unique id referenced to prompts as a foreign key see schema README.md, auto incremented
    username      VARCHAR(50)          NOT NULL UNIQUE,-- unique name
    email         VARCHAR(150)         NOT NULL UNIQUE,-- one account per email
    password_hash VARCHAR(255)         NOT NULL,-- password hash using BCRYPT
    role          ENUM('user','admin') NOT NULL DEFAULT 'user', -- roles if none assigned default is user
    created_at    DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP, -- date time filled on data insert
    PRIMARY KEY (id) -- primary key for this table
);

CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT, -- PK /unique id referenced to prompts as a foreign key see schema README.md, auto incremented
    name       VARCHAR(100) NOT NULL UNIQUE, -- unique label/category name, only one label insertion
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- date time filled on insert
    PRIMARY KEY (id)
);

CREATE TABLE prompts (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT, -- PK/unique id auto incremented
    title       VARCHAR(200) NOT NULL, -- short name for the prompt
    content     TEXT         NOT NULL, -- full prompt content with no limit
    user_id     INT UNSIGNED NOT NULL, -- links to user-id to know who wrote it 
    category_id INT UNSIGNED NOT NULL, -- links to category id to know to what category it belongs
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- auto filled on insertion
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- auto filled on edit
    PRIMARY KEY (id),

    -- added these conditions/constraints(if a user is deleted all his prompts are deleted too)
    CONSTRAINT fk_prompts_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    -- added a RESTRICT for deletion of categories; if a categorie is deleted the prompts arent if maybe admin wants to assign them to another category manually
    CONSTRAINT fk_prompts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);
-- ┌─────────────────────┬──────────────────────────┬──────────────────────────────────────┐
-- │                     │  user_id → CASCADE       │  category_id → RESTRICT              │
-- ├─────────────────────┼──────────────────────────┼──────────────────────────────────────┤
-- │ Scenario            │  User account deleted    │  Category deleted                    │
-- │ Silent Behaviour    │  Prompts deleted too     │  Deletion blocked                    │
-- │ Why                 │  Orphan prompts useless  │  Prompts should be reassigned first  │
-- └─────────────────────┴──────────────────────────┴──────────────────────────────────────┘


SHOW TABLES;

INSERT INTO categories (name) VALUES ('Programming');
INSERT INTO categories (name) VALUES ('SQL');
INSERT INTO categories (name) VALUES ('SEO');
INSERT INTO categories (name) VALUES ('Marketing');
INSERT INTO categories (name) VALUES ('Documentation');
INSERT INTO categories (name) VALUES ('QAtesting');

--  insert DATA — Users
--  Passwords below are hashed versions of:
--    password  → for admin user
--    password  → for dev users
--  (generated with password_hash($pass, PASSWORD_BCRYPT))

INSERT INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
INSERT INTO users (username, email, password_hash, role) VALUES ('dev', 'ayoub@dev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
INSERT INTO users (username, email, password_hash, role) VALUES ('dev2', 'dev@dev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

--  SEED DATA — Prompts

INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Generate a REST API in PHP', 'You are a senior PHP developer. Generate a fully functional REST API endpoint for a resource called [RESOURCE_NAME]. Include: input validation, PDO prepared statements, JSON responses with proper HTTP status codes, and error handling. Use no framework, pure PHP only.', 2, 1);
INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Write a MySQL query with JOIN', 'You are a database expert. Write a MySQL SELECT query that joins [TABLE_A] and [TABLE_B] on [FOREIGN_KEY]. Include: column aliases, a WHERE filter on [CONDITION], ORDER BY [COLUMN] DESC, and a LIMIT of 20 rows. Explain each clause in a comment.', 2, 2);
INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Write a Dockerfile for a PHP/MySQL app', 'Act as a DevOps engineer. Write a production-ready Dockerfile for a PHP 8.2 application connected to a MySQL database. Include: Alpine base image, composer install step, environment variables for DB config, and a health check.', 3, 3);
INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Write a Facebook Ad copy', 'You are a senior copywriter. Write 3 variations of a Facebook Ad for [PRODUCT_NAME] targeting [AUDIENCE]. Each variation must include: a hook, a value proposition, social proof placeholder, and a CTA. Tone: [TONE].', 3, 4);
INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Generate PHPDoc for a class', 'You are a technical writer. Generate complete PHPDoc comments for the following PHP class: [PASTE_CLASS_HERE]. Include: @package, @author, @version tags on the class, and @param, @return, @throws on each method.', 2, 5);
INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Write unit tests with PHPUnit', 'Act as a QA engineer. Write PHPUnit test cases for the following PHP function: [PASTE_FUNCTION_HERE]. Cover: the happy path, edge cases (empty input, null, wrong type), and at least one exception test. Use data providers where relevant.', 3, 6);
