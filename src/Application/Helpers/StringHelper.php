<?php
declare(strict_types=1);

namespace App\Application\Helpers;

class StringHelper
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function generateRandomString($length = 10): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Длина не может быть меньше 1');
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * @param int $days
     * @return string
     */
    public static function convertDuration(int $days): string
    {
        $month = $days/30;
        switch ($month) {
            case 1:
                return '1 месяц';
            case $month > 21 && $month < 25:
            case $month > 1 && $month <5:
                return $month . ' месяца';
            case $month > 4 && $month < 21:
                return $month . ' месяц';
        }
    }
}