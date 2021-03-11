<?php

try {
    $input_obj = new \DateTime($argv[1]);
    if ($input_obj->format('Y/m/d') != $argv[1] && $input_obj->format('Y/n/j') != $argv[1]) {
       throw new Exception();
    }
} catch (\Exception $e) {
    echo "入力された日付に誤りがあります。\n";
    echo "Y/m/d形式で入力してください。\n";
    exit();
}

// 内閣府から直近の祝日情報を取得する
$remote = "https://www8.cao.go.jp/chosei/shukujitsu/syukujitsu.csv";
$curl_obj = curl_init();
curl_setopt($curl_obj, CURLOPT_HEADER, false);
curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl_obj, CURLOPT_URL, $remote);
$res_sjis = curl_exec($curl_obj);

// sjis->utf8
$res_utf8 = mb_convert_encoding($res_sjis, "UTF8", "SJIS");

// 改行でparse
$lines = explode("\n", $res_utf8);

// 祝日情報はここにいれる
$public_holiday_array = [];

// 祝日情報レコード分ループ
foreach ($lines as $key => $val) {
    // ヘッダは読み飛ばし
    if ($key == 0)
    {
        continue;
    }

    // ,でparse
    $col = explode(",", $val);

    // 空行は読み捨て
    if (!$col[0]) {
        continue;
    }

    // DateTimeでエラーだったら無視
    try {
        $buf_obj = new \DateTime($col[0]);
        $public_holiday_array[] = [
            'name' => trim($col[1]),
            'date' => $buf_obj->format("Y/m/d"),
        ];

    } catch(Exception $e) {
        // 無視
    }
}

// 祝日判定
if ($result = array_search($input_obj->format('Y/m/d'), array_column($public_holiday_array, 'date'))) {
    echo $input_obj->format('Y/m/d'). "は祝日です。" .$public_holiday_array[$result]['name'] ."\n";
} elseif  ($input_obj->format('w') == 0) {
    echo $input_obj->format('Y/m/d'). "は日曜日です。\n";
} else {
    echo $input_obj->format('Y/m/d'). "は平日です。\n";
}



