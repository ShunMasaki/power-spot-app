<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Spot;

class SpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Spot::create([
            'name' => '明治神宮',
            'description' => '東京・原宿にある有名な神社。',
            'address' => '東京都渋谷区代々木神園町1-1',
            'latitude' => 35.6764,
            'longitude' => 139.6993,
            'image_url' => null,
        ]);

        Spot::create([
            'name' => '東京大神宮',
            'description' => '縁結びのご利益で人気の神社。',
            'address' => '東京都千代田区富士見2-4-1',
            'latitude' => 35.7003,
            'longitude' => 139.7461,
            'image_url' => null,
        ]);
    }
}
