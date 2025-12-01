<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user = Role::firstOrCreate(['name' => 'user']);
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $support = Role::firstOrCreate(['name' => 'support']);
        $billing = Role::firstOrCreate(['name' => 'billing']);

        // Daftar permission berdasarkan menu structure
        $permissions = [
            'Dashboard' => [
                'dashboard-view',
            ],
            'Customer Area' => [
                'customer-area-view',
                'customer-orders-view',
                'customer-invoices-view',
                'customer-subscriptions-view',
                'customer-tickets-view',
                'customer-domains-view',
                'customer-domains-create',
                'customer-domains-edit',
                'customer-domains-delete',
                'customer-ssl-view',
                'customer-ssl-create',
                'customer-ssl-edit',
                'customer-ssl-delete',
            ],
            'Admin Catalog' => [
                'admin-catalog-view',
                'admin-products-view',
                'admin-products-create',
                'admin-products-edit',
                'admin-products-delete',
                'admin-plans-view',
                'admin-plans-create',
                'admin-plans-edit',
                'admin-plans-delete',
            ],
            'Admin Orders' => [
                'admin-orders-view',
                'admin-orders-update',
                'admin-invoices-view',
                'admin-invoices-update',
                'admin-invoices-download',
                'admin-subscriptions-view',
                'admin-subscriptions-update',
                'admin-subscriptions-cancel',
            ],
            'Admin Domains' => [
                'admin-domains-view',
                'admin-domains-create',
                'admin-domains-edit',
                'admin-domains-delete',
                'admin-domains-register',
                'admin-domains-transfer',
                'admin-domains-renew',
                'admin-domain-prices-view',
                'admin-ssl-view',
                'admin-ssl-create',
                'admin-ssl-edit',
                'admin-ssl-delete',
                'admin-ssl-manage',
            ],
            'Admin Provisioning' => [
                'admin-provisioning-view',
                'admin-servers-view',
                'admin-servers-create',
                'admin-servers-edit',
                'admin-servers-delete',
                'admin-panel-accounts-view',
                'admin-panel-accounts-sync',
                'admin-provision-tasks-view',
                'admin-provision-tasks-retry',
            ],
            'Admin Support' => [
                'admin-support-view',
                'admin-tickets-view',
                'admin-tickets-edit',
                'admin-tickets-assign',
                'admin-tickets-close',
            ],
            'Access' => [
                'access-view',
                'permission-view',
                'permission-create',
                'permission-edit',
                'permission-delete',
                'users-view',
                'users-create',
                'users-edit',
                'users-delete',
                'roles-view',
                'roles-create',
                'roles-edit',
                'roles-delete',
            ],
            'Settings' => [
                'settings-view',
                'menu-view',
                'menu-create',
                'menu-edit',
                'menu-delete',
                'app-settings-view',
                'app-settings-update',
                'backup-view',
                'backup-run',
                'backup-download',
                'backup-delete',
            ],
            'Utilities' => [
                'utilities-view',
                'log-view',
                'filemanager-view',
                'filemanager-upload',
                'filemanager-delete',
            ],
        ];

        // Create permissions
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $name) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name],
                    ['group' => $group]
                );

                // Update group if permission exists but group is different
                if ($permission->group !== $group) {
                    $permission->update(['group' => $group]);
                }
            }
        }

        // Assign permissions to admin (all permissions)
        $admin->givePermissionTo(Permission::all());

        // Assign permissions to customer role
        $customer->givePermissionTo([
            'dashboard-view',
            'customer-area-view',
            'customer-orders-view',
            'customer-invoices-view',
            'customer-subscriptions-view',
            'customer-tickets-view',
            'customer-domains-view',
            'customer-domains-create',
            'customer-domains-edit',
            'customer-ssl-view',
            'customer-ssl-create',
        ]);

        // Assign permissions to support role
        $support->givePermissionTo([
            'dashboard-view',
            'admin-support-view',
            'admin-tickets-view',
            'admin-tickets-edit',
            'admin-tickets-assign',
            'admin-tickets-close',
            'customer-area-view', // Can view customer area to understand issues
        ]);

        // Assign permissions to billing role
        $billing->givePermissionTo([
            'dashboard-view',
            'admin-orders-view',
            'admin-invoices-view',
            'admin-invoices-update',
            'admin-invoices-download',
            'admin-subscriptions-view',
            'customer-area-view',
            'customer-orders-view',
            'customer-invoices-view',
            'customer-subscriptions-view',
        ]);

        // Assign basic permissions to user role (default)
        $user->givePermissionTo([
            'dashboard-view',
            'customer-area-view',
            'customer-orders-view',
            'customer-invoices-view',
            'customer-subscriptions-view',
            'customer-tickets-view',
            'customer-domains-view',
            'customer-domains-create',
            'customer-domains-edit',
        ]);
    }
}
