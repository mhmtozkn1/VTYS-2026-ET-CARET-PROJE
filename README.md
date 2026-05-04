# Eticaret (PHP × SQL Server)

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

### Ortam kısa özeti

PHP 8 ve **PDO + `sqlsrv`** ile SQL Server’a bağlanırsınız. Apache’yi XAMMPP’tan veya benzerinden kullanabilirsiniz. Microsoft’un PHP için SQL Server sürücü paketinin (`sqlsrv`, `pdo_sqlsrv`) yüklü ve `php.ini` içinde açılmış olması şart — ayrıntılar için resmi dökümantasyon: [Microsoft Drivers for PHP for SQL Server](https://docs.microsoft.com/sql/connect/php/microsoft-php-driver-for-sql-server).

Veritabanı adı kodda **`EticaretDB`** olarak geçer; MySQL / MariaDB şeması yoktur.

---

### İlk kurulum sırasında dokunacağınız yerler

| Konu | Dosya / yer |
|------|-------------|
| Sunucu adı ve veritabanı | `baglanti.php` → `$serverName`, `$database` |
| İsteğe bağlı SQL login | Aynı dosyada yorumlu satırdaki PDO örneği |
| Site kök adresi | Tüm kodda kullanılan `/eticaret/` öneki — klasörü farklı isimde barındırıyorsanız arama ile güncellenmelidir |

Örnek bağlantı parçası (sunucuyu kendininkine çevir):

```php
$serverName = "BILGISAYARINIZ\\SQLEXPRESS";
$database   = "EticaretDB";
```

Windows kimlik doğrulaması varsayılan yoldur; `sa` vb. kullanacaksanız `baglanti.php` içindeki alternatif PDO satırını etkinleştirip kimlik bilgilerini yazın.

---

### Veritabanı

1. SQL Server üzerinde `EticaretDB` oluşturun.
2. Uygulamanın beklediği tabloları (örn. `Kullanicilar`, `Urunler`, `Siparisler`, `RememberTokens`, sepet ile ilgili yapılar) kendi DDL script’inizle kurun — bu pakette eksiksiz “sıfırdan kurulum” SQL’i yoksa bunu eklemeniz gerekir.
3. `veritabani_guncelle.sql` mevcut veriye yönelik güncelleme içerir; şema oturduktan ve uygun olduğunda çalıştırın.

---

### Dosyalar (mantıksal gruplar)

**Mağaza ve hesap:** `index.php`, `urunler.php`, `urun-detay.php`, `sepet.php`, `sepet_islem.php`, `siparis.php`, `siparislerim.php`, `giris.php`, `kayit.php`, `cikis.php`

**Yönetim:** `admin/index.php`, `admin/urun_*.php`, `admin/siparisler.php`, `admin/kullanicilar.php`

**Ortak:** `includes/header.php`, `includes/footer.php`, `css/style.css`, `baglanti.php`

---

### Yerel adres

| Bileşen        | Teknoloji                          |
| -------------- | ---------------------------------- |
| Veritabanı     | Microsoft SQL Server               |
| Backend        | PHP                                |
| Veri erişimi   | PDO (`sqlsrv`)                     |
| Oturum         | PHP `session` (+ isteğe bağlı “beni hatırla” çerezi) |
| Arayüz         | HTML / CSS                         |

---

### Küçük bir sorun giderme notu

- **PDO / driver ile ilgili hata:** Eksik `sqlsrv` veya yanlış PHP sürümü eşlemesi (TS/NTS, x86/x64) en sık nedendir.
- **Bağlantı kurulamıyor:** SSMS’te kullandığınız tam sunucu dizesini `baglanti.php` ile karşılaştırın; named instance kullanıyorsanız `SUNUCU\ORNEK` biçimi ve gerekli servisler (TCP, Browser) sık sık gözden kaçar.
- **Sayfa bulunamadı / yanlış yönlendirme:** Sabit `/eticaret/` yolu ile gerçek deployment yolu uyuşmuyor olabilir.

İlk admin kullanıcıyı elle ekleyebilir veya kodunuz uygunsa `kayit` sonrası veritabanında `KullaniciAdi` değerini `admin` yapabilirsiniz; giriş e-posta tabanlıdır (`giris.php`).
