<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan seluruh izin (permissions) sistem
        $permissions = [
            // Master Data (Hanya Admin)
            'manage-users',
            'manage-time-slots',
            'manage-products',
            'manage-trainers',
            'manage-payment-methods',
            'manage-greedy',

            // Operasional & Kasir
            'view-dashboard',
            'manage-members',
            'manage-memberships',
            'manage-membership-transactions',
            'manage-trainer-bookings',
            'manage-reservations',
            'manage-schedules',
            'manage-attendances',
            'view-transactions',

            // Trainer
            'manage-own-trainer-bookings',

            // Member
            'order-memberships',
            'make-reservations',
            'view-own-schedules',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat / Ambil peran (Roles)
        $adminRole = Role::firstOrCreate(['name' => 'Admin/Manager', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
        $trainerRole = Role::firstOrCreate(['name' => 'Trainer', 'guard_name' => 'web']);
        $memberRole = Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);

        // 3. Tetapkan izin ke peran
        // Admin: Semua izin
        $adminRole->syncPermissions(Permission::all());

        // Kasir: Operasional kasir sesuai kebutuhan
        $kasirRole->syncPermissions([
            'view-dashboard',
            'manage-members',
            'manage-memberships',
            'manage-membership-transactions',
            'manage-trainer-bookings',
            'manage-reservations',
            'manage-schedules',
            'manage-attendances',
            'view-transactions',
        ]);

        // Trainer: Kelola sesi miliknya sendiri
        $trainerRole->syncPermissions([
            'manage-own-trainer-bookings',
        ]);

        // Member: Pemesanan mandiri & reservasi
        $memberRole->syncPermissions([
            'order-memberships',
            'make-reservations',
            'view-own-schedules',
        ]);
    }
}
