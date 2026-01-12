# Analisis Error aaPanel API

## 📋 Error yang Terjadi

### Log Error

```
[2026-01-10 19:20:59] local.INFO: aaPanel API Response {
  "url": "https://192.168.100.7:12636/v2/virtual/get_package_list.json",
  "action": "v2/virtual/get_package_list.json",
  "status": 200,
  "response": {
    "code": 500,
    "error_msg": "HTTPSConnectionPool(host='127.0.0.1', port=46907): Max retries exceeded with url: /account/get_package_list (Caused by NewConnectionError('<urllib3.connection.HTTPSConnection object at 0x7f098023c260>: Failed to establish a new connection: [Errno -9] Address family for hostname not supported'))",
    "message": [],
    "msg": "Request failed: HTTPSConnectionPool(host='127.0.0.1', port=46907): Max retries exceeded with url: /account/get_package_list (Caused by NewConnectionError('<urllib3.connection.HTTPSConnection object at 0x7f098023c260>: Failed to establish a new connection: [Errno -9] Address family for hostname not supported'))",
    "status": -1
  }
}
```

## 🔍 Analisis Error

### 1. HTTP Status vs Response Body

| Aspek               | Nilai | Keterangan                                |
| ------------------- | ----- | ----------------------------------------- |
| **HTTP Status**     | `200` | ✅ Request berhasil sampai ke aaPanel API |
| **Response Status** | `-1`  | ❌ Error dari aaPanel internal service    |
| **Response Code**   | `500` | ❌ Internal server error di aaPanel       |

### 2. Root Cause Analysis

**Masalah Utama:**

- aaPanel API berhasil menerima request kita (HTTP 200)
- Tapi **aaPanel internal service** (virtual/multi-user service) gagal melakukan operasi internal
- Error terjadi saat aaPanel mencoba connect ke port `46907` di `127.0.0.1` (localhost)

**Error Detail:**

```
HTTPSConnectionPool(host='127.0.0.1', port=46907): Max retries exceeded
Error: [Errno -9] Address family for hostname not supported
```

### 3. Penyebab Error

Error `[Errno -9] Address family for hostname not supported` biasanya terjadi karena:

1. **Masalah Network Configuration**

   - IPv4/IPv6 mismatch
   - Network interface tidak terkonfigurasi dengan benar
   - Firewall atau security policy memblokir koneksi

2. **Masalah di aaPanel Server**

   - Virtual/multi-user service tidak bisa connect ke internal port
   - Port 46907 tidak listening atau tidak accessible
   - Service configuration error

3. **Masalah Konfigurasi aaPanel**
   - Virtual service port configuration salah
   - Service binding ke interface yang salah

### 4. Informasi dari Log Sebelumnya

Dari log sebelumnya (line 1552), kita tahu:

```json
{
	"message": {
		"install_status": 2, // ✅ Terinstall
		"run_status": 1, // ✅ Running
		"server_port": "46907", // Port yang digunakan
		"version": "2.2.8"
	},
	"status": 0 // ✅ Success
}
```

**Kesimpulan:**

- Virtual service terinstall dan running ✅
- Port yang digunakan: `46907` ✅
- Tapi saat melakukan operasi (get_package_list), service gagal connect ke port tersebut ❌

## 🛠️ Solusi yang Sudah Diterapkan

### 1. Improved Error Handling

**File:** `app/Infrastructure/Provisioning/AaPanel/HttpClient.php`

**Perubahan:**

- Menambahkan pengecekan response body untuk error dari aaPanel
- Check `status` field: `0` = success, `-1` atau lainnya = error
- Check `code` field: `500` atau error code lainnya
- Throw exception dengan pesan error yang jelas

**Sebelum:**

```php
if (! $response->successful()) {
    throw new \Exception('aaPanel API request failed: ' . $response->body());
}
return $responseData ?? [];
```

**Sesudah:**

```php
// Check HTTP status
if (! $response->successful()) {
    throw new \Exception('aaPanel API request failed: '.$response->body());
}

// Check response body untuk error dari aaPanel
// aaPanel API mengembalikan HTTP 200 meskipun ada error di response body
if (isset($responseData['status']) && (int) $responseData['status'] !== 0) {
    $errorMsg = $responseData['msg'] ?? $responseData['error_msg'] ?? 'Unknown error';
    $errorCode = $responseData['code'] ?? $responseData['status'] ?? 'unknown';
    throw new \Exception("aaPanel API error (code: {$errorCode}): {$errorMsg}");
}

if (isset($responseData['code']) && (int) $responseData['code'] !== 0 && (int) $responseData['code'] !== 200) {
    $errorMsg = $responseData['msg'] ?? $responseData['error_msg'] ?? 'Unknown error';
    $errorCode = $responseData['code'];
    throw new \Exception("aaPanel API error (code: {$errorCode}): {$errorMsg}");
}
```

### 2. Better Logging

- Log error dengan detail lengkap (error_code, error_msg, response)
- Memudahkan debugging di masa depan

## 🔧 Solusi untuk Admin Server

### Analisis Log Server aaPanel

Dari log server aaPanel yang diberikan:

```
[DEBUG] - vhost---------Use custom port: 46907
[DEBUG] - vhost---------Host config file missing, use default host: 127.0.0.1
[DEBUG] - vhost---------Constructed request URL: https://127.0.0.1:46907/account/get_package_list
[DEBUG] - vhost---------Error: Request failed: HTTPSConnectionPool(host='127.0.0.1', port=46907): Max retries exceeded
```

**Masalah yang Teridentifikasi:**

1. ✅ Port 46907 digunakan (custom port)
2. ❌ **Host config file missing** - menggunakan default `127.0.0.1`
3. ❌ Service mencoba connect via **HTTPS** ke `127.0.0.1:46907` tapi gagal
4. ❌ Connection timeout/max retries exceeded

### Root Cause

**Masalah Utama:**

- Virtual service mencoba connect ke `https://127.0.0.1:46907` (HTTPS)
- Tapi service mungkin hanya listen di **HTTP** (bukan HTTPS)
- Atau service tidak running di port tersebut
- Atau ada masalah dengan SSL certificate untuk localhost

### Checklist Troubleshooting

1. **Cek Status Virtual Service**

   ```bash
   # Login ke server aaPanel
   # Cek apakah virtual service running
   ps aux | grep vhost
   # atau
   systemctl status bt-vhost
   ```

2. **Cek Port 46907 - Apakah Listening?**

   ```bash
   # Cek apakah port listening
   netstat -tlnp | grep 46907
   # atau
   ss -tlnp | grep 46907
   # atau
   lsof -i :46907
   ```

3. **Cek Protocol (HTTP vs HTTPS)**

   ```bash
   # Cek apakah service listen di HTTP atau HTTPS
   # Jika listen di HTTP, URL harus http:// bukan https://
   curl -k http://127.0.0.1:46907/account/get_package_list
   curl -k https://127.0.0.1:46907/account/get_package_list
   ```

4. **Cek Host Config File**

   ```bash
   # Log menunjukkan "Host config file missing"
   # Cek apakah file config ada
   # Biasanya di /www/server/panel/vhost/ atau /www/server/panel/config/
   ls -la /www/server/panel/vhost/
   ls -la /www/server/panel/config/
   ```

5. **Cek Network Configuration**

   ```bash
   # Cek IPv4/IPv6 configuration
   ip addr show
   # Cek firewall rules
   iptables -L -n
   # Cek apakah localhost bisa diakses
   ping 127.0.0.1
   ```

6. **Restart Virtual Service**

   ```bash
   # Restart virtual/multi-user service di aaPanel
   # Via command line:
   /etc/init.d/bt-vhost restart
   # atau
   systemctl restart bt-vhost
   # atau melalui panel aaPanel
   ```

7. **Cek Log aaPanel**
   ```bash
   # Cek log aaPanel untuk detail error
   tail -f /www/server/panel/logs/error.log
   tail -f /www/server/panel/logs/vhost.log
   ```

### Kemungkinan Solusi Berdasarkan Log

#### Solusi 1: Fix Host Config File (PRIORITAS TINGGI)

Log menunjukkan: **"Host config file missing, use default host: 127.0.0.1"**

**Action:**

1. Cek apakah file config ada di `/www/server/panel/vhost/` atau `/www/server/panel/config/`
2. Jika tidak ada, buat file config atau reinstall virtual service
3. Pastikan config file memiliki setting yang benar untuk host dan port

**Command:**

```bash
# Cek apakah file config ada
ls -la /www/server/panel/vhost/
ls -la /www/server/panel/config/

# Jika tidak ada, mungkin perlu reinstall virtual service
```

#### Solusi 2: Fix Protocol (HTTP vs HTTPS)

Service mencoba connect via **HTTPS** tapi mungkin service hanya listen di **HTTP**.

**Action:**

1. Cek apakah service listen di HTTP atau HTTPS
2. Jika listen di HTTP, mungkin perlu update config untuk menggunakan HTTP
3. Atau install SSL certificate untuk localhost

**Test:**

```bash
# Test HTTP
curl -v http://127.0.0.1:46907/account/get_package_list

# Test HTTPS
curl -k -v https://127.0.0.1:46907/account/get_package_list
```

#### Solusi 3: Restart Virtual Service

**Action:**

```bash
# Restart service
/etc/init.d/bt-vhost restart
# atau
systemctl restart bt-vhost

# Cek status setelah restart
systemctl status bt-vhost
```

#### Solusi 4: Reinstall Virtual Service

Jika masalah persist:

**Action:**

1. Uninstall virtual service melalui panel aaPanel
2. Reinstall virtual service
3. Pastikan semua dependencies terinstall
4. Cek log setelah reinstall

#### Solusi 5: Update aaPanel

**Action:**

1. Update aaPanel ke versi terbaru jika ada bug fix
2. Versi saat ini: 2.2.8
3. Cek changelog untuk fix terkait virtual service

#### Solusi 6: Fix Network/Firewall

**Action:**

```bash
# Pastikan localhost bisa diakses
ping 127.0.0.1

# Cek firewall
iptables -L -n | grep 46907

# Jika perlu, allow port
iptables -A INPUT -p tcp --dport 46907 -j ACCEPT
```

## 📝 Format Response aaPanel

### Success Response

```json
{
	"status": 0,
	"message": {
		// data here
	}
}
```

### Error Response

```json
{
	"status": -1,
	"code": 500,
	"msg": "Error message",
	"error_msg": "Detailed error message",
	"message": []
}
```

### Status Codes

| Status | Keterangan                                 |
| ------ | ------------------------------------------ |
| `0`    | Success                                    |
| `-1`   | Error                                      |
| `500`  | Internal Server Error                      |
| `200`  | HTTP Success (tapi bisa ada error di body) |

## ⚠️ Catatan Penting

1. **HTTP 200 ≠ Success**

   - aaPanel API bisa mengembalikan HTTP 200 meskipun ada error
   - Selalu check `status` field di response body
   - `status: 0` = success, `status: -1` = error

2. **Error Handling**

   - Sekarang HttpClient akan throw exception jika ada error di response body
   - Error message akan lebih informatif dengan error code dan message

3. **Logging**
   - Semua error akan di-log dengan detail lengkap
   - Memudahkan troubleshooting di masa depan

## 🎯 Kesimpulan

Error ini adalah masalah di sisi **aaPanel server**, bukan di kode aplikasi kita. Masalahnya adalah:

1. ✅ Request kita berhasil sampai ke aaPanel API
2. ✅ Virtual service terinstall dan running
3. ❌ Tapi saat melakukan operasi internal, service gagal connect ke port 46907

**Action Items:**

- ✅ Sudah diperbaiki: Error handling di HttpClient untuk detect error di response body
- ⚠️ Perlu action: Admin server perlu troubleshoot masalah network/service di aaPanel server
