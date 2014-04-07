<?php

class GeoHelper
{
    public function isLat($lat)
    {
        if (preg_match("/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}$/", $lat)) {
            return true;
        } else {
            return false;
        }
    }

    public function isLon($lon)
    {
        if(preg_match("/^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,6}$/", $lon)) {
           return true;
        } else {
           return false;
        }
    }
}
