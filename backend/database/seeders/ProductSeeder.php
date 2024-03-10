<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Product;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        User::truncate();
//        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $faker = Factory::create();
        $name = $faker->sentence($nbWords = 3, $variableNbWords = true);
        $product = new Product();
        $product->name = $name;
        $product->slug = Str::slug($name);
        $product->description = $faker->text($maxNbChars = 100);
        $product->save();

    }
}
