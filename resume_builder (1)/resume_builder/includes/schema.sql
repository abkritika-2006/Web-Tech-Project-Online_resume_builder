-- Run this in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS resume_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE resume_builder;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Resumes table (one resume per user for now)
CREATE TABLE IF NOT EXISTS resumes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    full_name       VARCHAR(100),
    job_title       VARCHAR(100),
    email           VARCHAR(150),
    phone           VARCHAR(30),
    location        VARCHAR(100),
    linkedin        VARCHAR(200),
    github          VARCHAR(200),
    website         VARCHAR(200),
    summary         TEXT,
    skills          TEXT,        -- JSON array of skill strings
    experience      LONGTEXT,    -- JSON array of experience objects
    education       LONGTEXT,    -- JSON array of education objects
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
