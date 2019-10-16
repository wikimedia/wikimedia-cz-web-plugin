<?php

class Place {
    public $lat;
    public $lon;
    
    public function __construct($lat, $lon) {
        $this->lat = $lat;
        $this->lon = $lon;
    }
}