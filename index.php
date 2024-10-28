<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$url = 'https://api.online-czs.ru/addorg2/';
$apiKey = 'fhjdkj33zxhhfg';


$inputFileName = './baseTest.xlsx';
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getActiveSheet();


$allCompaniesData = $sheet->toArray(null, true, true, true);
$isFirst = true;
$count = 0;
$companiesData = [];


foreach ($allCompaniesData as $company) {
    if ($isFirst) {
        $isFirst = false;
        continue;
    }

    $inn = $company["B"];

    $companiesData[] = [
        'org_name' => $company["A"],
        'inn'      => $company["B"],
        'country'  => 'Russia',
        'city'     => $company["G"],
        'adress'   => $company["F"],
        'okved'    => getOkved($inn)
    ];
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "api-key: $apiKey"
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($companiesData));
$response = curl_exec($ch);

curl_close($ch);


function getOkved($inn)
{
    $urlFns = 'https://api-fns.ru/api/egr';
    $apiKeyFns = '371c056c7584773b884127832d84f6b0626f5390';


    $data = [
        'req' => $inn,
        'key' => $apiKeyFns
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $urlFns . '?' . http_build_query($data));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Ошибка: ' . curl_error($ch);
    }

    curl_close($ch);
    $result = json_decode($response, true);

    return $result['items'][0]['ЮЛ']['ОснВидДеят']['Код'];
}
