-- Setup Database Sekolah
-- Run this file to create database and tables

CREATE DATABASE IF NOT EXISTS db_sekolah;
USE db_sekolah;

-- Table Users (Updated Schema)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    kelas ENUM('10', '11', '12') DEFAULT NULL,
    nomor_kelas INT(1) DEFAULT NULL,
    jurusan ENUM('TKJ', 'AKL', 'Lakes', 'Perhotelan') DEFAULT NULL,
    angkatan YEAR DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_angkatan (angkatan),
    INDEX idx_jurusan (jurusan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Database db_sekolah berhasil dibuat!' as message;
SELECT 'Table users berhasil dibuat!' as message;