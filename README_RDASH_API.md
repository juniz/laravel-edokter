# RDASH API Integration

Implementasi API RDASH dengan pendekatan Domain Driven Design (DDD) untuk memudahkan maintenance dan pengembangan.

## Struktur Domain

Implementasi mengikuti struktur Domain Driven Design dengan pemisahan yang jelas:

```
app/
  Domain/
    Rdash/
      Account/          # Account management (profile, balance, prices, transactions)
      Customer/         # Customer management
      Contact/          # Contact management
      Domain/           # Domain management
      Dns/              # DNS management
      Host/             # Child nameserver management
      Forwarding/       # Domain forwarding
      WhoisProtection/  # Whois protection
      Ssl/              # SSL certificate management
      ObjectStorage/    # Object storage management
      BareMetal/        # Bare metal server management
  Infrastructure/
    Rdash/
      HttpClient.php           # HTTP client untuk komunikasi dengan RDASH API
      Repositories/            # Implementasi repository untuk setiap domain
  Application/
    Rdash/                     # Use cases / Services
  Http/
    Controllers/
      Api/
        Rdash/                 # API Controllers
```

## Konfigurasi

Tambahkan konfigurasi berikut ke file `.env`:

```env
RDASH_API_URL=https://api.rdash.id/v1
RDASH_RESELLER_ID=your_reseller_id
RDASH_API_KEY=your_api_key
RDASH_TIMEOUT=30
RDASH_RETRY_TIMES=3
RDASH_RETRY_DELAY=100
```

## Penggunaan

### Account Management

```php
use App\Domain\Rdash\Account\Contracts\AccountRepository;

// Get profile
$profile = app(AccountRepository::class)->getProfile();

// Get balance
$balance = app(AccountRepository::class)->getBalance();

// Get domain prices
$prices = app(AccountRepository::class)->getPrices([
    'domainExtension.extension' => '.co.id',
    'promo' => true,
]);
```

### Domain Management

```php
use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;

$domainRepository = app(RdashDomainRepository::class);

// Check availability
$availability = $domainRepository->checkAvailability('example.com');

// Register domain
$domain = $domainRepository->register([
    'name' => 'example.com',
    'period' => 1,
    'customer_id' => 123,
    'nameserver[0]' => 'ns1.example.com',
    'nameserver[1]' => 'ns2.example.com',
]);

// Get domain by id
$domain = $domainRepository->getById(123);

// Update nameservers
$domain = $domainRepository->updateNameservers(123, [
    'ns1.example.com',
    'ns2.example.com',
]);
```

### DNS Management

```php
use App\Domain\Rdash\Dns\Contracts\DnsRepository;

$dnsRepository = app(DnsRepository::class);

// Get DNS records
$records = $dnsRepository->getRecords(123);

// Create DNS records
$dnsRepository->createRecords(123, [
    [
        'name' => 'www',
        'type' => 'A',
        'content' => '192.168.1.1',
        'ttl' => 3600,
    ],
]);

// Update DNS record
$dnsRepository->updateRecord(123, [
    'name' => 'www',
    'type' => 'A',
    'content' => '192.168.1.2',
    'ttl' => 3600,
]);
```

## API Endpoints

Semua endpoint menggunakan prefix `/api/rdash`:

### Account Endpoints

- `GET /api/rdash/account/profile` - Get reseller profile
- `GET /api/rdash/account/balance` - Get balance amount
- `GET /api/rdash/account/prices` - Get list all domain prices
- `GET /api/rdash/account/prices/{priceId}` - Get domain price details
- `GET /api/rdash/account/transactions` - Get list all transactions
- `GET /api/rdash/account/transactions/{transactionId}` - Get transaction details

### Domain Endpoints

- `GET /api/rdash/domains` - Get list all domains
- `GET /api/rdash/domains/{domainId}` - Get domain by id
- `GET /api/rdash/domains/availability/check?domain=example.com` - Check domain availability
- `GET /api/rdash/domains/whois/check?domain=example.com` - Get domain whois info
- `POST /api/rdash/domains/register` - Register new domain
- `POST /api/rdash/domains/transfer` - Transfer domain
- `POST /api/rdash/domains/{domainId}/renew` - Renew domain
- `PUT /api/rdash/domains/{domainId}/nameservers` - Update nameservers
- `GET /api/rdash/domains/{domainId}/auth-code` - Get auth code
- `PUT /api/rdash/domains/{domainId}/auth-code` - Reset auth code
- `PUT /api/rdash/domains/{domainId}/lock` - Lock domain
- `DELETE /api/rdash/domains/{domainId}/lock` - Unlock domain
- `PUT /api/rdash/domains/{domainId}/suspend` - Suspend domain
- `DELETE /api/rdash/domains/{domainId}/suspend` - Unsuspend domain

## Arsitektur

### Domain Layer

Setiap domain memiliki:

- **Contracts**: Interface repository yang mendefinisikan kontrak untuk akses data
- **ValueObjects**: Immutable objects yang merepresentasikan data domain
- **Exceptions**: Custom exceptions untuk domain tersebut

### Infrastructure Layer

- **HttpClient**: Wrapper untuk HTTP client dengan retry logic dan error handling
- **Repositories**: Implementasi konkret dari repository contracts menggunakan HTTP client

### Application Layer

- **Services/Use Cases**: Business logic orchestration yang menggunakan repository

### Presentation Layer

- **Controllers**: HTTP request handling dengan validasi dan response formatting

## Keuntungan Pendekatan DDD

1. **Separation of Concerns**: Setiap layer memiliki tanggung jawab yang jelas
2. **Testability**: Mudah untuk membuat mock repository untuk testing
3. **Maintainability**: Perubahan di satu layer tidak mempengaruhi layer lain
4. **Extensibility**: Mudah menambah fitur baru tanpa mengubah kode yang sudah ada
5. **Type Safety**: Value Objects memastikan data konsisten dan valid

## Testing

Contoh testing dengan mock repository:

```php
use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Domain\Rdash\Account\ValueObjects\AccountProfile;

$mockRepository = Mockery::mock(AccountRepository::class);
$mockRepository->shouldReceive('getProfile')
    ->once()
    ->andReturn(new AccountProfile(
        id: 1,
        name: 'Test Reseller',
        email: 'test@example.com'
    ));

app()->instance(AccountRepository::class, $mockRepository);

$profile = app(AccountRepository::class)->getProfile();
```

## Error Handling

Semua error dari RDASH API akan di-log dan di-throw sebagai `RuntimeException` dengan pesan yang jelas. HTTP client juga memiliki retry mechanism untuk menangani transient errors.

## Catatan

- Semua request menggunakan Basic Auth dengan Reseller ID dan API Key
- Timeout default adalah 30 detik
- Retry mechanism: 3 kali dengan delay 100ms
- Semua response di-parse menjadi Value Objects untuk type safety
