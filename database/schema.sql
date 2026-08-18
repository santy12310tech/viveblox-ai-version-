-- ViveBlox 1.8 - MySQL/MariaDB schema
CREATE DATABASE IF NOT EXISTS viveblox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE viveblox;

CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(32) NOT NULL UNIQUE,
 display_name VARCHAR(64) NOT NULL,
 email VARCHAR(255) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('user','moderator','admin') NOT NULL DEFAULT 'user',
 club_active BOOLEAN NOT NULL DEFAULT FALSE,
 coins BIGINT UNSIGNED NOT NULL DEFAULT 0,
 avatar_json JSON NULL,
 status ENUM('online','offline','away') NOT NULL DEFAULT 'offline',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_audit_log (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 admin_user_id BIGINT UNSIGNED NOT NULL,
 target_user_id BIGINT UNSIGNED NULL,
 action VARCHAR(64) NOT NULL,
 details JSON NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (admin_user_id) REFERENCES users(id),
 FOREIGN KEY (target_user_id) REFERENCES users(id)
);

-- Cambios de saldo: siempre registrar quién los hizo.
CREATE TABLE coin_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 admin_user_id BIGINT UNSIGNED NULL,
 amount BIGINT NOT NULL,
 reason VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id),
 FOREIGN KEY (admin_user_id) REFERENCES users(id)
);

-- El panel debe ejecutar operaciones mediante el backend autenticado,
-- nunca exponer estas credenciales ni permitir SQL directo desde el navegador.
