<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $dosenRole = Role::where('slug', 'dosen')->firstOrFail();
        $mahasiswaRole = Role::where('slug', 'mahasiswa')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@lms.test'],
            [
                'name' => 'Administrator LMS',
                'role_id' => $adminRole->id,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dosen@lms.test'],
            [
                'name' => 'Dosen LMS',
                'role_id' => $dosenRole->id,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mahasiswa@lms.test'],
            [
                'name' => 'Mahasiswa LMS',
                'role_id' => $mahasiswaRole->id,
                'password' => 'password',
                'is_active' => true,
            ]
        );
    }
}
