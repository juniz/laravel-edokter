# Implementasi User Management dengan RDASH API Integration

Dokumentasi implementasi integrasi User Management dengan RDASH API sesuai dengan plan.md.

## ✅ Yang Sudah Diimplementasikan

### 1. Database Migration
- ✅ Migration untuk menambahkan kolom RDASH sync ke tabel `customers`
  - `rdash_customer_id` (integer nullable)
  - `rdash_synced_at` (datetime nullable)
  - `rdash_sync_status` (enum: pending|synced|failed)
  - `rdash_sync_error` (text nullable)
  - Index untuk `rdash_customer_id` dan `rdash_sync_status`

### 2. Domain Layer
- ✅ Update Model `Customer` dengan kolom RDASH sync
- ✅ Update `CustomerRepository` contract dengan method `update()`

### 3. Application Layer (Use Cases)
- ✅ `SyncUserToRdashService` - Sync user ke RDASH customer
- ✅ `CreateUserWithRdashCustomerService` - Create user sekaligus create customer di RDASH
- ✅ `GetRdashCustomerForUserService` - Get RDASH customer details untuk user
- ✅ `BulkSyncUsersToRdashService` - Sync multiple users ke RDASH dalam batch

### 4. Infrastructure Layer (Jobs)
- ✅ `SyncUserToRdashJob` - Job untuk async sync user ke RDASH
- ✅ `BulkSyncUsersToRdashJob` - Job untuk bulk sync multiple users

### 5. Events & Listeners
- ✅ `UserCreated` event
- ✅ `UserUpdated` event
- ✅ `CustomerCreated` event
- ✅ `CustomerUpdated` event
- ✅ `SyncUserToRdashListener` - Auto sync saat user created/updated
- ✅ `SyncCustomerToRdashListener` - Auto sync saat customer created/updated
- ✅ `EventServiceProvider` untuk register event listeners

### 6. Controllers
- ✅ Update `UserController`:
  - `index()` - Menampilkan users dengan RDASH sync status
  - `show()` - Menampilkan user details dengan RDASH integration tab
  - `store()` - Create user dengan optional RDASH sync
  - `update()` - Update user dengan optional RDASH sync
  - `syncRdash()` - Manual sync user ke RDASH
  - `bulkSyncRdash()` - Bulk sync multiple users
  - `getRdashCustomer()` - Get RDASH customer details

### 7. Routes
- ✅ `POST /users/{user}/sync-rdash` - Manual sync user ke RDASH
- ✅ `POST /users/bulk-sync-rdash` - Bulk sync multiple users
- ✅ `GET /users/{user}/rdash-customer` - Get RDASH customer details

### 8. UI Components (React/Inertia)
- ✅ Update `UserIndex.tsx`:
  - Menampilkan RDASH sync status badge
  - Menampilkan RDASH Customer ID
  - Button untuk sync ke RDASH
  - Link ke user details page
  
- ✅ Update `UserForm.tsx`:
  - Checkbox "Create customer in RDASH" untuk create user
  - Checkbox "Sync to RDASH after update" untuk edit user
  
- ✅ Create `UserShow.tsx`:
  - Tab "Details" untuk user information
  - Tab "RDASH Integration" untuk RDASH customer details
  - Button untuk sync ke RDASH
  - Display sync status, error, dan customer details

- ✅ Create `RdashSyncStatusBadge.tsx` - Component untuk menampilkan status sync
- ✅ Create `SyncToRdashButton.tsx` - Component untuk trigger sync

### 9. Configuration
- ✅ Update `config/rdash.php` dengan auto sync settings:
  - `auto_sync_on_user_create`
  - `auto_sync_on_customer_create`
  - `auto_sync_on_customer_update`

### 10. Service Providers
- ✅ `EventServiceProvider` untuk register event listeners
- ✅ Register `EventServiceProvider` di `bootstrap/providers.php`

## 📋 Cara Menggunakan

### 1. Install Dependencies
```bash
npm install
# atau
npm install @radix-ui/react-tabs
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Setup Environment Variables
Tambahkan ke `.env`:
```env
RDASH_API_URL=https://api.rdash.id/v1
RDASH_RESELLER_ID=your_reseller_id
RDASH_API_KEY=your_api_key
RDASH_AUTO_SYNC_ON_USER_CREATE=false
RDASH_AUTO_SYNC_ON_CUSTOMER_CREATE=true
RDASH_AUTO_SYNC_ON_CUSTOMER_UPDATE=true
```

### 4. Start Queue Worker
```bash
php artisan queue:work
# atau menggunakan Horizon
php artisan horizon
```

## 🔄 Alur Kerja

### Manual Sync
1. Admin buka User Management (`/users`)
2. Klik button "Sync to RDASH" pada user yang ingin di-sync
3. System akan dispatch `SyncUserToRdashJob`
4. Job akan sync user ke RDASH API
5. Status akan ter-update di UI

### Auto Sync
1. Saat user dibuat dengan checkbox "Create customer in RDASH" dicentang
2. System akan dispatch `SyncUserToRdashJob` otomatis
3. Atau jika `RDASH_AUTO_SYNC_ON_USER_CREATE=true`, akan auto sync tanpa checkbox

### Bulk Sync
1. Admin pilih multiple users
2. Klik button "Bulk Sync"
3. System akan dispatch `BulkSyncUsersToRdashJob`
4. Semua users akan di-sync dalam batch

## 🎨 UI Features

### User Index Page
- ✅ RDASH Sync Status Badge (Synced/Pending/Failed)
- ✅ RDASH Customer ID display
- ✅ Sync button untuk manual sync
- ✅ Link ke user details page

### User Form Page
- ✅ Checkbox untuk create/sync ke RDASH
- ✅ Informasi tentang RDASH integration

### User Show Page
- ✅ Tab "Details" - User information
- ✅ Tab "RDASH Integration" - RDASH customer details
- ✅ Sync status dan error display
- ✅ Button untuk sync ke RDASH

## 🧪 Testing

Untuk testing, pastikan:
1. RDASH API credentials sudah benar di `.env`
2. Queue worker sudah running
3. Test dengan user yang belum punya customer di RDASH
4. Test dengan user yang sudah punya customer di RDASH
5. Test bulk sync dengan multiple users

## 📝 Catatan Penting

1. **Password untuk RDASH**: Saat create customer di RDASH, password default adalah `TempPassword123!`. User harus mengubah password ini di RDASH dashboard.

2. **Error Handling**: Jika sync gagal, error akan disimpan di `rdash_sync_error` dan status akan menjadi `failed`. Admin dapat retry sync dari UI.

3. **Retry Mechanism**: Job memiliki retry mechanism dengan exponential backoff. Jika masih gagal setelah 3 attempts, status akan menjadi `failed`.

4. **Auto Sync**: Auto sync dapat di-disable melalui config `RDASH_AUTO_SYNC_ON_USER_CREATE=false`.

## 🚀 Next Steps

- [ ] Implementasi UI untuk manage RDASH contacts
- [ ] Implementasi UI untuk view domains di RDASH
- [ ] Implementasi bulk sync dengan checkbox selection
- [ ] Implementasi filter di User Index berdasarkan RDASH sync status
- [ ] Unit tests untuk Use Cases
- [ ] Integration tests untuk sync operations

