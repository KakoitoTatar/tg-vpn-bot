<?php

declare(strict_types=1);

namespace App\Application\Helpers;

class DateTimeHelper
{
    public static function rangeWeek($datestr)
    {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime($datestr);
        return array(
            "start" => date('N', $dt) == 1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt)),
            "end" => date('N', $dt) == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt))
        );
    }
}