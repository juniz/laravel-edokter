<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // MENU: Dashboard
        Menu::create([
            'title' => 'Dashboard',
            'icon' => 'Home',
            'route' => '/dashboard',
            'order' => 1,
            'permission_name' => 'dashboard-view',
        ]);

        // GROUP: Catalog (Public - no auth required)
        $catalog = Menu::create([
            'title' => 'Catalog',
            'icon' => 'ShoppingCart',
            'route' => '/catalog',
            'order' => 2,
            'permission_name' => null, // Public access
        ]);

        // GROUP: Customer Area
        $customerArea = Menu::create([
            'title' => 'Customer Area',
            'icon' => 'User',
            'route' => '#',
            'order' => 3,
            'permission_name' => 'customer-area-view',
        ]);

        Menu::create([
            'title' => 'My Orders',
            'icon' => 'Package',
            'route' => '/customer/orders',
            'order' => 1,
            'permission_name' => 'customer-orders-view',
            'parent_id' => $customerArea->id,
        ]);

        Menu::create([
            'title' => 'My Invoices',
            'icon' => 'FileText',
            'route' => '/customer/invoices',
            'order' => 2,
            'permission_name' => 'customer-invoices-view',
            'parent_id' => $customerArea->id,
        ]);

        Menu::create([
            'title' => 'My Subscriptions',
            'icon' => 'CreditCard',
            'route' => '/customer/subscriptions',
            'order' => 3,
            'permission_name' => 'customer-subscriptions-view',
            'parent_id' => $customerArea->id,
        ]);

        Menu::create([
            'title' => 'Support Tickets',
            'icon' => 'MessageSquare',
            'route' => '/customer/tickets',
            'order' => 4,
            'permission_name' => 'customer-tickets-view',
            'parent_id' => $customerArea->id,
        ]);

        Menu::create([
            'title' => 'My Domains',
            'icon' => 'Globe',
            'route' => '/customer/domains',
            'order' => 5,
            'permission_name' => 'customer-domains-view',
            'parent_id' => $customerArea->id,
        ]);

        // GROUP: Admin - Catalog Management
        $adminCatalog = Menu::create([
            'title' => 'Catalog Management',
            'icon' => 'Store',
            'route' => '#',
            'order' => 10,
            'permission_name' => 'admin-catalog-view',
        ]);

        Menu::create([
            'title' => 'Products',
            'icon' => 'Box',
            'route' => '/admin/products',
            'order' => 1,
            'permission_name' => 'admin-products-view',
            'parent_id' => $adminCatalog->id,
        ]);

        Menu::create([
            'title' => 'Plans',
            'icon' => 'List',
            'route' => '/admin/plans',
            'order' => 2,
            'permission_name' => 'admin-plans-view',
            'parent_id' => $adminCatalog->id,
        ]);

        // GROUP: Admin - Order Management
        $adminOrders = Menu::create([
            'title' => 'Order Management',
            'icon' => 'ShoppingBag',
            'route' => '#',
            'order' => 11,
            'permission_name' => 'admin-orders-view',
        ]);

        Menu::create([
            'title' => 'Orders',
            'icon' => 'Package',
            'route' => '/admin/orders',
            'order' => 1,
            'permission_name' => 'admin-orders-view',
            'parent_id' => $adminOrders->id,
        ]);

        Menu::create([
            'title' => 'Invoices',
            'icon' => 'FileText',
            'route' => '/admin/invoices',
            'order' => 2,
            'permission_name' => 'admin-invoices-view',
            'parent_id' => $adminOrders->id,
        ]);

        Menu::create([
            'title' => 'Subscriptions',
            'icon' => 'CreditCard',
            'route' => '/admin/subscriptions',
            'order' => 3,
            'permission_name' => 'admin-subscriptions-view',
            'parent_id' => $adminOrders->id,
        ]);

        // GROUP: Admin - Domain Management
        $adminDomains = Menu::create([
            'title' => 'Domain Management',
            'icon' => 'Globe',
            'route' => '#',
            'order' => 14,
            'permission_name' => 'admin-domains-view',
        ]);

        Menu::create([
            'title' => 'Domains',
            'icon' => 'Globe',
            'route' => '/admin/domains',
            'order' => 1,
            'permission_name' => 'admin-domains-view',
            'parent_id' => $adminDomains->id,
        ]);

        // GROUP: Admin - Provisioning
        $adminProvisioning = Menu::create([
            'title' => 'Provisioning',
            'icon' => 'Server',
            'route' => '#',
            'order' => 12,
            'permission_name' => 'admin-provisioning-view',
        ]);

        Menu::create([
            'title' => 'Servers',
            'icon' => 'Server',
            'route' => '/admin/servers',
            'order' => 1,
            'permission_name' => 'admin-servers-view',
            'parent_id' => $adminProvisioning->id,
        ]);

        Menu::create([
            'title' => 'Panel Accounts',
            'icon' => 'UserCircle',
            'route' => '/admin/panel-accounts',
            'order' => 2,
            'permission_name' => 'admin-panel-accounts-view',
            'parent_id' => $adminProvisioning->id,
        ]);

        Menu::create([
            'title' => 'Provision Tasks',
            'icon' => 'Settings',
            'route' => '/admin/provision-tasks',
            'order' => 3,
            'permission_name' => 'admin-provision-tasks-view',
            'parent_id' => $adminProvisioning->id,
        ]);

        // GROUP: Admin - Support
        $adminSupport = Menu::create([
            'title' => 'Support',
            'icon' => 'Headphones',
            'route' => '#',
            'order' => 13,
            'permission_name' => 'admin-support-view',
        ]);

        Menu::create([
            'title' => 'Tickets',
            'icon' => 'MessageSquare',
            'route' => '/admin/tickets',
            'order' => 1,
            'permission_name' => 'admin-tickets-view',
            'parent_id' => $adminSupport->id,
        ]);

        // GROUP: Access
        $access = Menu::create([
            'title' => 'Access',
            'icon' => 'Contact',
            'route' => '#',
            'order' => 20,
            'permission_name' => 'access-view',
        ]);

        Menu::create([
            'title' => 'Permissions',
            'icon' => 'AlertOctagon',
            'route' => '/permissions',
            'order' => 2,
            'permission_name' => 'permission-view',
            'parent_id' => $access->id,
        ]);

        Menu::create([
            'title' => 'Users',
            'icon' => 'Users',
            'route' => '/users',
            'order' => 3,
            'permission_name' => 'users-view',
            'parent_id' => $access->id,
        ]);

        Menu::create([
            'title' => 'Roles',
            'icon' => 'AlertTriangle',
            'route' => '/roles',
            'order' => 4,
            'permission_name' => 'roles-view',
            'parent_id' => $access->id,
        ]);

        // GROUP: Settings
        $settings = Menu::create([
            'title' => 'Settings',
            'icon' => 'Settings',
            'route' => '#',
            'order' => 21,
            'permission_name' => 'settings-view',
        ]);

        Menu::create([
            'title' => 'Menu Manager',
            'icon' => 'Menu',
            'route' => '/menus',
            'order' => 1,
            'permission_name' => 'menu-view',
            'parent_id' => $settings->id,
        ]);

        Menu::create([
            'title' => 'App Settings',
            'icon' => 'AtSign',
            'route' => '/settingsapp',
            'order' => 2,
            'permission_name' => 'app-settings-view',
            'parent_id' => $settings->id,
        ]);

        Menu::create([
            'title' => 'Backup',
            'icon' => 'Inbox',
            'route' => '/backup',
            'order' => 3,
            'permission_name' => 'backup-view',
            'parent_id' => $settings->id,
        ]);

        // GROUP: Utilities
        $utilities = Menu::create([
            'title' => 'Utilities',
            'icon' => 'CreditCard',
            'route' => '#',
            'order' => 22,
            'permission_name' => 'utilities-view',
        ]);

        Menu::create([
            'title' => 'Audit Logs',
            'icon' => 'Activity',
            'route' => '/audit-logs',
            'order' => 2,
            'permission_name' => 'log-view',
            'parent_id' => $utilities->id,
        ]);

        Menu::create([
            'title' => 'File Manager',
            'icon' => 'Folder',
            'route' => '/files',
            'order' => 3,
            'permission_name' => 'filemanager-view',
            'parent_id' => $utilities->id,
        ]);

        // Create permissions from menu
        $permissions = Menu::pluck('permission_name')->unique()->filter();

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        // Assign basic permissions to user role
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'dashboard-view',
            'customer-area-view',
            'customer-orders-view',
            'customer-invoices-view',
            'customer-subscriptions-view',
            'customer-tickets-view',
        ]);
    }
}
