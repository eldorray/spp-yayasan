<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Fahmie',
            'email' => 'fahmie@gmail.com',
        ]);

        // Create tata usaha users
        $tu1 = User::factory()->create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@yayasan.sch.id',
        ]);

        $tu2 = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@yayasan.sch.id',
        ]);

        // Run role permission seeder
        $this->call(RolePermissionSeeder::class);

        // Assign roles
        $admin->assignRole('super-admin');
        $tu1->assignRole('admin');
        $tu2->assignRole('admin');

        // Create navigation menus
        $menus = [
            ['title' => 'Instansi', 'url' => '/institutions', 'icon' => 'Building2', 'order' => 1],
            ['title' => 'Tahun Ajaran', 'url' => '/academic-years', 'icon' => 'Calendar', 'order' => 2],
            ['title' => 'Kelas', 'url' => '/classrooms', 'icon' => 'School', 'order' => 3],
            ['title' => 'Siswa', 'url' => '/students', 'icon' => 'Users', 'order' => 4],
            ['title' => 'Tagihan Bulanan', 'url' => '/monthly-bills', 'icon' => 'Receipt', 'order' => 5],
            ['title' => 'Kegiatan', 'url' => '/activities', 'icon' => 'CalendarDays', 'order' => 6],
            ['title' => 'Pembayaran', 'url' => '/payments', 'icon' => 'CreditCard', 'order' => 7],
            ['title' => 'Laporan', 'url' => '/reports', 'icon' => 'FileBarChart', 'order' => 8],
        ];

        foreach ($menus as $menu) {
            Menu::create(array_merge($menu, ['is_active' => true]));
        }

        // // Run SPP seeder
        // $this->call(SppSeeder::class);
    }
}
