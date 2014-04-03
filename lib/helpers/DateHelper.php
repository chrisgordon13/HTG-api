<?php

class DateHelper
{
    public function isValid($str_date, $str_date_format)
    {
        $date = DateTime::createFromFormat($str_date_format, $str_date);
        return $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
    }
}
