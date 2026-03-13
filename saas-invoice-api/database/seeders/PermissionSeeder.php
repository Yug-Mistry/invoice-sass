<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Clients
            ['name' => 'View Clients',   'slug' => 'clients.view',   'description' => 'View client list and details'],
            ['name' => 'Create Clients', 'slug' => 'clients.create', 'description' => 'Create new clients'],
            ['name' => 'Update Clients', 'slug' => 'clients.update', 'description' => 'Edit existing clients'],
            ['name' => 'Delete Clients', 'slug' => 'clients.delete', 'description' => 'Delete clients'],

            // Products
            ['name' => 'View Products',   'slug' => 'products.view',   'description' => 'View product list and details'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Create new products'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'description' => 'Edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'description' => 'Delete products'],

            // Invoices
            ['name' => 'View Invoices',   'slug' => 'invoices.view',   'description' => 'View invoice list and details'],
            ['name' => 'Create Invoices', 'slug' => 'invoices.create', 'description' => 'Create new invoices'],
            ['name' => 'Update Invoices', 'slug' => 'invoices.update', 'description' => 'Edit existing invoices'],
            ['name' => 'Delete Invoices', 'slug' => 'invoices.delete', 'description' => 'Delete invoices'],

            // Challans
            ['name' => 'View Challans',   'slug' => 'challans.view',   'description' => 'View challan list and details'],
            ['name' => 'Create Challans', 'slug' => 'challans.create', 'description' => 'Create new challans'],
            ['name' => 'Update Challans', 'slug' => 'challans.update', 'description' => 'Edit existing challans'],
            ['name' => 'Delete Challans', 'slug' => 'challans.delete', 'description' => 'Delete challans'],

            // Reports
            ['name' => 'View Reports',   'slug' => 'reports.view',   'description' => 'View reports and analytics'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Export reports to PDF/CSV'],

            // Admin
            ['name' => 'Manage Users',    'slug' => 'users.manage',    'description' => 'Create, edit and manage tenant users'],
            ['name' => 'Manage Groups',   'slug' => 'groups.manage',   'description' => 'Create and manage user groups'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Manage tenant settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
