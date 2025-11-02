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
        // 明治神宮
        $meiji = Spot::updateOrCreate(
            ['name' => '明治神宮'],
            [
                'description' => '東京・原宿にある有名な神社。明治天皇と昭憲皇太后を祀る。広大な森に囲まれた都会のオアシス。',
                'address' => '東京都渋谷区代々木神園町1-1',
                'latitude' => 35.6764,
                'longitude' => 139.6993,
                'image_url' => null,
            ]
        );

        // 明治神宮のご利益を追加
        $meiji->spotBenefits()->delete(); // 既存のご利益を削除
        $meiji->spotBenefits()->createMany([
            ['benefit_type_id' => 1, 'rating' => 5], // 恋愛運
            ['benefit_type_id' => 2, 'rating' => 5], // 健康運
            ['benefit_type_id' => 3, 'rating' => 4], // 金運
            ['benefit_type_id' => 5, 'rating' => 4], // 学業運
            ['benefit_type_id' => 6, 'rating' => 5], // 仕事運
        ]);

        // 東京大神宮
        $tokyo = Spot::updateOrCreate(
            ['name' => '東京大神宮'],
            [
                'description' => '縁結びのご利益で人気の神社。「東京のお伊勢さま」として親しまれ、神前結婚式創始の神社として知られる。',
                'address' => '東京都千代田区富士見2-4-1',
                'latitude' => 35.7003,
                'longitude' => 139.7461,
                'image_url' => null,
            ]
        );

        // 東京大神宮のご利益を追加
        $tokyo->spotBenefits()->delete(); // 既存のご利益を削除
        $tokyo->spotBenefits()->createMany([
            ['benefit_type_id' => 1, 'rating' => 5], // 恋愛運
            ['benefit_type_id' => 4, 'rating' => 5], // 家庭運
            ['benefit_type_id' => 6, 'rating' => 4], // 仕事運
        ]);

        // 浅草寺
        $sensoji = Spot::updateOrCreate(
            ['name' => '浅草寺'],
            [
                'description' => '東京最古の寺院。雷門と仲見世通りで有名。観音菩薩を本尊とし、年間約3000万人が訪れる東京を代表する観光スポット。',
                'address' => '東京都台東区浅草2-3-1',
                'latitude' => 35.7148,
                'longitude' => 139.7967,
                'image_url' => null,
            ]
        );

        // 浅草寺のご利益を追加
        $sensoji->spotBenefits()->delete();
        $sensoji->spotBenefits()->createMany([
            ['benefit_type_id' => 2, 'rating' => 4], // 健康運
            ['benefit_type_id' => 3, 'rating' => 5], // 金運
            ['benefit_type_id' => 4, 'rating' => 4], // 家庭運
            ['benefit_type_id' => 7, 'rating' => 5], // 厄除け
        ]);

        // 神田明神
        $kanda = Spot::updateOrCreate(
            ['name' => '神田明神'],
            [
                'description' => '江戸総鎮守として江戸時代から信仰を集める神社。商売繁盛、縁結び、厄除けのご利益があり、ビジネスマンにも人気。',
                'address' => '東京都千代田区外神田2-16-2',
                'latitude' => 35.7016,
                'longitude' => 139.7676,
                'image_url' => null,
            ]
        );

        // 神田明神のご利益を追加
        $kanda->spotBenefits()->delete();
        $kanda->spotBenefits()->createMany([
            ['benefit_type_id' => 1, 'rating' => 4], // 恋愛運
            ['benefit_type_id' => 3, 'rating' => 5], // 金運
            ['benefit_type_id' => 6, 'rating' => 5], // 仕事運
            ['benefit_type_id' => 7, 'rating' => 5], // 厄除け
        ]);

        // 増上寺
        $zojoji = Spot::updateOrCreate(
            ['name' => '増上寺'],
            [
                'description' => '徳川将軍家の菩提寺。東京タワーを背景にした境内の風景が美しい。6人の将軍が眠る歴史ある寺院。',
                'address' => '東京都港区芝公園4-7-35',
                'latitude' => 35.6575,
                'longitude' => 139.7476,
                'image_url' => null,
            ]
        );

        // 増上寺のご利益を追加
        $zojoji->spotBenefits()->delete();
        $zojoji->spotBenefits()->createMany([
            ['benefit_type_id' => 2, 'rating' => 5], // 健康運
            ['benefit_type_id' => 4, 'rating' => 5], // 家庭運
            ['benefit_type_id' => 5, 'rating' => 4], // 学業運
            ['benefit_type_id' => 7, 'rating' => 4], // 厄除け
        ]);
    }
}
