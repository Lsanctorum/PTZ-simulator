<?php

use PtzSimulator\Manager\CityManager;
use PtzSimulator\Twig\PriceExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'));
$twig->addExtension(new PriceExtension());

$cityManager = new CityManager();

$search = $_GET['search'] ?? null;
if (!empty($search)) {
    $search = trim($search);
    if (strlen($search) >= 3) {
        echo $cityManager->formatDataForSelect($search);
        return;
    }
    throw new Exception('Should not happend');
}

if (!empty($_POST['compute']) && true == $_POST['compute']) {
    $fiscalRevenue = $_POST['fiscalRevenue'] ?? 0;
    $people = $_POST['people'] ?? 1;
    $citiesId = $_POST['cities'] ?? [];
    $interval = (isset($_POST['interval']) && (int) $_POST['interval'] >= 500) ? $_POST['interval'] : 1000;
    $price = $_POST['price'] ?? 0;
    $rayon = $_POST['rayon'] ?? 0;

    if ($rayon > 0 && 1 === count($citiesId)) {
        $city = $cityManager->fetchOne($citiesId[0]);
        $cities = $cityManager->findByRayon($city, $rayon);
    } else {
        $cities = $cityManager->findMultipleByIds(...$citiesId);
    }

    foreach ($cities as $city) {
        $ptz = (new \PtzSimulator\Entity\Ptz())
            ->setPrice($price)
            ->setCity($city)
            ->setPeople($people)
            ->setFiscalRevenue($fiscalRevenue);

        $ptz->computePtzWithInterval($interval);

        $results[] = $ptz;
    }

    echo json_encode([
        'template' => $twig->render('results.html.twig', ['results' => $results ?? []]),
        'hasData' => !empty($results)
    ]);
    return;
}

function getMiddleGpsPoint(array $arr) {
    $closest = null;
    $search = (array_sum($arr) / count($arr));
    foreach ($arr as $item) {
        if (null === $closest || abs($search - $closest) > abs($item - $search)) {
            $closest = $item;
        }
    }
    return $closest;
}

function getMedian($arr) {
    sort($arr);
    $count = count($arr);
    $middleval = floor(($count-1)/2);
    if ($count % 2) {
        $median = $arr[$middleval];
    } else {
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    return $median;
}

echo $twig->render('index.html.twig');
