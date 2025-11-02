<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Spot;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // まずユーザーとスポットを1つずつ作成
        $user = User::first() ?? User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'cognito_sub' => \Str::uuid(),
            'nickname' => 'パワスポ太郎',
        ]);

        $spot = Spot::first() ?? Spot::factory()->create([
            'name' => '富士山本宮浅間大社',
            'address' => '静岡県富士宮市',
            'description' => '霊峰富士山の麓に鎮座する格式高い神社です。',
        ]);

        // 複数レビューを作成
        Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'spot_id' => $spot->id,
        ]);
    }
}
