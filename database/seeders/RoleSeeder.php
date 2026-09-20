<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Mengelola seluruh sistem LMS.',
            ],
            [
                'name' => 'Dosen',
                'slug' => 'dosen',
                'description' => 'Mengelola course, materi, tugas, quiz, dan nilai.',
            ],
            [
                'name' => 'Mahasiswa',
                'slug' => 'mahasiswa',
                'description' => 'Mengikuti course, mengakses materi, mengerjakan tugas dan quiz.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
