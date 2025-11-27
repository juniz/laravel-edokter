# 🚀 Audit Log Queue System

## 📋 **Overview**

Sistem audit log yang menggunakan Laravel Queue untuk memproses log database secara asynchronous, menggantikan variable `$busy` dengan pendekatan yang lebih robust dan scalable.

## ✨ **Fitur Utama**

### 🔄 **Queue-based Processing**

- **Asynchronous**: Log diproses di background tanpa blocking main thread
- **Scalable**: Bisa handle multiple workers
- **Reliable**: Built-in retry mechanism dan error handling
- **Performance**: Tidak mempengaruhi response time aplikasi

### 🛡️ **Duplicate Prevention**

- **Query Hash**: Unique identifier untuk setiap query
- **Cache Lock**: Mencegah duplikasi dalam satu request
- **TTL Management**: Automatic cleanup untuk mencegah memory leak

### 📊 **Monitoring & Debugging**

- **Queue Status**: Command untuk monitoring queue
- **Statistics**: Performance metrics dan analytics
- **Failed Jobs**: Tracking dan management untuk failed jobs

## 🏗️ **Architecture**

```
DB Query → AppServiceProvider → ProcessAuditLog Job → Queue → Worker → Database
    ↓              ↓                    ↓           ↓       ↓        ↓
  Listen      Dispatch Job         Background   Redis/DB  Process  Audit Log
  Event       to Queue            Processing   Storage   Job      Insert
```

## 📁 **File Structure**

```
app/
├── Jobs/
│   └── ProcessAuditLog.php          # Job untuk memproses audit log
├── Console/Commands/
│   └── MonitorAuditQueue.php        # Command untuk monitoring
└── Providers/
    └── AppServiceProvider.php       # DB listener & job dispatch

database/migrations/
└── 2025_01_18_000000_add_query_hash_to_audit_sql_logs_table.php

config/
└── queue.php                        # Queue configuration
```

## 🚀 **Installation & Setup**

### 1. **Run Migration**

```bash
php artisan migrate
```

### 2. **Start Queue Worker**

```bash
# Development
php artisan queue:work --queue=audit-logs

# Production (with supervisor)
php artisan queue:work --queue=audit-logs --tries=3 --timeout=30
```

### 3. **Verify Setup**

```bash
php artisan audit:monitor
```

## 📊 **Usage Examples**

### **Monitor Queue Status**

```bash
# Basic status
php artisan audit:monitor

# Statistics
php artisan audit:monitor --stats

# Failed jobs
php artisan audit:monitor --failed

# Clear failed jobs
php artisan audit:monitor --clear
```

### **Queue Configuration**

```bash
# Set queue driver
QUEUE_CONNECTION=database

# Set queue name
QUEUE_NAME=audit-logs
```

## 🔧 **Configuration**

### **Queue Settings**

```php
// config/queue.php
'audit-logs' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'audit-logs',
    'retry_after' => 300,    // 5 menit
    'after_commit' => false,
    'timeout' => 30,         // 30 detik
],
```

### **Job Settings**

```php
// app/Jobs/ProcessAuditLog.php
public $timeout = 30;        // 30 detik timeout
public $tries = 3;           // Retry 3 kali
public $maxExceptions = 3;   // Maksimal 3 exception
```

## 📈 **Performance Benefits**

### **Before (Variable \$busy)**

- ❌ **Blocking**: Main thread terblock saat insert log
- ❌ **Race Conditions**: Duplikasi log masih terjadi
- ❌ **No Retry**: Jika gagal, log hilang
- ❌ **Memory Issues**: Static variable tidak reliable

### **After (Queue-based)**

- ✅ **Non-blocking**: Main thread tidak terpengaruh
- ✅ **No Duplicates**: Query hash + cache lock
- ✅ **Auto Retry**: Built-in retry mechanism
- ✅ **Scalable**: Multiple workers support
- ✅ **Monitoring**: Full visibility ke queue status

## 🛠️ **Troubleshooting**

### **Common Issues**

#### 1. **Queue Not Processing**

```bash
# Check queue status
php artisan queue:work --queue=audit-logs

# Check failed jobs
php artisan audit:monitor --failed
```

#### 2. **High Memory Usage**

```bash
# Clear failed jobs
php artisan audit:monitor --clear

# Restart queue worker
php artisan queue:restart
```

#### 3. **Duplicate Logs**

```bash
# Check cache configuration
php artisan config:cache

# Verify query hash uniqueness
php artisan audit:monitor --stats
```

### **Debug Commands**

```bash
# Queue size
php artisan queue:size audit-logs

# Failed jobs count
php artisan queue:failed --queue=audit-logs

# Clear all queues
php artisan queue:flush
```

## 📊 **Monitoring & Metrics**

### **Key Metrics**

- **Queue Size**: Jumlah pending jobs
- **Processing Time**: Rata-rata waktu proses
- **Success Rate**: Persentase job berhasil
- **Error Rate**: Persentase job gagal

### **Alerts**

```bash
# Monitor queue size
if [ $(php artisan queue:size audit-logs) -gt 100 ]; then
    echo "WARNING: Audit queue size > 100"
fi

# Monitor failed jobs
if [ $(php artisan queue:failed --queue=audit-logs | wc -l) -gt 10 ]; then
    echo "WARNING: Too many failed audit jobs"
fi
```

## 🔒 **Security Considerations**

### **Data Protection**

- **Query Hash**: Mencegah SQL injection tracking
- **Binding Limits**: Mencegah BLOB overflow
- **TTL Management**: Mencegah cache poisoning
- **User Isolation**: Session-based user tracking

### **Access Control**

```php
// Hanya user yang login yang di-track
$username = session()?->get('username');
if (!$username) return;
```

## 🚀 **Production Deployment**

### **Supervisor Configuration**

```ini
[program:laravel-audit-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=audit-logs --tries=3 --timeout=30
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/audit-queue.log
```

### **Environment Variables**

```env
QUEUE_CONNECTION=database
QUEUE_NAME=audit-logs
AUDIT_LOG_ENABLED=true
AUDIT_LOG_TTL=300
```

## 📚 **API Reference**

### **ProcessAuditLog Job**

```php
// Dispatch job
ProcessAuditLog::dispatch($queryData);

// Dispatch with delay
ProcessAuditLog::dispatch($queryData)->delay(now()->addMinutes(5));

// Dispatch with specific queue
ProcessAuditLog::dispatch($queryData)->onQueue('high-priority');
```

### **MonitorAuditQueue Command**

```php
// Basic monitoring
$this->call('audit:monitor');

// With options
$this->call('audit:monitor', ['--stats' => true]);
```

## 🔄 **Migration from Old System**

### **Step 1: Backup**

```bash
# Backup existing audit logs
php artisan tinker
DB::table('audit_sql_logs')->get()->toArray();
```

### **Step 2: Update Code**

```php
// Old way (variable $busy)
static $busy = false;
if ($busy) return;
$busy = true;
// ... insert log
$busy = false;

// New way (queue-based)
ProcessAuditLog::dispatch($queryData);
```

### **Step 3: Test & Deploy**

```bash
# Test in development
php artisan queue:work --queue=audit-logs

# Deploy to production
php artisan migrate
php artisan queue:restart
```

## 📞 **Support & Maintenance**

### **Regular Maintenance**

```bash
# Daily
php artisan audit:monitor --stats

# Weekly
php artisan audit:monitor --failed
php artisan queue:flush --queue=audit-logs

# Monthly
php artisan queue:restart
```

### **Performance Tuning**

```bash
# Increase workers
numprocs=4

# Adjust TTL
AUDIT_LOG_TTL=600

# Monitor memory usage
php artisan audit:monitor --stats
```

---

**🎯 Goal**: Mengganti variable `$busy` dengan sistem queue yang robust, scalable, dan reliable untuk audit logging.

**✅ Benefits**: Non-blocking, no duplicates, auto-retry, monitoring, dan performance yang lebih baik.
