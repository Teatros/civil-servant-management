<?php

namespace app\lib\util;

class UUIDUtil {
    public static function getUUID(): string
    {
        return sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
                                                                                                                                                                                                                                                                                                                                                                             mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),

            mt_rand(0, 0xFFFF),

            mt_rand(0, 0x0FFF) | 0x4000,

            mt_rand(0, 0x3FFF) | 0x8000,

            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );
    }
}