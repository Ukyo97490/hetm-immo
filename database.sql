-- ============================================================
-- H&M Immobilier — Schema base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS hm_immobilier
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hm_immobilier;

-- ──────────────────────────────────────────────────────────
-- TABLE : users
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(191) NOT NULL,                       -- 191 × 4 = 764 bytes < 767 limit InnoDB
    password_hash VARCHAR(255) NOT NULL,                       -- bcrypt via password_hash()
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    role          ENUM('admin','agent') NOT NULL DEFAULT 'agent',
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_email (email),                             -- clé unique sur 191 chars max
    INDEX idx_role  (role)
);

-- ──────────────────────────────────────────────────────────
-- TABLE : sessions  (gestion de session serveur)
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sessions (
    id         CHAR(64) NOT NULL PRIMARY KEY,   -- random_bytes(32) → bin2hex
    user_id    INT UNSIGNED NOT NULL,
    ip_addr    VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
);

-- ──────────────────────────────────────────────────────────
-- TABLE : properties (biens immobiliers)
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS properties (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by      INT UNSIGNED NOT NULL,                    -- agent / admin qui a créé le bien
    managed_by      INT UNSIGNED NOT NULL,                    -- peut être réaffecté par l'admin
    title           VARCHAR(255)  NOT NULL,
    description     TEXT,
    location        VARCHAR(255)  NOT NULL,
    property_type   ENUM('maison','appartement','terrain','villa','chalet') NOT NULL,
    price           DECIMAL(15,2) NOT NULL,
    surface         DECIMAL(8,2)  NULL,                       -- en m²
    bedrooms        TINYINT(3)    NULL,
    bathrooms       TINYINT(3)    NULL,
    has_pool        TINYINT(1)    NOT NULL DEFAULT 0,
    has_garage      TINYINT(1)    NOT NULL DEFAULT 0,
    has_garden      TINYINT(1)    NOT NULL DEFAULT 0,
    status          ENUM('active','inactive','sold') NOT NULL DEFAULT 'active',
    main_image      VARCHAR(255)  NULL,                       -- chemin relatif à l'upload
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (managed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_status    (status),
    INDEX idx_type      (property_type),
    INDEX idx_managed   (managed_by),
    INDEX idx_created   (created_by)
);

-- ──────────────────────────────────────────────────────────
-- TABLE : property_images (galerie supplémentaire)
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS property_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    file_path   VARCHAR(255) NOT NULL,
    sort_order  TINYINT(3)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id, sort_order)
);

-- ──────────────────────────────────────────────────────────
-- TABLE : audit_log  (traçabilité des actions sensibles)
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(64)  NOT NULL,    -- ex: 'login', 'create_user', 'delete_property'
    target     VARCHAR(64)  NULL,        -- ex: 'user:5', 'property:12'
    details    TEXT         NULL,
    ip_addr    VARCHAR(45)  NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, created_at),
    INDEX idx_action      (action, created_at)
);

-- ──────────────────────────────────────────────────────────
-- Premier compte admin : créé automatiquement par setup-admin.php
-- Rendez-vous sur /setup-admin.php après avoir exécuté ce fichier SQL.
-- ──────────────────────────────────────────────────────────
