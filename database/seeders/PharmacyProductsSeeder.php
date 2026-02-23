<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PharmacyProductsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first();
        $owner = User::updateOrCreate(
            ['email' => 'farmacia@medlink.local'],
            [
                'name' => 'Farmacia Central',
                'password' => Hash::make('farmacia123'),
                'is_admin' => false,
            ]
        );

        $pharmacy = Pharmacy::updateOrCreate(
            ['user_id' => $owner->id],
            [
                'name' => 'Farmacia Central',
                'responsible_name' => 'Ana Silva',
                'phone' => '(+244) 900 000 000',
                'email' => 'contato@farmacia-central.local',
                'address' => 'Rua Principal, Luanda',
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin?->id ?? $owner->id,
            ]
        );

        $products = [
            [
                'name' => 'Paracetamol 500mg',
                'description' => 'Analgesico e antitermico para dores leves.',
                'price' => 1200,
                'stock' => 80,
                'category' => 'Analgesicos',
                'image_url' => 'images/products/paracetamol.svg',
            ],
            [
                'name' => 'Vitamina C 1g',
                'description' => 'Suplemento para reforcar a imunidade.',
                'price' => 2200,
                'stock' => 50,
                'category' => 'Vitaminas',
                'image_url' => 'images/products/vitamina-c.svg',
            ],
            [
                'name' => 'Pomada Antisseptica',
                'description' => 'Auxilia na cicatrizacao de pequenos ferimentos.',
                'price' => 1800,
                'stock' => 35,
                'category' => 'Cuidados pessoais',
                'image_url' => 'images/products/pomada-antisseptica.svg',
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => $data['name'],
                ],
                array_merge($data, [
                    'pharmacy_id' => $pharmacy->id,
                    'is_active' => true,
                ])
            );
        }

        $pendingOwner = User::updateOrCreate(
            ['email' => 'farmacia.pendente@medlink.local'],
            [
                'name' => 'Farmacia Espera',
                'password' => Hash::make('farmacia123'),
                'is_admin' => false,
            ]
        );

        Pharmacy::updateOrCreate(
            ['user_id' => $pendingOwner->id],
            [
                'name' => 'Farmacia Espera',
                'responsible_name' => 'Carlos Mota',
                'phone' => '(+244) 911 111 111',
                'email' => 'pendente@farmacia.local',
                'address' => 'Rua Espera, Luanda',
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]
        );
    }
}
