-- ============================================
-- DATABASE: toko_sederhana
-- REVISI: 2025 - Tambah foreign key dan relasi
-- ============================================

-- Hapus database jika ada (hati-hati!)
DROP DATABASE IF EXISTS toko_sederhana;

-- Buat database baru
CREATE DATABASE toko_sederhana;
USE toko_sederhana;

-- ============================================
-- TABLE: user
-- Role: admin, kasir
-- Password menggunakan MD5 (bisa diganti dengan password_hash di PHP)
-- ============================================

CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun default (password = admin123 / kasir123)
INSERT INTO user (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('kasir', MD5('kasir123'), 'kasir');

-- ============================================
-- TABLE: produk
-- Data barang yang dijual
-- ============================================

CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contoh data awal
INSERT INTO produk (nama_produk, harga, stok) VALUES
('Indomie Goreng', 3500, 100),
('Aqua 600ml', 5000, 50),
('Sampo Sachet', 2000, 80),
('Kopi Sachet', 1500, 120),
('Roti Tawar', 12000, 30),
('Susu UHT 250ml', 7000, 40),
('Telur 1kg', 25000, 20),
('Minyak Goreng 1L', 18000, 25),
('Gula Pasir 1kg', 15000, 15),
('Teh Celup', 5000, 60);

-- ============================================
-- TABLE: transaksi
-- Data transaksi utama
-- ============================================

CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    total_harga INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal),
    INDEX idx_user (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: detail_transaksi
-- Detail item di setiap transaksi
-- ============================================

CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    harga_satuan INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_transaksi (id_transaksi),
    INDEX idx_produk (id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- STORED PROCEDURE & FUNCTIONS (Opsional)
-- ============================================

-- Function: Hitung total pendapatan hari ini
DELIMITER //
CREATE FUNCTION get_pendapatan_hari_ini() 
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COALESCE(SUM(total_harga), 0) INTO total 
    FROM transaksi 
    WHERE DATE(tanggal) = CURDATE();
    RETURN total;
END//
DELIMITER ;

-- Procedure: Update stok setelah transaksi
DELIMITER //
CREATE PROCEDURE update_stok_produk(
    IN p_id_produk INT,
    IN p_jumlah INT,
    IN p_operation ENUM('increase', 'decrease')
)
BEGIN
    IF p_operation = 'decrease' THEN
        UPDATE produk 
        SET stok = stok - p_jumlah 
        WHERE id_produk = p_id_produk;
    ELSEIF p_operation = 'increase' THEN
        UPDATE produk 
        SET stok = stok + p_jumlah 
        WHERE id_produk = p_id_produk;
    END IF;
END//
DELIMITER ;

-- ============================================
-- VIEW: Untuk laporan
-- ============================================

-- View: Laporan harian
CREATE VIEW laporan_harian AS
SELECT 
    DATE(t.tanggal) as tanggal,
    COUNT(t.id_transaksi) as jumlah_transaksi,
    SUM(t.total_harga) as total_pendapatan,
    SUM(dt.jumlah) as total_item_terjual,
    COUNT(DISTINCT dt.id_produk) as jumlah_produk_terjual
FROM transaksi t
LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
GROUP BY DATE(t.tanggal);

-- View: Transaksi lengkap
CREATE VIEW transaksi_lengkap AS
SELECT 
    t.id_transaksi,
    t.tanggal,
    u.username as kasir,
    t.total_harga,
    p.nama_produk,
    dt.harga_satuan,
    dt.jumlah,
    dt.subtotal
FROM transaksi t
JOIN user u ON t.id_user = u.id_user
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
JOIN produk p ON dt.id_produk = p.id_produk
ORDER BY t.tanggal DESC;

-- ============================================
-- TRIGGER: Untuk otomatisasi
-- ============================================

-- Trigger: Update stok otomatis saat transaksi
DELIMITER //
CREATE TRIGGER after_insert_detail_transaksi
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
    -- Kurangi stok produk
    UPDATE produk 
    SET stok = stok - NEW.jumlah 
    WHERE id_produk = NEW.id_produk;
END//
DELIMITER ;

-- Trigger: Update timestamp produk
DELIMITER //
CREATE TRIGGER before_update_produk
BEFORE UPDATE ON produk
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;

-- ============================================
-- DATA SAMPLE UNTUK TESTING
-- ============================================

-- Transaksi sample (opsional untuk testing)
INSERT INTO transaksi (id_user, total_harga) VALUES
(1, 7000),  -- admin
(2, 15000), -- kasir
(2, 32000); -- kasir

INSERT INTO detail_transaksi (id_transaksi, id_produk, harga_satuan, jumlah, subtotal) VALUES
(1, 1, 3500, 2, 7000),   -- 2 Indomie
(2, 2, 5000, 1, 5000),   -- 1 Aqua
(2, 3, 2000, 5, 10000),  -- 5 Sampo
(3, 1, 3500, 3, 10500),  -- 3 Indomie
(3, 2, 5000, 2, 10000),  -- 2 Aqua
(3, 3, 2000, 3, 6000),   -- 3 Sampo
(3, 4, 1500, 1, 1500),   -- 1 Kopi
(3, 5, 12000, 1, 12000); -- 1 Roti

-- ============================================
-- INDEX Tambahan untuk performa
-- ============================================

CREATE INDEX idx_produk_nama ON produk(nama_produk);
CREATE INDEX idx_user_username ON user(username);
CREATE INDEX idx_transaksi_tanggal_user ON transaksi(tanggal, id_user);

-- ============================================
-- PRIVILEGES (Opsional untuk deployment)
-- ============================================

-- Buat user khusus aplikasi (opsional)
-- CREATE USER 'toko_app'@'localhost' IDENTIFIED BY 'password123';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON toko_sederhana.* TO 'toko_app'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- QUERY TEST
-- ============================================

-- Test query untuk cek database bekerja
SELECT 'Database toko_sederhana berhasil dibuat!' as status;

-- Tampilkan struktur
SHOW TABLES;

-- Hitung jumlah data
SELECT 
    (SELECT COUNT(*) FROM user) as total_user,
    (SELECT COUNT(*) FROM produk) as total_produk,
    (SELECT COUNT(*) FROM transaksi) as total_transaksi,
    (SELECT COUNT(*) FROM detail_transaksi) as total_detail,
    (SELECT get_pendapatan_hari_ini()) as pendapatan_hari_ini;