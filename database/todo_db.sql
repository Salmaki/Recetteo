CREATE DATABASE IF NOT EXISTS todo_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todo_db;

CREATE TABLE IF NOT EXISTS users (
    id       INT          AUTO_INCREMENT PRIMARY KEY,  
    username VARCHAR(50)  NOT NULL UNIQUE,          
    email    VARCHAR(100) NOT NULL UNIQUE,             
    password VARCHAR(255) NOT NULL,                   
    created_at DATETIME   DEFAULT CURRENT_TIMESTAMP   
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tasks (
    id         INT          AUTO_INCREMENT PRIMARY KEY,  
    user_id    INT          NOT NULL,                   
    title      VARCHAR(255) NOT NULL,                   
    is_done    TINYINT(1)   NOT NULL DEFAULT 0,          
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,  

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
