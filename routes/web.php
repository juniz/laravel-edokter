<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\Domain\Billing\InvoiceController;
use App\Http\Controllers\Domain\Billing\PaymentController;
use App\Http\Controllers\Domain\Catalog\CatalogController;
use App\Http\Controllers\Domain\Catalog\PlanController;
use App\Http\Controllers\Domain\Catalog\ProductController;
use App\Http\Controllers\Domain\DomainController;
use App\Http\Controllers\Domain\DomainPriceController;
use App\Http\Controllers\Domain\Order\CartController;
use App\Http\Controllers\Domain\Order\OrderController;
use App\Http\Controllers\Domain\Provisioning\PanelAccountController;
use App\Http\Controllers\Domain\Provisioning\ProvisionTaskController;
use App\Http\Controllers\Domain\Provisioning\ServerController;
use App\Http\Controllers\Domain\Ssl\SslController as DomainSslController;
use App\Http\Controllers\Domain\Subscription\SubscriptionController;
use App\Http\Controllers\Domain\Support\TicketController;
use App\Http\Controllers\LogViewerController;
use App\Http\Controllers\MediaFolderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingAppController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Test email route (without queue)
Route::get('/send_email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'testSend'])
    ->name('test.send-email');

// Public catalog routes
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::post('/catalog/checkout', [CatalogController::class, 'checkout'])->name('catalog.checkout')->middleware('auth');

// Route download PDF invoice - tanpa middleware Inertia untuk direct file download
Route::get('/customer/invoices/{id}/download', [InvoiceController::class, 'download'])
    ->name('customer.invoices.download')
    ->middleware('auth')
    ->withoutMiddleware(\App\Http\Middleware\HandleInertiaRequests::class);

Route::middleware(['auth', 'menu.permission'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Customer routes
    Route::prefix('customer')->name('customer.')->group(function () {
        // Cart routes
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::put('/cart/items/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/items/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        // Route download dipindahkan ke luar middleware group
        Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');

        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');

        // Customer domains
        Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
        Route::get('/domains/create', [DomainController::class, 'create'])->name('domains.create');
        Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
        Route::get('/domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
        Route::post('/domains/check-availability', [DomainController::class, 'checkAvailability'])->name('domains.check-availability');
        Route::post('/domains/get-details', [DomainController::class, 'getDetails'])->name('domains.get-details');

        // Customer SSL
        Route::get('/ssl', [DomainSslController::class, 'index'])->name('ssl.index');

        // Customer Domain Prices
        Route::get('/domain-prices', [DomainPriceController::class, 'index'])->name('domain-prices.index');
        Route::get('/domain-prices/by-extension', [DomainPriceController::class, 'getByExtension'])->name('domain-prices.by-extension');
        Route::get('/domain-prices/{priceId}', [DomainPriceController::class, 'show'])->name('domain-prices.show');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('plans', PlanController::class);
        Route::post('servers/{id}/test-connection', [ServerController::class, 'testConnection'])->name('servers.test-connection');
        Route::resource('servers', ServerController::class);
        Route::get('/panel-accounts', [PanelAccountController::class, 'index'])->name('panel-accounts.index');
        Route::post('/panel-accounts', [PanelAccountController::class, 'create'])->name('panel-accounts.create');
        Route::post('/panel-accounts/virtual', [PanelAccountController::class, 'createVirtualAccount'])->name('panel-accounts.create-virtual');
        Route::get('/panel-accounts/{id}', [PanelAccountController::class, 'show'])->name('panel-accounts.show');
        Route::get('/provision-tasks', [ProvisionTaskController::class, 'index'])->name('provision-tasks.index');
        Route::get('/provision-tasks/{id}', [ProvisionTaskController::class, 'show'])->name('provision-tasks.show');

        // Admin orders, invoices, subscriptions views
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

        // Admin tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
        Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
        Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{id}/assign', [TicketController::class, 'assign'])->name('tickets.assign');

        // Domain management
        Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
        Route::get('/domains/create', [DomainController::class, 'create'])->name('domains.create');
        Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
        Route::get('/domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
        Route::post('/domains/check-availability', [DomainController::class, 'checkAvailability'])->name('domains.check-availability');
        Route::post('/domains/get-details', [DomainController::class, 'getDetails'])->name('domains.get-details');

        // Domain price management (RDASH domain prices)
        Route::get('/domain-prices', [DomainPriceController::class, 'index'])->name('domain-prices.index');
        Route::get('/domain-prices/by-extension', [DomainPriceController::class, 'getByExtension'])->name('domain-prices.by-extension');
        Route::get('/domain-prices/{priceId}', [DomainPriceController::class, 'show'])->name('domain-prices.show');

        // SSL management
        Route::get('/ssl', [DomainSslController::class, 'index'])->name('ssl.index');
    });

    // Existing admin routes
    Route::resource('roles', RoleController::class);
    Route::resource('menus', MenuController::class);
    Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    // RDASH Integration routes
    Route::post('/users/{user}/sync-rdash', [UserController::class, 'syncRdash'])->name('users.sync-rdash');
    Route::post('/users/bulk-sync-rdash', [UserController::class, 'bulkSyncRdash'])->name('users.bulk-sync-rdash');
    Route::get('/users/{user}/rdash-customer', [UserController::class, 'getRdashCustomer'])->name('users.rdash-customer');
    Route::put('/users/{user}/rdash-customer', [UserController::class, 'updateRdashCustomer'])->name('users.update-rdash-customer');
    Route::get('/settingsapp', [SettingAppController::class, 'edit'])->name('setting.edit');
    Route::post('/settingsapp', [SettingAppController::class, 'update'])->name('setting.update');
    Route::get('/settings/margin', [\App\Http\Controllers\Settings\MarginController::class, 'edit'])->name('margin.edit')->middleware('admin');
    Route::put('/settings/margin', [\App\Http\Controllers\Settings\MarginController::class, 'update'])->name('margin.update')->middleware('admin');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/utilities/log-viewer', [LogViewerController::class, 'index'])->name('log-viewer.index');
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/run', [BackupController::class, 'run'])->name('backup.run');
    Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/delete/{file}', [BackupController::class, 'delete'])->name('backup.delete');
    Route::get('/files', [UserFileController::class, 'index'])->name('files.index');
    Route::post('/files', [UserFileController::class, 'store'])->name('files.store');
    Route::delete('/files/{id}', [UserFileController::class, 'destroy'])->name('files.destroy');
    Route::resource('media', MediaFolderController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
