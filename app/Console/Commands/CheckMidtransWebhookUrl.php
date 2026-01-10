<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class CheckMidtransWebhookUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:check-webhook-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek URL webhook Midtrans dan konfigurasi yang diperlukan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Pengecekan URL Webhook Midtrans');
        $this->newLine();

        // Get webhook URL
        $webhookUrl = $this->getWebhookUrl();
        $appUrl = config('app.url');

        // Display current configuration
        $this->info('📋 Konfigurasi Saat Ini:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['APP_URL', $appUrl],
                ['Webhook URL', $webhookUrl],
                ['Route Name', 'payments.midtrans.webhook'],
                ['Route Path', '/api/payments/midtrans/webhook'],
            ]
        );

        $this->newLine();

        // Check if route exists
        $this->info('✅ Pengecekan Route:');
        try {
            $routes = Route::getRoutes();
            $route = null;

            // Try to find route by name
            try {
                $route = $routes->getByName('payments.midtrans.webhook');
            } catch (\Exception $e) {
                // Route might not be registered yet, check file instead
            }

            // Check if route file exists
            $apiRoutesFile = base_path('routes/api.php');
            if (file_exists($apiRoutesFile)) {
                $apiRoutesContent = file_get_contents($apiRoutesFile);
                if (strpos($apiRoutesContent, 'midtrans/webhook') !== false) {
                    $this->line('  ✓ Route file ditemukan: routes/api.php');
                    $this->line('  ✓ Route path: /api/payments/midtrans/webhook');
                    $this->line('  ✓ Method: POST');

                    // Check if route is registered in bootstrap
                    if ($route) {
                        $this->line('  ✓ Route terdaftar di aplikasi');
                    } else {
                        $this->warn('  ⚠ Route belum terdaftar di bootstrap/app.php');
                        $this->line('     Pastikan routes/api.php di-load di bootstrap/app.php');
                    }
                } else {
                    $this->error('  ✗ Route tidak ditemukan di routes/api.php!');

                    return Command::FAILURE;
                }
            } else {
                $this->error('  ✗ File routes/api.php tidak ditemukan!');

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Error saat mengecek route: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $this->newLine();

        // Check environment configuration
        $this->info('⚙️  Konfigurasi Environment:');
        $midtransServerKey = config('payment.midtrans.server_key');
        $midtransClientKey = config('payment.midtrans.client_key');
        $isProduction = config('payment.midtrans.is_production');
        $verifySignature = config('payment.midtrans.verify_webhook_signature');

        $this->table(
            ['Setting', 'Status', 'Value'],
            [
                [
                    'MIDTRANS_SERVER_KEY',
                    $midtransServerKey ? '✓ Set' : '✗ Not Set',
                    $midtransServerKey ? substr($midtransServerKey, 0, 20) . '...' : 'Not configured',
                ],
                [
                    'MIDTRANS_CLIENT_KEY',
                    $midtransClientKey ? '✓ Set' : '✗ Not Set',
                    $midtransClientKey ? substr($midtransClientKey, 0, 20) . '...' : 'Not configured',
                ],
                [
                    'MIDTRANS_IS_PRODUCTION',
                    $isProduction ? 'Production' : 'Sandbox',
                    $isProduction ? 'true' : 'false',
                ],
                [
                    'MIDTRANS_VERIFY_WEBHOOK_SIGNATURE',
                    $verifySignature ? '✓ Enabled' : '⚠ Disabled',
                    $verifySignature ? 'true' : 'false',
                ],
            ]
        );

        $this->newLine();

        // Instructions for Midtrans Dashboard
        $this->info('📝 Instruksi Setup di Midtrans Dashboard:');
        $this->newLine();
        $this->line('1. Login ke Midtrans Dashboard:');
        $this->line('   https://dashboard.midtrans.com');
        $this->newLine();
        $this->line('2. Masuk ke Settings > Configuration');
        $this->newLine();
        $this->line('3. Set Payment Notification URL ke:');
        $this->line('   ' . $webhookUrl);
        $this->newLine();
        $this->line('4. Pastikan HTTP Notification diaktifkan');
        $this->newLine();
        $this->line('5. Simpan konfigurasi');
        $this->newLine();

        // Warning for production
        if ($isProduction) {
            $this->warn('⚠️  PERINGATAN: Anda menggunakan mode PRODUCTION');
            $this->line('   - Pastikan URL webhook menggunakan HTTPS');
            $this->line('   - Pastikan signature verification diaktifkan');
            $this->line('   - Pastikan webhook URL dapat diakses publik');
        } else {
            $this->info('ℹ️  Mode: SANDBOX (Development/Testing)');
            $this->line('   - Untuk testing lokal, gunakan ngrok atau tool serupa');
            $this->line('   - Signature verification dapat dinonaktifkan untuk testing');
        }

        $this->newLine();

        // Testing instructions
        $this->info('🧪 Cara Testing Webhook:');
        $this->newLine();
        $this->line('1. Test dengan curl:');
        $this->line('   curl -X POST ' . $webhookUrl . ' \\');
        $this->line('     -H "Content-Type: application/json" \\');
        $this->line('     -d \'{"order_id":"test123","transaction_status":"settlement","status_code":"200"}\'');
        $this->newLine();
        $this->line('2. Cek webhook logs:');
        $this->line('   php artisan tinker');
        $this->line('   >>> \\App\\Models\\Domain\\Billing\\MidtransWebhookLog::latest()->take(5)->get()');
        $this->newLine();
        $this->line('3. Cek Laravel logs:');
        $this->line('   tail -f storage/logs/laravel.log | grep "Midtrans webhook"');

        $this->newLine();
        $this->info('✅ Pengecekan selesai!');

        return Command::SUCCESS;
    }

    /**
     * Get webhook URL
     */
    private function getWebhookUrl(): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $webhookPath = '/api/payments/midtrans/webhook';

        return $appUrl . $webhookPath;
    }
}
