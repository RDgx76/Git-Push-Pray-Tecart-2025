-- =====================================================
--  BYTE MART POS - FULL DATABASE SCHEMA
--  Combined SQL in One Script
--  Compatible with MySQL / MariaDB
-- =====================================================

CREATE DATABASE IF NOT EXISTS bytemart;
USE bytemart;

-- =====================================================
--  TABLE: pegawai
-- =====================================================
CREATE TABLE pegawai (
    id_pegawai INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    username VARCHAR(80) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
--  TABLE: produk
-- =====================================================
CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(160) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(100),
    harga_beli DECIMAL(12,2) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    barcode VARCHAR(120),
    gambar VARCHAR(255),
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
--  TABLE: penjualan
-- =====================================================
CREATE TABLE penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    diskon DECIMAL(12,2) DEFAULT 0,
    metode_pembayaran ENUM('tunai', 'kartu', 'qr') NOT NULL,
    uang_diterima DECIMAL(12,2) DEFAULT NULL,
    kembalian DECIMAL(12,2) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =====================================================
--  TABLE: penjualan_detail
-- =====================================================
CREATE TABLE penjualan_detail (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_produk INT NOT NULL,
    kuantitas INT NOT NULL,
    harga_saat_it DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,

    FOREIGN KEY (id_penjualan)
        REFERENCES penjualan(id_penjualan)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =====================================================
--  TABLE: kinerja_pegawai
-- =====================================================
CREATE TABLE kinerja_pegawai (
    id_kinerja INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT NOT NULL,
    tanggal DATE NOT NULL,
    jumlah_transaksi INT DEFAULT 0,
    total_penjualan DECIMAL(12,2) DEFAULT 0,

    FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
--  TABLE: toko
-- =====================================================
CREATE TABLE toko (
    id_toko INT PRIMARY KEY,
    nama_toko VARCHAR(150),
    alamat TEXT,
    header_nota TEXT,
    pajak_persen DECIMAL(5,2) DEFAULT 0
);

INSERT INTO toko (id_toko, nama_toko, alamat, header_nota, pajak_persen)
VALUES (1, 'ByteMart', 'Jl. Contoh No. 12', 'Terima kasih telah berbelanja!', 0);

-- =====================================================
--  TABLE: log_aktifitas
-- =====================================================
CREATE TABLE log_aktifitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT,
    aktifitas VARCHAR(255),
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- =====================================================
--  INSERT DUMMY DATA
-- =====================================================
INSERT INTO pegawai (nama, username, password, role)
VALUES 
('Admin', 'admin', MD5('admin123'), 'admin'),
('Kasir A', 'kasir1', MD5('kasir123'), 'kasir');

INSERT INTO produk (nama_produk, harga_beli, harga_jual, stok, kategori)
VALUES
('Indomie Goreng', 2500, 3500, 120, 'Makanan'),
('Susu Ultra 250ml', 4500, 6500, 80, 'Minuman'),
('Mouse Wireless', 35000, 55000, 20, 'Elektronik');

-- =====================================================
-- END OF FILE
-- ByteMart POS Database Fully Installed
-- =====================================================
