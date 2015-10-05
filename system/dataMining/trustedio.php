<?php

require_once '../shared/dataInterchange.php';

$data = Satan_Shared_TrustedDataInterchange::read();
$data['reply'] = ['yes'];
Satan_Shared_TrustedDataInterchange::reply($data);
