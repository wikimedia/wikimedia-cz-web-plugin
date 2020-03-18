<?php

class Place {
    public $lat;
    public $lon;
    public $address;
    
    public function __construct($lat, $lon, $address = null) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->address = $address;
    }
}