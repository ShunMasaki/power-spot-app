<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Spot;

class AdditionalSpotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spots = [
            // 渋谷区
            ['name' => '代々木八幡宮', 'description' => '鎌倉時代創建の古社。出世開運、厄除けのご利益で知られる。', 'address' => '東京都渋谷区代々木5-1-1', 'latitude' => 35.6741, 'longitude' => 139.6863],
            ['name' => '金王八幡宮', 'description' => '渋谷の鎮守。金王丸の伝説が残る歴史ある神社。', 'address' => '東京都渋谷区渋谷3-5-12', 'latitude' => 35.6621, 'longitude' => 139.7043],

            // 新宿区
            ['name' => '花園神社', 'description' => '新宿の総鎮守。商売繁盛、開運のご利益。新宿の歓楽街を見守る。', 'address' => '東京都新宿区新宿5-17-3', 'latitude' => 35.6941, 'longitude' => 139.7070],
            ['name' => '穴八幡宮', 'description' => '虫封じ・金銀融通の神として信仰を集める。一陽来復のお守りが有名。', 'address' => '東京都新宿区西早稲田2-1-11', 'latitude' => 35.7077, 'longitude' => 139.7199],
            ['name' => '須賀神社', 'description' => '四谷の総鎮守。映画「君の名は。」の舞台としても知られる。', 'address' => '東京都新宿区須賀町5', 'latitude' => 35.6869, 'longitude' => 139.7264],

            // 港区
            ['name' => '愛宕神社', 'description' => '東京23区内最高峰の愛宕山頂上に鎮座。出世の石段で有名。', 'address' => '東京都港区愛宕1-5-3', 'latitude' => 35.6661, 'longitude' => 139.7488],
            ['name' => '芝大神宮', 'description' => '関東のお伊勢様。商売繁盛、縁結びのご利益。', 'address' => '東京都港区芝大門1-12-7', 'latitude' => 35.6560, 'longitude' => 139.7508],
            ['name' => '氷川神社（赤坂）', 'description' => '赤坂の氷川様。縁結びのご利益で知られる。', 'address' => '東京都港区赤坂6-10-12', 'latitude' => 35.6697, 'longitude' => 139.7329],
            ['name' => '乃木神社', 'description' => '乃木希典将軍と夫人を祀る。勝利・学業のご利益。', 'address' => '東京都港区赤坂8-11-27', 'latitude' => 35.6646, 'longitude' => 139.7270],

            // 中央区
            ['name' => '水天宮', 'description' => '安産・子授けの神様として有名。江戸時代から続く信仰。', 'address' => '東京都中央区日本橋蛎殻町2-4-1', 'latitude' => 35.6858, 'longitude' => 139.7857],
            ['name' => '小網神社', 'description' => '日本橋の小さな神社。強運厄除、金運向上で知られる。', 'address' => '東京都中央区日本橋小網町16-23', 'latitude' => 35.6818, 'longitude' => 139.7796],
            ['name' => '波除稲荷神社', 'description' => '築地の守り神。災難除け、商売繁盛のご利益。', 'address' => '東京都中央区築地6-20-37', 'latitude' => 35.6661, 'longitude' => 139.7705],

            // 千代田区
            ['name' => '日枝神社', 'description' => '江戸三大祭の一つ山王祭で有名。出世・縁結びの神様。', 'address' => '東京都千代田区永田町2-10-5', 'latitude' => 35.6743, 'longitude' => 139.7398],
            ['name' => '靖国神社', 'description' => '戦没者を祀る神社。桜の名所としても有名。', 'address' => '東京都千代田区九段北3-1-1', 'latitude' => 35.6947, 'longitude' => 139.7440],
            ['name' => '神田神社（神田明神）', 'description' => '江戸総鎮守。商売繁盛、縁結びのご利益。', 'address' => '東京都千代田区外神田2-16-2', 'latitude' => 35.7016, 'longitude' => 139.7676],

            // 台東区
            ['name' => '浅草神社', 'description' => '三社祭で有名。浅草寺の隣に鎮座。', 'address' => '東京都台東区浅草2-3-1', 'latitude' => 35.7148, 'longitude' => 139.7980],
            ['name' => '鳥越神社', 'description' => '鳥越祭で知られる下町の神社。厄除けのご利益。', 'address' => '東京都台東区鳥越2-4-1', 'latitude' => 35.7060, 'longitude' => 139.7876],
            ['name' => '上野東照宮', 'description' => '徳川家康を祀る。金色殿と呼ばれる豪華な社殿。', 'address' => '東京都台東区上野公園9-88', 'latitude' => 35.7186, 'longitude' => 139.7738],
            ['name' => '今戸神社', 'description' => '招き猫発祥の地。縁結びのご利益で有名。', 'address' => '東京都台東区今戸1-5-22', 'latitude' => 35.7161, 'longitude' => 139.8032],

            // 文京区
            ['name' => '根津神社', 'description' => 'つつじの名所。千本鳥居が美しい古社。', 'address' => '東京都文京区根津1-28-9', 'latitude' => 35.7206, 'longitude' => 139.7614],
            ['name' => '湯島天満宮（湯島天神）', 'description' => '学問の神様菅原道真を祀る。合格祈願で有名。', 'address' => '東京都文京区湯島3-30-1', 'latitude' => 35.7077, 'longitude' => 139.7683],
            ['name' => '護国寺', 'description' => '徳川綱吉創建の真言宗豊山派の大本山。', 'address' => '東京都文京区大塚5-40-1', 'latitude' => 35.7200, 'longitude' => 139.7286],

            // 豊島区
            ['name' => '雑司ヶ谷鬼子母神堂', 'description' => '安産・子育ての神様。樹齢600年超の大イチョウがある。', 'address' => '東京都豊島区雑司が谷3-15-20', 'latitude' => 35.7198, 'longitude' => 139.7162],

            // 北区
            ['name' => '王子神社', 'description' => '王子の地名由来の神社。熊野信仰の拠点。', 'address' => '東京都北区王子本町1-1-12', 'latitude' => 35.7519, 'longitude' => 139.7374],

            // 荒川区
            ['name' => '素盞雄神社', 'description' => '南千住の総鎮守。厄除け・開運のご利益。', 'address' => '東京都荒川区南千住6-60-1', 'latitude' => 35.7344, 'longitude' => 139.7923],

            // 足立区
            ['name' => '西新井大師', 'description' => '関東三大師の一つ。厄除け開運で有名。', 'address' => '東京都足立区西新井1-15-1', 'latitude' => 35.7787, 'longitude' => 139.7838],

            // 葛飾区
            ['name' => '柴又帝釈天', 'description' => '映画「男はつらいよ」の舞台。商売繁盛のご利益。', 'address' => '東京都葛飾区柴又7-10-3', 'latitude' => 35.7619, 'longitude' => 139.8761],

            // 江戸川区
            ['name' => '小岩神社', 'description' => '小岩の総鎮守。厄除け・縁結びのご利益。', 'address' => '東京都江戸川区東小岩6-15-15', 'latitude' => 35.7342, 'longitude' => 139.8808],

            // 墨田区
            ['name' => '牛嶋神社', 'description' => '向島の総鎮守。撫牛信仰で知られる。', 'address' => '東京都墨田区向島1-4-5', 'latitude' => 35.7112, 'longitude' => 139.8095],

            // 江東区
            ['name' => '富岡八幡宮', 'description' => '深川八幡とも呼ばれる。勧進相撲発祥の地。', 'address' => '東京都江東区富岡1-20-3', 'latitude' => 35.6718, 'longitude' => 139.7979],
            ['name' => '亀戸天神社', 'description' => '学問の神様。藤の名所として有名。', 'address' => '東京都江東区亀戸3-6-1', 'latitude' => 35.6979, 'longitude' => 139.8266],

            // 品川区
            ['name' => '品川神社', 'description' => '品川宿の鎮守。出世開運のご利益。富士塚がある。', 'address' => '東京都品川区北品川3-7-15', 'latitude' => 35.6189, 'longitude' => 139.7386],
            ['name' => '荏原神社', 'description' => '南品川の鎮守。海上安全、商売繁盛のご利益。', 'address' => '東京都品川区北品川2-30-28', 'latitude' => 35.6178, 'longitude' => 139.7408],

            // 目黒区
            ['name' => '目黒不動尊', 'description' => '江戸五色不動の一つ。厄除け・開運のご利益。', 'address' => '東京都目黒区下目黒3-20-26', 'latitude' => 35.6316, 'longitude' => 139.7000],
            ['name' => '大鳥神社', 'description' => '目黒の総鎮守。商売繁盛、開運のご利益。', 'address' => '東京都目黒区下目黒3-1-2', 'latitude' => 35.6305, 'longitude' => 139.7014],

            // 大田区
            ['name' => '池上本門寺', 'description' => '日蓮宗の大本山。日蓮聖人終焉の地。', 'address' => '東京都大田区池上1-1-1', 'latitude' => 35.5803, 'longitude' => 139.7059],
            ['name' => '穴守稲荷神社', 'description' => '羽田の稲荷様。商売繁盛、家内安全のご利益。', 'address' => '東京都大田区羽田5-2-7', 'latitude' => 35.5499, 'longitude' => 139.7482],

            // 世田谷区
            ['name' => '松陰神社', 'description' => '吉田松陰を祀る。学業成就、合格祈願で人気。', 'address' => '東京都世田谷区若林4-35-1', 'latitude' => 35.6447, 'longitude' => 139.6608],
            ['name' => '豪徳寺', 'description' => '招き猫発祥の地の一つ。彦根藩井伊家の菩提寺。', 'address' => '東京都世田谷区豪徳寺2-24-7', 'latitude' => 35.6509, 'longitude' => 139.6430],
            ['name' => '等々力不動尊', 'description' => '渓谷沿いの霊場。厄除け、商売繁盛のご利益。', 'address' => '東京都世田谷区等々力1-22-47', 'latitude' => 35.6036, 'longitude' => 139.6493],

            // 中野区
            ['name' => '新井薬師', 'description' => '目の薬師として信仰を集める。', 'address' => '東京都中野区新井5-3-5', 'latitude' => 35.7161, 'longitude' => 139.6643],
            ['name' => '沼袋氷川神社', 'description' => '沼袋の鎮守。縁結び、家内安全のご利益。', 'address' => '東京都中野区沼袋1-31-4', 'latitude' => 35.7229, 'longitude' => 139.6694],

            // 杉並区
            ['name' => '大宮八幡宮', 'description' => '東京のへそと呼ばれる。安産・子育てのご利益。', 'address' => '東京都杉並区大宮2-3-1', 'latitude' => 35.6801, 'longitude' => 139.6378],
            ['name' => '井草八幡宮', 'description' => '源頼朝ゆかりの古社。厄除け、商売繁盛のご利益。', 'address' => '東京都杉並区善福寺1-33-1', 'latitude' => 35.7151, 'longitude' => 139.6011],

            // 練馬区
            ['name' => '石神井氷川神社', 'description' => '石神井の総鎮守。縁結び、厄除けのご利益。', 'address' => '東京都練馬区石神井台1-18-24', 'latitude' => 35.7413, 'longitude' => 139.5976],

            // 板橋区
            ['name' => '板橋不動尊', 'description' => '縁結び不動として知られる。', 'address' => '東京都板橋区蓮沼町48-8', 'latitude' => 35.7672, 'longitude' => 139.6956],

            // 追加50件
            // 渋谷区
            ['name' => '氷川神社（渋谷）', 'description' => '渋谷最古の神社。縁結びのご利益で知られる。', 'address' => '東京都渋谷区東2-5-6', 'latitude' => 35.6528, 'longitude' => 139.7139],
            ['name' => '渋谷区猿楽神社', 'description' => '代官山の小さな神社。芸能上達のご利益。', 'address' => '東京都渋谷区猿楽町11-6', 'latitude' => 35.6490, 'longitude' => 139.7032],

            // 新宿区
            ['name' => '神楽坂若宮八幡神社', 'description' => '神楽坂の守り神。商売繁盛のご利益。', 'address' => '東京都新宿区若宮町18', 'latitude' => 35.7023, 'longitude' => 139.7339],
            ['name' => '鎧神社', 'description' => '新宿の小さな神社。勝負運のご利益。', 'address' => '東京都新宿区北新宿3-16-18', 'latitude' => 35.6990, 'longitude' => 139.6883],
            ['name' => '市谷亀岡八幡宮', 'description' => '市ヶ谷の総鎮守。茶の木稲荷が有名。', 'address' => '東京都新宿区市谷八幡町15', 'latitude' => 35.6980, 'longitude' => 139.7264],

            // 港区
            ['name' => '十番稲荷神社', 'description' => '麻布十番の守り神。商売繁盛のご利益。', 'address' => '東京都港区麻布十番1-4-6', 'latitude' => 35.6551, 'longitude' => 139.7369],
            ['name' => '虎ノ門金刀比羅宮', 'description' => '金運、商売繁盛で有名。オフィス街の守り神。', 'address' => '東京都港区虎ノ門1-2-7', 'latitude' => 35.6677, 'longitude' => 139.7503],
            ['name' => '櫻田神社', 'description' => '新橋の氏神様。商売繁盛のご利益。', 'address' => '東京都港区新橋2-15-5', 'latitude' => 35.6671, 'longitude' => 139.7558],

            // 中央区
            ['name' => '茶ノ木神社', 'description' => '日本橋の小さな神社。商売繁盛のご利益。', 'address' => '東京都中央区日本橋人形町1-12-10', 'latitude' => 35.6858, 'longitude' => 139.7814],
            ['name' => '松島神社', 'description' => '日本橋人形町の氏神。良縁、開運のご利益。', 'address' => '東京都中央区日本橋人形町2-15-2', 'latitude' => 35.6851, 'longitude' => 139.7835],
            ['name' => '福徳神社', 'description' => '日本橋のビル群に囲まれた神社。宝くじ祈願で有名。', 'address' => '東京都中央区日本橋室町2-4-14', 'latitude' => 35.6863, 'longitude' => 139.7739],
            ['name' => '鉄砲洲稲荷神社', 'description' => '湊の総鎮守。商売繁盛、海上安全のご利益。', 'address' => '東京都中央区湊1-6-7', 'latitude' => 35.6652, 'longitude' => 139.7760],

            // 千代田区
            ['name' => '築土神社', 'description' => '九段下の神社。勝運、仕事運のご利益。', 'address' => '東京都千代田区九段北1-14-21', 'latitude' => 35.6963, 'longitude' => 139.7492],
            ['name' => '神田須田町稲荷神社', 'description' => '神田の商売繁盛の神様。', 'address' => '東京都千代田区神田須田町1-5', 'latitude' => 35.6960, 'longitude' => 139.7719],

            // 台東区
            ['name' => '小野照崎神社', 'description' => '入谷の守り神。学問・芸能のご利益。', 'address' => '東京都台東区下谷2-13-14', 'latitude' => 35.7203, 'longitude' => 139.7881],
            ['name' => '下谷神社', 'description' => '下谷の総鎮守。商売繁盛のご利益。', 'address' => '東京都台東区東上野3-29-8', 'latitude' => 35.7113, 'longitude' => 139.7807],
            ['name' => '矢先稲荷神社', 'description' => '合羽橋の近く。商売繁盛のご利益。', 'address' => '東京都台東区松が谷2-14-1', 'latitude' => 35.7105, 'longitude' => 139.7897],
            ['name' => '待乳山聖天', 'description' => '浅草の聖天様。商売繁盛、縁結びのご利益。', 'address' => '東京都台東区浅草7-4-1', 'latitude' => 35.7164, 'longitude' => 139.8050],

            // 文京区
            ['name' => '白山神社', 'description' => '白山のあじさい神社。縁結び、商売繁盛のご利益。', 'address' => '東京都文京区白山5-31-26', 'latitude' => 35.7240, 'longitude' => 139.7509],
            ['name' => '小石川大神宮', 'description' => '小石川の伊勢神宮。家内安全のご利益。', 'address' => '東京都文京区小石川2-6-18', 'latitude' => 35.7100, 'longitude' => 139.7425],
            ['name' => '牛天神北野神社', 'description' => '学問の神様。牛の像が有名。', 'address' => '東京都文京区春日1-5-2', 'latitude' => 35.7110, 'longitude' => 139.7495],

            // 豊島区
            ['name' => '大塚天祖神社', 'description' => '大塚の氏神様。家内安全、商売繁盛のご利益。', 'address' => '東京都豊島区南大塚3-49-1', 'latitude' => 35.7290, 'longitude' => 139.7280],
            ['name' => '池袋御嶽神社', 'description' => '池袋の小さな神社。開運のご利益。', 'address' => '東京都豊島区池袋3-51-2', 'latitude' => 35.7311, 'longitude' => 139.7134],

            // 北区
            ['name' => '王子稲荷神社', 'description' => '関東稲荷総社。商売繁盛、火伏せのご利益。', 'address' => '東京都北区岸町1-12-26', 'latitude' => 35.7523, 'longitude' => 139.7396],
            ['name' => '七社神社', 'description' => '西ヶ原の総鎮守。子育て、家内安全のご利益。', 'address' => '東京都北区西ヶ原2-11-1', 'latitude' => 35.7389, 'longitude' => 139.7425],

            // 荒川区
            ['name' => '三河島稲荷神社', 'description' => '三河島の守り神。商売繁盛のご利益。', 'address' => '東京都荒川区荒川4-34-2', 'latitude' => 35.7318, 'longitude' => 139.7813],
            ['name' => '石浜神社', 'description' => '南千住の古社。厄除け、縁結びのご利益。', 'address' => '東京都荒川区南千住3-28-58', 'latitude' => 35.7358, 'longitude' => 139.7941],

            // 足立区
            ['name' => '伊興氷川神社', 'description' => '伊興の総鎮守。家内安全のご利益。', 'address' => '東京都足立区伊興2-10-3', 'latitude' => 35.7804, 'longitude' => 139.8006],

            // 葛飾区
            ['name' => '白髭神社', 'description' => '東京のお伊勢さま。厄除け、延命長寿のご利益。', 'address' => '東京都墨田区東向島3-5-2', 'latitude' => 35.7170, 'longitude' => 139.8168],
            ['name' => '金町香取神社', 'description' => '金町の総鎮守。勝運、交通安全のご利益。', 'address' => '東京都葛飾区金町3-26-12', 'latitude' => 35.7614, 'longitude' => 139.8707],

            // 江戸川区
            ['name' => '平井諏訪神社', 'description' => '平井の鎮守。勝運、家内安全のご利益。', 'address' => '東京都江戸川区平井6-17-36', 'latitude' => 35.7059, 'longitude' => 139.8466],
            ['name' => '葛西神社', 'description' => '葛西の総鎮守。厄除け、商売繁盛のご利益。', 'address' => '東京都葛飾区東金町6-10-5', 'latitude' => 35.7593, 'longitude' => 139.8740],

            // 墨田区
            ['name' => '飛木稲荷神社', 'description' => '商売繁盛、縁結びのご利益。飛んできた御神木の伝説。', 'address' => '東京都墨田区押上2-39-6', 'latitude' => 35.7070, 'longitude' => 139.8103],
            ['name' => '三囲神社', 'description' => '三井家ゆかりの神社。商売繁盛のご利益。', 'address' => '東京都墨田区向島2-5-17', 'latitude' => 35.7126, 'longitude' => 139.8108],

            // 江東区
            ['name' => '洲崎神社', 'description' => '木場の氏神。航海安全、商売繁盛のご利益。', 'address' => '東京都江東区木場6-13-13', 'latitude' => 35.6732, 'longitude' => 139.8094],
            ['name' => '深川神明宮', 'description' => '深川の伊勢神宮。家内安全のご利益。', 'address' => '東京都江東区森下1-3-17', 'latitude' => 35.6860, 'longitude' => 139.7971],
            ['name' => '亀戸香取神社', 'description' => 'スポーツの神様。勝運向上のご利益。', 'address' => '東京都江東区亀戸3-57-22', 'latitude' => 35.6970, 'longitude' => 139.8254],

            // 品川区
            ['name' => '小山八幡神社', 'description' => '小山の総鎮守。開運、商売繁盛のご利益。', 'address' => '東京都品川区小山2-14-30', 'latitude' => 35.6145, 'longitude' => 139.7091],
            ['name' => '戸越八幡神社', 'description' => '戸越の氏神。家内安全、商売繁盛のご利益。', 'address' => '東京都品川区戸越2-6-23', 'latitude' => 35.6101, 'longitude' => 139.7143],

            // 目黒区
            ['name' => '五本木氷川神社', 'description' => '祐天寺の近く。縁結び、家内安全のご利益。', 'address' => '東京都目黒区中央町1-6-8', 'latitude' => 35.6382, 'longitude' => 139.6914],
            ['name' => '碑文谷八幡宮', 'description' => '碑文谷の総鎮守。厄除け、開運のご利益。', 'address' => '東京都目黒区碑文谷3-7-3', 'latitude' => 35.6218, 'longitude' => 139.6822],

            // 大田区
            ['name' => '新田神社', 'description' => '矢口の新田義貞ゆかりの神社。勝運のご利益。', 'address' => '東京都大田区矢口1-21-23', 'latitude' => 35.5684, 'longitude' => 139.6883],
            ['name' => '磐井神社', 'description' => '大森の古社。交通安全、商売繁盛のご利益。', 'address' => '東京都大田区大森北2-20-8', 'latitude' => 35.5913, 'longitude' => 139.7287],

            // 世田谷区
            ['name' => '駒留八幡神社', 'description' => '上馬の総鎮守。勝運、開運のご利益。', 'address' => '東京都世田谷区上馬4-31-10', 'latitude' => 35.6358, 'longitude' => 139.6756],
            ['name' => '奥沢神社', 'description' => '大蛇伝説で有名。厄除け、縁結びのご利益。', 'address' => '東京都世田谷区奥沢5-22-1', 'latitude' => 35.6105, 'longitude' => 139.6709],
            ['name' => '瀬田玉川神社', 'description' => '二子玉川の守り神。家内安全、商売繁盛のご利益。', 'address' => '東京都世田谷区瀬田4-11-31', 'latitude' => 35.6116, 'longitude' => 139.6281],

            // 中野区
            ['name' => '多田神社', 'description' => '中野区南台の鎮守。厄除け、開運のご利益。', 'address' => '東京都中野区南台3-43-1', 'latitude' => 35.6697, 'longitude' => 139.6624],
            ['name' => '氷川神社（中野）', 'description' => '中野の総鎮守。縁結び、商売繁盛のご利益。', 'address' => '東京都中野区東中野1-11-1', 'latitude' => 35.7077, 'longitude' => 139.6858],

            // 杉並区
            ['name' => '阿佐ヶ谷神明宮', 'description' => '阿佐ヶ谷の総鎮守。家内安全、商売繁盛のご利益。', 'address' => '東京都杉並区阿佐谷北1-25-5', 'latitude' => 35.7068, 'longitude' => 139.6366],
            ['name' => '馬橋稲荷神社', 'description' => '商売繁盛、火伏せのご利益。双龍鳥居が有名。', 'address' => '東京都杉並区阿佐谷南2-4-4', 'latitude' => 35.7025, 'longitude' => 139.6351],

            // 練馬区
            ['name' => '氷川神社（練馬）', 'description' => '練馬の総鎮守。縁結び、商売繁盛のご利益。', 'address' => '東京都練馬区豊玉南2-15-5', 'latitude' => 35.7308, 'longitude' => 139.6564],
        ];

        foreach ($spots as $spotData) {
            $spot = Spot::updateOrCreate(
                ['name' => $spotData['name']],
                $spotData
            );

            // ランダムに2-4個のご利益を付与
            $spot->spotBenefits()->delete();
            $benefitCount = rand(2, 4);
            $availableBenefits = [1, 2, 3, 4, 5, 6, 7];
            shuffle($availableBenefits);

            $benefits = [];
            for ($i = 0; $i < $benefitCount; $i++) {
                $benefits[] = [
                    'benefit_type_id' => $availableBenefits[$i],
                    'rating' => rand(3, 5)
                ];
            }

            $spot->spotBenefits()->createMany($benefits);
        }
    }
}

