<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // MENU: Dashboard
        $dashboard = Menu::updateOrCreate(
            ['route' => '/dashboard'],
            [
                'title' => 'Dashboard',
                'icon' => 'Home',
                'order' => 1,
                'permission_name' => 'dashboard-view',
                'parent_id' => null,
            ]
        );

        // GROUP: Catalog (Public - no auth required)
        $catalog = Menu::updateOrCreate(
            ['route' => '/catalog'],
            [
                'title' => 'Catalog',
                'icon' => 'ShoppingCart',
                'order' => 2,
                'permission_name' => null, // Public access
                'parent_id' => null,
            ]
        );

        // GROUP: Customer Area
        $customerArea = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Customer Area'],
            [
                'icon' => 'User',
                'order' => 3,
                'permission_name' => 'customer-area-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/orders', 'parent_id' => $customerArea->id],
            [
                'title' => 'My Orders',
                'icon' => 'Package',
                'order' => 1,
                'permission_name' => 'customer-orders-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/invoices', 'parent_id' => $customerArea->id],
            [
                'title' => 'My Invoices',
                'icon' => 'FileText',
                'order' => 2,
                'permission_name' => 'customer-invoices-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/subscriptions', 'parent_id' => $customerArea->id],
            [
                'title' => 'My Subscriptions',
                'icon' => 'CreditCard',
                'order' => 3,
                'permission_name' => 'customer-subscriptions-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/tickets', 'parent_id' => $customerArea->id],
            [
                'title' => 'Support Tickets',
                'icon' => 'MessageSquare',
                'order' => 4,
                'permission_name' => 'customer-tickets-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/domains', 'parent_id' => $customerArea->id],
            [
                'title' => 'My Domains',
                'icon' => 'Globe',
                'order' => 5,
                'permission_name' => 'customer-domains-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/ssl', 'parent_id' => $customerArea->id],
            [
                'title' => 'SSL Certificates',
                'icon' => 'Shield',
                'order' => 6,
                'permission_name' => 'customer-ssl-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/customer/domain-prices', 'parent_id' => $customerArea->id],
            [
                'title' => 'Domain Prices',
                'icon' => 'Tags',
                'order' => 7,
                'permission_name' => 'customer-domain-prices-view',
            ]
        );

        // GROUP: Admin - Catalog Management
        $adminCatalog = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Catalog Management'],
            [
                'icon' => 'Store',
                'order' => 10,
                'permission_name' => 'admin-catalog-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/products', 'parent_id' => $adminCatalog->id],
            [
                'title' => 'Products',
                'icon' => 'Box',
                'order' => 1,
                'permission_name' => 'admin-products-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/plans', 'parent_id' => $adminCatalog->id],
            [
                'title' => 'Plans',
                'icon' => 'List',
                'order' => 2,
                'permission_name' => 'admin-plans-view',
            ]
        );

        // GROUP: Admin - Order Management
        $adminOrders = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Order Management'],
            [
                'icon' => 'ShoppingBag',
                'order' => 11,
                'permission_name' => 'admin-orders-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/orders', 'parent_id' => $adminOrders->id],
            [
                'title' => 'Orders',
                'icon' => 'Package',
                'order' => 1,
                'permission_name' => 'admin-orders-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/invoices', 'parent_id' => $adminOrders->id],
            [
                'title' => 'Invoices',
                'icon' => 'FileText',
                'order' => 2,
                'permission_name' => 'admin-invoices-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/subscriptions', 'parent_id' => $adminOrders->id],
            [
                'title' => 'Subscriptions',
                'icon' => 'CreditCard',
                'order' => 3,
                'permission_name' => 'admin-subscriptions-view',
            ]
        );

        // GROUP: Admin - Domain Management
        $adminDomains = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Domain Management'],
            [
                'icon' => 'Globe',
                'order' => 14,
                'permission_name' => 'admin-domains-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/domains', 'parent_id' => $adminDomains->id],
            [
                'title' => 'Domains',
                'icon' => 'Globe',
                'order' => 1,
                'permission_name' => 'admin-domains-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/ssl', 'parent_id' => $adminDomains->id],
            [
                'title' => 'SSL Certificates',
                'icon' => 'Shield',
                'order' => 2,
                'permission_name' => 'admin-ssl-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/domain-prices', 'parent_id' => $adminDomains->id],
            [
                'title' => 'Domain Prices',
                'icon' => 'Tags',
                'order' => 3,
                'permission_name' => 'admin-domain-prices-view',
            ]
        );

        // GROUP: Admin - Provisioning
        $adminProvisioning = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Provisioning'],
            [
                'icon' => 'Server',
                'order' => 12,
                'permission_name' => 'admin-provisioning-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/servers', 'parent_id' => $adminProvisioning->id],
            [
                'title' => 'Servers',
                'icon' => 'Server',
                'order' => 1,
                'permission_name' => 'admin-servers-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/panel-accounts', 'parent_id' => $adminProvisioning->id],
            [
                'title' => 'Panel Accounts',
                'icon' => 'UserCircle',
                'order' => 2,
                'permission_name' => 'admin-panel-accounts-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/provision-tasks', 'parent_id' => $adminProvisioning->id],
            [
                'title' => 'Provision Tasks',
                'icon' => 'Settings',
                'order' => 3,
                'permission_name' => 'admin-provision-tasks-view',
            ]
        );

        // GROUP: Admin - Support
        $adminSupport = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Support'],
            [
                'icon' => 'Headphones',
                'order' => 13,
                'permission_name' => 'admin-support-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/admin/tickets', 'parent_id' => $adminSupport->id],
            [
                'title' => 'Tickets',
                'icon' => 'MessageSquare',
                'order' => 1,
                'permission_name' => 'admin-tickets-view',
            ]
        );

        // GROUP: Access
        $access = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Access'],
            [
                'icon' => 'Contact',
                'order' => 20,
                'permission_name' => 'access-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/permissions', 'parent_id' => $access->id],
            [
                'title' => 'Permissions',
                'icon' => 'AlertOctagon',
                'order' => 2,
                'permission_name' => 'permission-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/users', 'parent_id' => $access->id],
            [
                'title' => 'Users',
                'icon' => 'Users',
                'order' => 3,
                'permission_name' => 'users-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/roles', 'parent_id' => $access->id],
            [
                'title' => 'Roles',
                'icon' => 'AlertTriangle',
                'order' => 4,
                'permission_name' => 'roles-view',
            ]
        );

        // GROUP: Settings
        $settings = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Settings'],
            [
                'icon' => 'Settings',
                'order' => 21,
                'permission_name' => 'settings-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/menus', 'parent_id' => $settings->id],
            [
                'title' => 'Menu Manager',
                'icon' => 'Menu',
                'order' => 1,
                'permission_name' => 'menu-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/settingsapp', 'parent_id' => $settings->id],
            [
                'title' => 'App Settings',
                'icon' => 'AtSign',
                'order' => 2,
                'permission_name' => 'app-settings-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/backup', 'parent_id' => $settings->id],
            [
                'title' => 'Backup',
                'icon' => 'Inbox',
                'order' => 3,
                'permission_name' => 'backup-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/settings/margin', 'parent_id' => $settings->id],
            [
                'title' => 'Margin Keuntungan',
                'icon' => 'TrendingUp',
                'order' => 4,
                'permission_name' => 'margin-settings-view',
            ]
        );

        // GROUP: Utilities
        $utilities = Menu::updateOrCreate(
            ['route' => '#', 'title' => 'Utilities'],
            [
                'icon' => 'CreditCard',
                'order' => 22,
                'permission_name' => 'utilities-view',
                'parent_id' => null,
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/audit-logs', 'parent_id' => $utilities->id],
            [
                'title' => 'Audit Logs',
                'icon' => 'Activity',
                'order' => 2,
                'permission_name' => 'log-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/utilities/log-viewer', 'parent_id' => $utilities->id],
            [
                'title' => 'Log Viewer',
                'icon' => 'FileText',
                'order' => 3,
                'permission_name' => 'log-viewer-view',
            ]
        );

        Menu::updateOrCreate(
            ['route' => '/files', 'parent_id' => $utilities->id],
            [
                'title' => 'File Manager',
                'icon' => 'Folder',
                'order' => 4,
                'permission_name' => 'filemanager-view',
            ]
        );

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
            'customer-ssl-view',
            'customer-domain-prices-view',
        ]);
    }
}
