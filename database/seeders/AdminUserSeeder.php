<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('ADMIN_NAME', 'Admin Medlink');
        $email = env('ADMIN_EMAIL', 'admin@medlink.local');
        $password = env('ADMIN_PASSWORD', '123admin');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );
    }
}
