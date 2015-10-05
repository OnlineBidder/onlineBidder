<?php

header('Content-Type: application/json');

// Set up the ORM library
require_once('setup.php');

if (isset($_GET['start']) AND isset($_GET['end'])) {

    $start = $_GET['start'];
    $end = $_GET['end'];
    $data = array();

    // Select the results with Idiorm
    $results = ORM::for_table('testCpm')
        ->where_gte('insert_time', strtotime($start))
        ->where_lte('insert_time', strtotime($end)+86400)
        ->where('ad_id', (int) $_GET['adId'])
        ->order_by_asc('insert_time')
        ->find_array();
//var_dump($results);
    // Build a new array with the data
    foreach ($results as $key => $value) {
        if ($value['insert_time'] <= 1424866396) {
            $value['cpm'] = $value['cpm'] / 0.9;
        }
        $data[$key]['label'] = round($value['cpm'], 2);

        $data[$key]['value'] = $value['cpc'];
    }

    echo json_encode($data);
}

/*
if (isset($_GET['start']) AND isset($_GET['end'])) {

    $start = $_GET['start'];
    $end = $_GET['end'];
    $file = '../../logs/'.$_GET['fileName'];
    $data = $results = array();


    $file = file($file);

    foreach ($file as $key => $string) {
        if (strpos($string, ':') !== false) {
            $arString = explode(' ', $string);
            if ($arString[0] == $start) {
                $results[$key]['label'] = $arString[0] . ' ' . $arString[1];
                $results[$key]['value'] = $arString[7]*100;
            }
        }
    }
    //$results = array_slice($results, 0, 10);

    echo json_encode($results);
}*/
