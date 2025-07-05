<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BenefitType;

class BenefitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'key' => 'love',
                'label' => '恋愛運',
                'icon' => '/icons/benefits/love.png',
                'sort_order' => 1,
            ],
            [
                'key' => 'money',
                'label' => '金運',
                'icon' => '/icons/benefits/money.png',
                'sort_order' => 2,
            ],
            [
                'key' => 'job',
                'label' => '仕事運',
                'icon' => '/icons/benefits/job.png',
                'sort_order' => 3,
            ],
            [
                'key' => 'health',
                'label' => '健康運',
                'icon' => '/icons/benefits/health.png',
                'sort_order' => 4,
            ],
            [
                'key' => 'wish',
                'label' => '願望成就',
                'icon' => '/icons/benefits/wish.png',
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            BenefitType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
