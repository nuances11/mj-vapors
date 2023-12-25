<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Brand',
                'values' => [
                    'Flava', 'Brand 2', 'Brand 3', 'Brand 4'
                ]
            ],
            [
                'name' => 'Type',
                'values' => [
                    'Disposable', 'E-Juice', 'Mod', 'Attomizer', 'Others'
                ]
            ],
            [
                'name' => 'Puffs',
                'values' => [
                    '10000', '8000', '2000'
                ]
            ],
            [
                'name' => 'Flavor',
                'values' => [
                    'Durian', 'Bangbangset', 'Ube', 'Cheesecake'
                ]
            ]

        ];

        foreach ($attributes as $attribute) {
            DB::transaction(function() use ($attribute) {
                $createdAttribute = Attribute::create([
                    'name' => $attribute['name'],
                    'slug' => Str::slug($attribute['name'])
                ]);
                foreach ($attribute['values'] as $value) {
                    $createdAttribute->attributeOptions()->create(['value' => $value]);
                }
            });
        }
    }
}
