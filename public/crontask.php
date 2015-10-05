<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 21.12.14
 * Time: 22:19
 * To change this template use File | Settings | File Templates.
 */
return 1;
ini_set('ignore_user_abort', 1);
ini_set('max_execution_time', 59);
for ($i = 1; $i < 3; $i++) {
    $ch = curl_init("http://185.20.227.155/vk/adsManager");

    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_exec($ch);
    curl_close($ch);
    sleep(30);
}
