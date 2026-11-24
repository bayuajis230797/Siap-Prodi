-- =============================================================================
-- SQL WAJIB di-import ke hosting untuk fitur Google Drive (upload institusi)
-- Database: sama dengan aplikasi (mis. siharkat_siap_prodi / nama DB hosting Anda)
-- File ini aman dijalankan berulang (IF NOT EXISTS).
-- =============================================================================
-- Setelah import:
-- 1. Deploy kode aplikasi (Profil Admin → Hubungkan Google Drive)
-- 2. Pastikan redirect URI di Google Cloud Console = https://DOMAIN/Google_drive_oauth/callback
-- 3. Isi Client ID / Secret di Profil Admin (atau tabel google_oauth_settings)
-- =============================================================================

CREATE TABLE IF NOT EXISTS google_oauth_settings (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  client_id VARCHAR(255) NOT NULL DEFAULT '',
  client_secret_enc TEXT NULL,
  redirect_uri VARCHAR(500) NOT NULL DEFAULT '',
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_google_drive (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  google_email VARCHAR(255) NULL,
  access_token_enc TEXT NULL,
  refresh_token_enc TEXT NULL,
  token_expires_at DATETIME NULL,
  root_folder_id VARCHAR(128) NULL,
  scopes TEXT NULL,
  connected_at DATETIME NULL,
  revoked_at DATETIME NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_google_drive_user (user_id),
  CONSTRAINT fk_user_google_drive_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan fitur UI (upload modern, chat WA, popup notifikasi, statistik dosen TA):
-- TIDAK perlu tabel baru. Memakai tabel existing: notifikasi, chat_*, tahun_akademik,
-- mahasiswa_skripsi, pkl_pengajuan, pkm_*, jadwal_*, proposal_bimbingan / pta_bimbingan.
