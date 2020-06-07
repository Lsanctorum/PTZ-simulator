<?php

require __DIR__ . '/vendor/autoload.php';

generateDataFile();

function generateDataFile()
{
    $cityManager = new \PtzSimulator\Manager\CityManager();
    $cities = \JsonMachine\JsonMachine::fromFile(__DIR__ . '/data/communes.json');

    $zoneCities = json_decode(file_get_contents(__DIR__ . '/data/zonage-commune.json'), true)['zonageCommunes'];

    $zoneCitiesWithKeyAsCode = [];
    foreach ($zoneCities as $zoneCity) {
        $zoneCitiesWithKeyAsCode[$zoneCity['codeInsee']] = $zoneCity;
    }
    unset($zoneCities);

    $i = 1;
    foreach ($cities as $city) {
        $code = $city['code'] ?? null;
        if (null === $code) {
            var_dump($city);
            continue;
        }

        $name = $city['nom'];
        $zoneCity = $zoneCitiesWithKeyAsCode[$code] ?? null;

        if (null === $zoneCity) {
            var_dump("Zone non trouvée pour " . $code . " " . $name);
            continue;
        }

        $coordinateGPS = $city['centre']['coordinates'];
        $cityData = [
            'id' => $code,
            'name' => $name,
            'zone' => $zoneCity['zone'],
            'zip_codes' => $city['codesPostaux'],
            'department' => $city['departement']['code'],
            'longitude' => $coordinateGPS[0],
            'latitude' => $coordinateGPS[1],
        ];

        $cityManager->persist($cityManager->buildCity($cityData));

        echo 'OK ' . $name . ' ' . $i . ' || ';
        $i++;
    }
}
