CREATE DATABASE IF NOT EXISTS laundry_2401020164
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE laundry_2401020164;

DROP TABLE IF EXISTS pesanan_laundry;
DROP TABLE IF EXISTS jenis_layanan;

CREATE TABLE jenis_layanan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    UNIQUE KEY jenis_layanan_nama_layanan_unique (nama_layanan)
) ENGINE = InnoDB;

CREATE TABLE pesanan_laundry (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan CHAR(10) NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    nomor_telepon VARCHAR(20) NOT NULL,
    berat_kg SMALLINT UNSIGNED NOT NULL,
    jenis_layanan_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY pesanan_laundry_kode_pesanan_unique (kode_pesanan),
    UNIQUE KEY pesanan_laundry_nomor_telepon_unique (nomor_telepon),
    CONSTRAINT pesanan_laundry_berat_kg_check CHECK (berat_kg BETWEEN 1 AND 20),
    CONSTRAINT pesanan_laundry_jenis_layanan_id_foreign
        FOREIGN KEY (jenis_layanan_id) REFERENCES jenis_layanan (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;

INSERT INTO jenis_layanan (id, nama_layanan) VALUES
    (1, 'Cuci Kering'),
    (2, 'Cuci Setrika');

INSERT INTO pesanan_laundry
    (kode_pesanan, nama_pelanggan, nomor_telepon, berat_kg, jenis_layanan_id, created_at, updated_at)
VALUES
    ('LD00000001', 'Ayu Lestari', '081200000001', 5, 1, NOW(), NOW()),
    ('LD00000002', 'Rafi Akbar', '081200000002', 8, 2, NOW(), NOW()),
    ('LD00000003', 'Nina Putri', '081200000003', 3, 1, NOW(), NOW());
