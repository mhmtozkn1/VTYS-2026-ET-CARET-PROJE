-- ============================================================
--  TeknoShop — Veritabanı Güncelleme Scripti
--  SSMS'de çalıştır (EticaretDB seçili iken)
-- ============================================================

USE EticaretDB;
GO

-- Urunler tablosuna yeni sütunlar ekle
-- (Zaten varsa hata almamak için IF NOT EXISTS ile kontrol ediyoruz)

IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Urunler' AND COLUMN_NAME='Kategori')
    ALTER TABLE Urunler ADD Kategori NVARCHAR(100) NULL;

IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Urunler' AND COLUMN_NAME='GorselURL')
    ALTER TABLE Urunler ADD GorselURL NVARCHAR(500) NULL;

IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Urunler' AND COLUMN_NAME='Stok')
    ALTER TABLE Urunler ADD Stok INT NOT NULL DEFAULT 0;

IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Urunler' AND COLUMN_NAME='Aciklama')
    ALTER TABLE Urunler ADD Aciklama NVARCHAR(MAX) NULL;

GO

-- Test verisi (isteğe bağlı, zaten ürünlerin varsa çalıştırmana gerek yok)
/*
INSERT INTO Urunler (UrunAdi, Fiyat, Kategori, Stok, Aciklama) VALUES
('Logitech MX Master 3', 1299.90, 'Mouse', 25, 'Profesyonel kablosuz mouse'),
('Mechanical Keyboard TKL', 899.00, 'Klavye', 15, 'Cherry MX Blue switch'),
('ASUS 27" 144Hz Monitor', 5499.00, 'Monitör', 8, '1ms, Full HD, FreeSync');
*/
