<?php

class Place {
    public $lat;
    public $lon;
    public $address;
    public $link;
    
    public function __construct($lat, $lon, $address = null) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->address = $address;
        $this->link = null;
    }

    /**
     * Set link
     * 
     * @param string|null $link Link, null to unset
     */
    public function setLink($link) {
        $this->link = $link;
    }
}