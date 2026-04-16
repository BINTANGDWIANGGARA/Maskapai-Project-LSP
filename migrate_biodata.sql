-- ============================================
-- Tikecting.yuu – Migrasi: Tambah Biodata User
-- Jalankan query ini di phpMyAdmin setelah
-- database tikecting_yuu sudah ada.
-- ============================================

USE `tikecting_yuu`;

-- Tambah kolom biodata ke tabel users
ALTER TABLE `users`
  ADD COLUMN `nik` varchar(20) DEFAULT NULL AFTER `phone`,
  ADD COLUMN `gender` enum('Laki-laki','Perempuan') DEFAULT NULL AFTER `nik`,
  ADD COLUMN `birthdate` date DEFAULT NULL AFTER `gender`,
  ADD COLUMN `address` text DEFAULT NULL AFTER `birthdate`,
  ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `address`;

-- Update demo user dengan biodata contoh
UPDATE `users` SET
  nik = '3201010101010001',
  gender = 'Laki-laki',
  birthdate = '1990-05-15',
  address = 'Jl. Sudirman No. 10, Jakarta Pusat'
WHERE id = 2;
