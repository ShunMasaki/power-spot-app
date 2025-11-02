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
                'key' => 'health',
                'label' => '健康運',
                'icon' => '/icons/benefits/health.png',
                'sort_order' => 2,
            ],
            [
                'key' => 'money',
                'label' => '金運',
                'icon' => '/icons/benefits/money.png',
                'sort_order' => 3,
            ],
            [
                'key' => 'family',
                'label' => '家庭運',
                'icon' => '/icons/benefits/family.png',
                'sort_order' => 4,
            ],
            [
                'key' => 'study',
                'label' => '学業運',
                'icon' => '/icons/benefits/study.png',
                'sort_order' => 5,
            ],
            [
                'key' => 'job',
                'label' => '仕事運',
                'icon' => '/icons/benefits/job.png',
                'sort_order' => 6,
            ],
            [
                'key' => 'protection',
                'label' => '厄除け',
                'icon' => '/icons/benefits/protection.png',
                'sort_order' => 7,
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
