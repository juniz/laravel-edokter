# Abahost - Platform Penyewaan Hosting

Aplikasi platform penyewaan layanan hosting (shared/VPS) dengan dukungan manajemen paket, domain, order, langganan (subscription), penagihan (invoice), pembayaran otomatis/manual, provisioning ke panel (cPanel/DirectAdmin/Proxmox melalui adapter), dan dukungan tiket.

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: React + Inertia.js + Tailwind CSS
- **Database**: MySQL 8/14+ atau PostgreSQL 14+
- **Cache/Queue**: Redis
- **Queue Management**: Laravel Horizon
- **Permissions**: Spatie Laravel Permission
- **Activity Log**: Spatie Laravel Activity Log
- **Media Library**: Spatie Laravel Media Library

## 📋 Persyaratan

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js & NPM
- MySQL 8+ atau PostgreSQL 14+
- Redis (untuk cache & queue)

## 🚀 Instalasi

1. Clone repository:
```bash
git clone <repository-url>
cd abahost
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Konfigurasi database di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abahost
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migrations dan seeders:
```bash
php artisan migrate
php artisan db:seed
```

6. Build frontend assets:
```bash
npm run build
# atau untuk development:
npm run dev
```

7. Jalankan aplikasi:
```bash
php artisan serve
```

## 📁 Struktur Project

```
app/
  Domain/                    # Domain Entities & Business Logic
    Billing/                 # Invoice, Payment, Refund
    Catalog/                 # Product, Plan, Coupon
    Customer/                # Customer, Domain
    Order/                   # Order, Cart
    Provisioning/            # Server, PanelAccount, ProvisionTask
    Subscription/            # Subscription, SubscriptionCycle
    Support/                 # Ticket, TicketReply
    Shared/                  # ValueObjects, Contracts, Exceptions
  Application/               # Use Cases / Services
    Billing/
    Catalog/
    Order/
    ...
  Infrastructure/            # External Implementations
    Persistence/
      Eloquent/              # Eloquent Repository implementations
    Provisioning/
      Adapters/              # cPanel, DirectAdmin, Proxmox adapters
    Payments/
      Adapters/              # Midtrans, Xendit, Tripay adapters
  Http/
    Controllers/             # HTTP Controllers
    Requests/                # Form Requests
  Jobs/                      # Queue Jobs
  Events/                    # Domain Events
  Listeners/                 # Event Listeners
```

## 🔑 Fitur Utama

### 1. Catalog Management
- Manajemen produk (Shared Hosting, VPS, Addon, Domain)
- Manajemen plans dengan berbagai billing cycle
- Plan features management
- Coupon management

### 2. Order Management
- Cart system
- Order placement
- Order tracking
- Automatic invoice generation

### 3. Billing System
- Invoice generation
- Multiple payment gateways (Midtrans, Xendit, Tripay, Manual Transfer)
- Payment tracking
- Refund management

### 4. Subscription Management
- Subscription lifecycle management
- Auto-renewal system
- Grace period handling
- Subscription cycles tracking

### 5. Provisioning System
- Multi-adapter support (cPanel, DirectAdmin, Proxmox)
- Automatic account provisioning
- Account suspension/termination
- Plan changes

### 6. Support System
- Ticket system
- Priority management
- SLA tracking
- Ticket replies

## 🏗️ Arsitektur

Aplikasi ini menggunakan **Domain-Driven Design (DDD)** dengan **Repository Pattern**:

### Domain Layer
- **Entities**: Customer, Product, Order, Invoice, Subscription, dll
- **Contracts**: Repository interfaces
- **Value Objects**: (jika diperlukan)

### Application Layer
- **Services/Use Cases**: Business logic orchestration
- Contoh: `PlaceOrderService`, `GenerateInvoiceService`

### Infrastructure Layer
- **Persistence**: Eloquent repository implementations
- **Adapters**: External service integrations

### Presentation Layer
- **Controllers**: HTTP request handling
- **Requests**: Form validation
- **React Components**: Frontend UI

## 🔄 Alur Bisnis

### 1. Order Flow
```
Customer → Pilih Produk → Add to Cart → Checkout → Create Order → Generate Invoice → Payment → Provision Account
```

### 2. Payment Flow
```
Invoice Created → Payment Gateway → Webhook → Mark Invoice Paid → Trigger Provisioning
```

### 3. Provisioning Flow
```
Invoice Paid → Dispatch ProvisionAccountJob → Adapter.createAccount() → Update Subscription → Send Welcome Email
```

### 4. Renewal Flow
```
Daily Cron → Check Due Subscriptions → Generate Invoice → Charge Payment → Extend Subscription
```

## 📝 Configuration

### Payment Gateways
Konfigurasi di `.env`:
```env
PAYMENT_DEFAULT=manual
MIDTRANS_SERVER_KEY=
XENDIT_API_KEY=
TRIPAY_API_KEY=
```

### Provisioning Adapters
Konfigurasi di `.env`:
```env
PROVISIONING_DEFAULT=cpanel
```

### Queue
Pastikan Redis sudah running dan konfigurasi queue:
```env
QUEUE_CONNECTION=redis
```

Jalankan queue worker:
```bash
php artisan queue:work
# atau menggunakan Horizon:
php artisan horizon
```

## 🧪 Testing

```bash
php artisan test
```

## 📅 Scheduled Tasks

Aplikasi memiliki scheduled tasks di `routes/console.php`:
- **RenewSubscriptionJob**: Berjalan setiap hari jam 02:00 untuk auto-renewal

Pastikan cron job sudah setup:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🔐 Roles & Permissions

Default roles:
- **admin**: Full access
- **billing**: Invoice & payment management
- **support**: Ticket management
- **customer**: Customer access

## 📚 API Documentation

API endpoints akan tersedia di `/api` (jika diperlukan).

## 🐛 Troubleshooting

### Migration Issues
Jika ada masalah dengan ULID:
```bash
php artisan migrate:fresh
```

### Queue Not Running
Pastikan Redis sudah running:
```bash
redis-cli ping
```

### Adapter Not Found
Pastikan service provider sudah terdaftar di `bootstrap/providers.php`

## 📄 License

MIT License

## 👥 Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📞 Support

Untuk pertanyaan atau dukungan, silakan buat issue di repository.

---

**Dibuat dengan ❤️ menggunakan Laravel + React + Inertia**
