<?php

use Carbon\Carbon;

class DateHelper
{
    /**
     * Returns param if already carbon, else formats with Y-m-d H:i:s
     * @param $date
     * @return Carbon|false
     */
    public static function convertToCarbon($date)
    {
        if ($date instanceof Carbon) return $date;
        elseif (is_string($date)) return Carbon::createFromFormat('Y-m-d H:i:s', $date);
        else return false;
    }
}