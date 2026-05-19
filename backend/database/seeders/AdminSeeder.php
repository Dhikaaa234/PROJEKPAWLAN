<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@filkom.edu'],
            [
                'nama' => 'Admin Filkom',
                'name' => 'Admin Filkom',
                'nim' => null,
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]
        );
    }
}
