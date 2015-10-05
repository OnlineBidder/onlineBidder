<?php

/**
 * @package Satan\shared
 */

class Satan_Shared_TrustedDataInterchange
{
    const ROUTE_DATA_MINING = 1;
    const ROUTE_THROTTLER   = 2;
    const ROUTE_BROKER      = 3;
    const ROUTE_PANDORA     = 4;


    /**
     * @param int $destination - one of self::ROUTE_* constants
     * @param mixed $data
     * @throws Exception
     * @return mixed
     */
    public static function send($destination, $data)
    {
        $urls = self::_getUrls();
        if (!isset($urls[$destination])) {
            throw new Exception;
        }

        $url = $urls[$destination];
        $packed = serialize($data);

        $context  = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query(['packed_data' => $packed]),
            ],
        ]);
        $reply = file_get_contents($url, false, $context);

        return unserialize($reply);
    }

    public static function read()
    {
        return unserialize($_POST['packed_data']);
    }

    public static function reply($reply)
    {
        echo serialize($reply);
    }

    private static function _getUrls()
    {
        return [
            self::ROUTE_DATA_MINING => 'http://127.0.0.1:8001/trustedio.php',
            self::ROUTE_THROTTLER   => 'http://127.0.0.1:8002/trustedio.php',
            self::ROUTE_BROKER      => 'http://127.0.0.1:8000/trustedio.php',
            self::ROUTE_PANDORA     => 'http://127.0.0.1:8003/trustedio.php',
        ];
    }
}
