-- ============================================================
-- ogrenci_no_system_db.sql
-- BGY206 – Web Programlama-I Proje Ödevi
-- Veritabanı adı: ogrenci_no_system_db  (öğrenci numaranızla değiştirin)
-- Bu dosyayı phpMyAdmin üzerinden "Import" ile çalıştırabilirsiniz.
-- ============================================================

-- Veritabanını oluştur (yoksa)
CREATE DATABASE IF NOT EXISTS `ogrenci_no_system_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Veritabanını seç
USE `ogrenci_no_system_db`;

-- ─── users tablosu ────────────────────────────────────────────────────────────
-- Kayıtlı kullanıcıların hesap bilgilerini tutar.
CREATE TABLE IF NOT EXISTS `users` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `ad`          VARCHAR(50)    NOT NULL                    COMMENT 'Kullanıcının adı',
    `soyad`       VARCHAR(50)    NOT NULL                    COMMENT 'Kullanıcının soyadı',
    `email`       VARCHAR(150)   NOT NULL                    COMMENT 'E-posta adresi (benzersiz)',
    `sifre_hash`  VARCHAR(255)   NOT NULL                    COMMENT 'bcrypt ile hashlenmiş şifre',
    `created_at`  DATETIME       NOT NULL DEFAULT NOW()      COMMENT 'Kayıt oluşturma tarihi',
    `updated_at`  DATETIME       NULL     ON UPDATE NOW()    COMMENT 'Son güncelleme tarihi',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── users_logs tablosu ───────────────────────────────────────────────────────
-- Kullanıcıların giriş / çıkış işlem kayıtlarını tutar.
CREATE TABLE IF NOT EXISTS `users_logs` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED   NULL                        COMMENT 'users.id referansı (kullanıcı silinince NULL)',
    `ip_adresi`   VARCHAR(45)    NOT NULL                    COMMENT 'IPv4 veya IPv6 adresi',
    `islem_tipi`  ENUM(
                    'giris_basarili',
                    'giris_basarisiz',
                    'giris_engellendi',
                    'cikis'
                  )              NOT NULL                    COMMENT 'İşlem türü',
    `aciklama`    VARCHAR(255)   NULL                        COMMENT 'İşlem hakkında ek açıklama',
    `created_at`  DATETIME       NOT NULL DEFAULT NOW()      COMMENT 'İşlem tarihi ve saati',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_logs_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users`(`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;