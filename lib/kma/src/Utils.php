<?php

namespace Banking\KMA;

class Utils
{
    public static function guid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public static function fileTime()
    {
        return intval((microtime(true) + 11644473600) * 10000000);
    }
}
