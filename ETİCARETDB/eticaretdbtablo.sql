USE master;
GO

-- Veritabanı zaten varsa sil
IF EXISTS (SELECT name FROM sys.databases WHERE name = N'EticaretDB')
    DROP DATABASE EticaretDB;
GO

CREATE DATABASE EticaretDB;
GO

USE EticaretDB;
GO

-- =====================
-- TABLOLAR
-- =====================

CREATE TABLE Kategoriler (
    KategoriID  INT IDENTITY(1,1) PRIMARY KEY,
    KategoriAdi NVARCHAR(100) NOT NULL
);

CREATE TABLE Kullanicilar (
    KullaniciID  INT IDENTITY(1,1) PRIMARY KEY,
    KullaniciAdi NVARCHAR(100) NOT NULL,
    Sifre        NVARCHAR(255) NOT NULL,
    Eposta       NVARCHAR(255) NOT NULL,
    AdSoyad      NVARCHAR(200) NULL
);

CREATE TABLE Urunler (
    UrunID     INT IDENTITY(1,1) PRIMARY KEY,
    UrunAdi    NVARCHAR(200) NOT NULL,
    Fiyat      DECIMAL(10,2) NOT NULL,
    KategoriID INT NULL,
    Kategori   NVARCHAR(100) NULL,
    GorselURL  NVARCHAR(500) NULL,
    Stok       INT NOT NULL DEFAULT 0,
    Aciklama   NVARCHAR(MAX) NULL
);

CREATE TABLE Siparisler (
    SiparisID   INT IDENTITY(1,1) PRIMARY KEY,
    KullaniciID INT NOT NULL,
    ToplamTutar DECIMAL(10,2) NOT NULL,
    Tarih       DATETIME DEFAULT GETDATE(),
    Durum       NVARCHAR(50) DEFAULT N'Beklemede',
    FOREIGN KEY (KullaniciID) REFERENCES Kullanicilar(KullaniciID)
);

CREATE TABLE SiparisDetay (
    DetayID    INT IDENTITY(1,1) PRIMARY KEY,
    SiparisID  INT NOT NULL,
    UrunID     INT NOT NULL,
    Miktar     INT NOT NULL,
    BirimFiyat DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (SiparisID) REFERENCES Siparisler(SiparisID),
    FOREIGN KEY (UrunID)    REFERENCES Urunler(UrunID)
);
GO

-- =====================
-- VERİLER
-- =====================

SET IDENTITY_INSERT Kategoriler ON;
INSERT INTO Kategoriler (KategoriID, KategoriAdi) VALUES
(1, N'Bilgisayar'),
(2, N'Donanım'),
(3, N'Çevre Birimleri');
SET IDENTITY_INSERT Kategoriler OFF;
GO

SET IDENTITY_INSERT Kullanicilar ON;
INSERT INTO Kullanicilar (KullaniciID, KullaniciAdi, Sifre, Eposta, AdSoyad) VALUES
(1, N'admin',        N'$2y$10$FHXnhHXzLWMT4lhm69c3n..Rx..m68eZOzxZ0x27ks/W1tM5TD8xC', N'admin@mail.com',               N'admin bey'),
(2, N'mehmet atakan',N'12345',  N'mehmetatakanozkan1@gmail.com', N'mehmet atakan');
SET IDENTITY_INSERT Kullanicilar OFF;
GO

SET IDENTITY_INSERT Urunler ON;
INSERT INTO Urunler (UrunID, UrunAdi, Fiyat, Stok) VALUES
(1,  N'Laptop',             15000.00, 0),
(2,  N'Mouse',                200.00, 0),
(3,  N'Klavye',               500.00, 0),
(4,  N'Monitor',             3500.00, 0),
(5,  N'Kulaklık',            1200.00, 0),
(6,  N'Koltuk',              1400.00, 0),
(7,  N'Gaming Mouse Pad',     350.00, 0),
(8,  N'Webcam 1080p',        1200.00, 0),
(9,  N'USB Çoklayıcı',        150.00, 0),
(10, N'Ekran Temizleme Kiti',  75.00, 0),
(16, N'Akıllı Telefon',     10000.00, 10);
SET IDENTITY_INSERT Urunler OFF;
GO

SET IDENTITY_INSERT Siparisler ON;
INSERT INTO Siparisler (SiparisID, KullaniciID, ToplamTutar, Tarih, Durum) VALUES
(1, 1,  150.00, '2026-04-29T11:20:42.723', N'İptal'),
(2, 1, 1425.00, '2026-04-29T11:21:45.550', N'Beklemede');
SET IDENTITY_INSERT Siparisler OFF;
GO

SET IDENTITY_INSERT SiparisDetay ON;
INSERT INTO SiparisDetay (DetayID, SiparisID, UrunID, Miktar, BirimFiyat) VALUES
(1, 1, 9,  1,  150.00),
(2, 2, 10, 1,   75.00),
(3, 2, 9,  1,  150.00),
(4, 2, 8,  1, 1200.00);
SET IDENTITY_INSERT SiparisDetay OFF;
GO