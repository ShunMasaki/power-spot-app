<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Spot;
use App\Models\BenefitType;
use App\Models\SpotBenefit;

class SpotBenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 最初の5件のスポットに、ご利益を3つずつランダムに付ける
        $spots = Spot::limit(5)->get();
        $benefitTypes = BenefitType::all();

        foreach ($spots as $spot) {
            // 3つのランダムなご利益タイプを選ぶ
            $randomTypes = $benefitTypes->random(min(3, $benefitTypes->count()));

            foreach ($randomTypes as $type) {
                SpotBenefit::create([
                    'spot_id' => $spot->id,
                    'benefit_type_id' => $type->id,
                    'rating' => rand(3, 5), // 3〜5で適当にレート
                ]);
            }
        }
    }
}
