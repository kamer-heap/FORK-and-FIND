-- Fork & Find - Variant 1 schema (4 tables, matches the 15 Oct 2026 proposal)
CREATE DATABASE IF NOT EXISTS fork_and_find CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fork_and_find;

DROP TABLE IF EXISTS Reviews;
DROP TABLE IF EXISTS Menu_Items;
DROP TABLE IF EXISTS Cafeterias;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    user_id     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,                       -- password_hash() output
    role        ENUM('student','owner','admin') NOT NULL DEFAULT 'student',
    is_approved TINYINT(1) NOT NULL DEFAULT 1                -- owners start at 0 until admin approves
) ENGINE=InnoDB;

CREATE TABLE Cafeterias (
    cafe_id    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    location   VARCHAR(150) NOT NULL,
    timings    VARCHAR(100) NOT NULL,
    days       VARCHAR(30) NULL,
    description VARCHAR(500) NULL,
    image      VARCHAR(100) NULL,
    owner_id   INT UNSIGNED NULL UNIQUE,                     -- one owner -> one cafeteria
    CONSTRAINT fk_cafe_owner FOREIGN KEY (owner_id) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE Menu_Items (
    item_id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cafe_id   INT UNSIGNED NOT NULL,
    name      VARCHAR(100) NOT NULL,
    price     DECIMAL(8,2) NOT NULL,
    category  VARCHAR(50) NOT NULL,
    description VARCHAR(500) NULL,
    image     VARCHAR(100) NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,                 -- 0 = sold out
    UNIQUE KEY uq_cafe_item (cafe_id, name),
    CONSTRAINT fk_item_cafe FOREIGN KEY (cafe_id) REFERENCES Cafeterias(cafe_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Reviews (
    review_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    item_id   INT UNSIGNED NOT NULL,
    user_id   INT UNSIGNED NOT NULL,
    rating    TINYINT UNSIGNED NOT NULL,
    comment   VARCHAR(500) NOT NULL DEFAULT '',
    date      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_user (item_id, user_id),              -- PENDING decision with Khadija
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_rev_item FOREIGN KEY (item_id) REFERENCES Menu_Items(item_id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_user FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
