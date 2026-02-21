# ChardyMart POS - Point of Sale System

Aplikasi web Point of Sale (POS) untuk minimarket ChardyMart dengan design Vercel-like yang clean dan modern.

## 🚀 Features

- ✅ Authentication System (Login & Register dengan Secret Key)
- ✅ Dashboard dengan statistik real-time
- ✅ Manajemen Produk & Kategori (CRUD)
- ✅ Kasir dengan input SKU & cart management
- ✅ Riwayat Transaksi & Filter
- ✅ Generate Receipt (PDF-ready format)
- ✅ Custom Modal & Notification Component (NO native dialogs!)
- ✅ Fully Responsive Design
- ✅ Vercel-like Modern UI

## 🛠️ Tech Stack

- **Backend**: PHP 7.4 Native (No Framework)
- **Database**: MySQL 5.7
- **Frontend**: HTML, CSS, Vanilla JavaScript
- **Deployment**: Docker & Docker Compose

## 📦 Installation

### Prerequisites
- Docker
- Docker Compose

### Quick Start

1. **Clone atau extract project ini**

2. **Jalankan Docker:**
```bash
docker-compose up -d
```

3. **Akses aplikasi:**
- App: http://localhost:8080
- PHPMyAdmin: http://localhost:8081 (user: root, password: secret123)

4. **Login default:**
- Username: `admin`
- Password: `chesta_admin`

5. **Untuk Register user baru, gunakan Secret Key:**
- Secret Key: `secret`

## 📝 Default Data

Database sudah include sample data:
- 1 user (admin)
- 5 kategori (Minuman, Makanan, Snack, Alat Tulis, Kesehatan)
- 15 produk sample

## 🔧 Configuration

Environment variables sudah di-set di `docker-compose.yml`:
- `DB_HOST=db`
- `DB_NAME=chardymart_pos`
- `DB_USER=root`
- `DB_PASSWORD=secret123`
- `SECRET_KEY=secret`

Untuk mengubah secret key, edit di `docker-compose.yml` dan restart container.

## 📁 Struktur Project

```
/chardymart-pos
├── docker-compose.yml
├── Dockerfile
├── .htaccess
├── /database
│   └── init.sql
├── /includes
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── /api
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── get-product.php
│   ├── save-transaction.php
│   ├── get-stats.php
│   └── ... (other endpoints)
├── /assets
│   ├── /css
│   │   ├── style.css
│   │   └── modal.css
│   └── /js
│       ├── main.js
│       ├── modal.js
│       ├── notification.js
│       ├── products.js
│       └── cashier.js
├── index.php (Dashboard)
├── login.php
├── register.php
├── products.php
├── cashier.php
├── transactions.php
├── generate-receipt.php
└── 404.php
```

## 🎨 Design System

- **Theme**: Vercel-like (Clean & Minimalist)
- **Colors**: White, Black, Blue accents
- **Fonts**: Inter, SF Mono
- **Components**: Custom Modal & Notification (NO native dialogs!)
- **Responsive**: Mobile-first approach

## 🔐 Security Features

- Password hashing dengan `password_hash()`
- Secret key validation untuk registrasi
- Session-based authentication
- PDO prepared statements (SQL injection protection)
- Input sanitization
- XSS protection

## 📱 Responsive Design

- Desktop: Full layout dengan sidebar
- Tablet: Optimized grid
- Mobile: Stack layout, touch-friendly buttons

## 🧪 Testing Flow

1. **Login** → Dashboard
2. **Tambah Kategori** → Products → Kelola Kategori
3. **Tambah Produk** → Products → Tambah Produk
4. **Transaksi** → Kasir → Input SKU → Proses Pembayaran
5. **Receipt** → Auto download setelah transaksi
6. **Riwayat** → Transactions → Filter & View Details

## 🚫 Important Notes

**DILARANG menggunakan:**
- `alert()` ❌
- `confirm()` ❌
- `prompt()` ❌

**WAJIB menggunakan:**
- `Modal.alert()` ✅
- `Modal.confirm()` ✅
- `Notification.show()` ✅

## 🐛 Troubleshooting

**Database connection error:**
```bash
docker-compose down
docker-compose up -d --build
```

**Port sudah digunakan:**
Edit `docker-compose.yml` dan ubah port mapping (misalnya 8080 → 8090)

**Permission error:**
```bash
sudo chown -R www-data:www-data /path/to/chardymart-pos
```

## 📊 Receipt Format

Receipt dihasilkan dalam format HTML yang bisa di-print sebagai PDF langsung dari browser:
- Thermal printer style (58mm/80mm)
- Include semua detail transaksi
- Auto calculate total, discount, change
- Format currency: Rp 25.000

## 🔄 Stop & Cleanup

```bash
# Stop containers
docker-compose down

# Stop dan hapus volumes (HATI-HATI: akan menghapus database!)
docker-compose down -v
```

## 👨‍💻 Development

Untuk development, edit file langsung. Docker volume mounting akan auto-reload changes.

## 📄 License

Free to use for educational purposes.

---

**Enjoy! 頑張って！🔥**
