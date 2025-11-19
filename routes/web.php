<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserFileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SettingAppController;
use App\Http\Controllers\MediaFolderController;
use App\Http\Controllers\Domain\Catalog\CatalogController;
use App\Http\Controllers\Domain\Catalog\ProductController;
use App\Http\Controllers\Domain\Catalog\PlanController;
use App\Http\Controllers\Domain\Order\OrderController;
use App\Http\Controllers\Domain\Billing\InvoiceController;
use App\Http\Controllers\Domain\Subscription\SubscriptionController;
use App\Http\Controllers\Domain\Support\TicketController;
use App\Http\Controllers\Domain\Provisioning\ServerController;
use App\Http\Controllers\Domain\Provisioning\PanelAccountController;
use App\Http\Controllers\Domain\Provisioning\ProvisionTaskController;
use App\Http\Controllers\Domain\DomainController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Public catalog routes
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware(['auth', 'menu.permission'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Customer routes
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        
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
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('plans', PlanController::class);
        Route::resource('servers', ServerController::class);
        Route::get('/panel-accounts', [PanelAccountController::class, 'index'])->name('panel-accounts.index');
        Route::get('/panel-accounts/{id}', [PanelAccountController::class, 'show'])->name('panel-accounts.show');
        Route::get('/provision-tasks', [ProvisionTaskController::class, 'index'])->name('provision-tasks.index');
        Route::get('/provision-tasks/{id}', [ProvisionTaskController::class, 'show'])->name('provision-tasks.show');
        
        // Admin orders, invoices, subscriptions views
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        
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
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/run', [BackupController::class, 'run'])->name('backup.run');
    Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/delete/{file}', [BackupController::class, 'delete'])->name('backup.delete');
    Route::get('/files', [UserFileController::class, 'index'])->name('files.index');
    Route::post('/files', [UserFileController::class, 'store'])->name('files.store');
    Route::delete('/files/{id}', [UserFileController::class, 'destroy'])->name('files.destroy');
    Route::resource('media', MediaFolderController::class);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
