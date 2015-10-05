<?php

require_once '../shared/dataInterchange.php';

$reply = Satan_Shared_TrustedDataInterchange::send(
    Satan_Shared_TrustedDataInterchange::ROUTE_DATA_MINING,
    ['x' => 1, 'y' => 2]
);

var_dump($reply);
