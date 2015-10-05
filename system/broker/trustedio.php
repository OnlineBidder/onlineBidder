<?php

require_once '../shared/dataInterchange.php';

$requests = Satan_Shared_TrustedDataInterchange::read();

$reply = [];
foreach ($requests as $key => $request) {
    switch ($request['command']) {
        case 'getParamsDefinition':
            $reply[$key] = [
                'male' => ['f', 'm'],
                'age'  => [[15, 20], ]
            ];
            break;
    }
}

Satan_Shared_TrustedDataInterchange::reply($reply);
