# Eticaret

TeknoShop — PHP & MS SQL Server ile geliştirilmiş e-ticaret web uygulaması.
FSMVÜ Bilgisayar Programcılığı - Veritabanı Yönetim Sistemleri Dersi 2025-2026 Bahar Dönemi Projesi.

Özellikler:
- Kullanıcı kayıt/giriş sistemi (bcrypt şifreleme)
- Ürün listeleme, arama ve filtreleme
- Sepet yönetimi (miktar kontrolü)
- Sipariş oluşturma ve takibi
- Admin paneli (ürün ve sipariş yönetimi)

Teknolojiler: PHP 8, MS SQL Server 2022, PDO, HTML5, CSS3, XAMPP

- PHP 8.x (PDO ile birlikte)
- Microsoft SQL Server (yerel veya uzak örnek)
- PHP için [Microsoft Drivers for PHP for SQL Server](https://docs.microsoft.com/sql/connect/php/microsoft-php-driver-for-sql-server) (`sqlsrv` uzantısı)
- Apache (ör. XAMPP) veya eşdeğer bir web sunucusu

> MySQL kullanılmaz; veritabanı **Microsoft SQL Server** ve veritabanı adı yapılandırmada `EticaretDB` olarak geçer.

---

## Klasör Yapısı

```
📁 eticaret
├── baglanti.php
├── index.php
├── giris.php
├── kayit.php
├── urun-detay.php
├── urunler.php
├── sepet.php
├── sepet_islem.php
├── siparis.php
├── siparislerim.php
├── cikis.php
├── veritabani_guncelle.sql
├── 📁 admin
│   ├── index.php
│   ├── urun_listesi.php
│   ├── urun_ekle.php
│   ├── urun_guncelle.php
│   ├── siparisler.php
│   └── kullanicilar.php
├── 📁 includes
│   ├── header.php
│   └── footer.php
└── 📁 css
    └── style.css
```

---

## Kurulum Adımları

### 1. Dosyaları Yerleştir

Projeyi web sunucunuzun belge köküne kopyalayın. XAMPP için örnek: `htdocs\eticaret`.

### 2. PHP SQL Server uzantılarını kur

`pdo_sqlsrv` ve `sqlsrv` uzantılarının yüklü ve `php.ini` içinde aktif olduğundan emin olun. Sürüm, PHP’nin thread safety (TS/NTS) ve mimariye (x64) uygun olmalıdır.

### 3. Veritabanını hazırla

- SQL Server’da **`EticaretDB`** adında bir veritabanı oluşturun.
- Tablolarınızı (ör. `Kullanicilar`, `Urunler`, `Siparisler`, `RememberTokens`, sepet ile ilgili tablolar) oluşturacak ana şema script’inizi çalıştırın *(bu repoda tam kurulum şeması yoksa kendi DDL dosyanızı ekleyebilirsiniz)*.
- Görsel güncelleme vb. için proje içindeki `veritabani_guncelle.sql` dosyasını, şema hazır olduktan **sonra** ve amacına uygun şekilde çalıştırın.

### 4. Bağlantı ayarını güncelle

`baglanti.php` dosyasını açın. Sunucu adını kendi SQL Server örneğinize göre değiştirin:

```php
$serverName = "DESKTOP-ELEOOQT"; // ← Kendi SERVER\INSTANCE veya adresiniz
$database   = "EticaretDB";
```

SQL Server kimlik doğrulaması kullanacaksanız, dosyadaki yorum satırındaki PDO satırını açıp kullanıcı adı ve şifreyi doldurun; Windows Authentication kullanıyorsanız mevcut `TrustServerCertificate=true` satırı ile devam edebilirsiniz.

### 5. Uygulama adresi

Proje `http://localhost/eticaret/` altında çalışacak şekilde yönlendirmeler kullanır. Farklı bir klasör adı veya sanal host kullanıyorsanız, PHP dosyalarındaki `/eticaret/` taban yolunu kendi ortamınıza göre güncellemeniz gerekir.

### 6. Tarayıcıda aç

```
http://localhost/eticaret/
```

---

## Roller ve yönetici

- **Yönetici paneli:** `Kullanicilar` tablosunda `KullaniciAdi` değeri tam olarak `admin` olan kullanıcı giriş yaptığında yönetim paneline erişir (`/eticaret/admin/`).
- **Müşteri:** `kayit.php` ile kayıt olup `giris.php` üzerinden e-posta ve şifre ile giriş yapılır.

Demo hesap bilgisi repoda sabitlenmemişse; ilk yönetici kullanıcıyı veritabanında `KullaniciAdi = 'admin'` olacak şekilde oluşturup güvenli bir şifre atayın.

---

## Teknik bilgiler

| Bileşen        | Teknoloji                          |
| -------------- | ---------------------------------- |
| Veritabanı     | Microsoft SQL Server               |
| Backend        | PHP                                |
| Veri erişimi   | PDO (`sqlsrv`)                     |
| Oturum         | PHP `session` (+ isteğe bağlı “beni hatırla” çerezi) |
| Arayüz         | HTML / CSS                         |

---

## Sık karşılaşılan hatalar

**“could not find driver” / PDO sqlsrv hatası:**  
PHP tarafında SQL Server sürücüleri kurulu değil veya `php.ini`'de aktif değil. Yukarıdaki Microsoft PHP driver kurulumunu kontrol edin.

**Bağlantı reddedildi / sunucuya ulaşılamıyor:**  
`baglanti.php` içindeki `$serverName` değerini SQL Server Configuration Manager veya SSMS’te bağlandığınız sunucu adıyla eşleştirin. TCP/IP etkin mi ve gerekiyorsa SQL Browser servisi açık mı kontrol edin.

**403 / 404 (yanlış yol):**  
Apache’de `eticaret` klasör adı ve `DocumentRoot` ile uyumlu olmalı; kod içindeki `/eticaret/` önekleri farklı bir alt yol kullanıyorsanız güncellenmelidir.
