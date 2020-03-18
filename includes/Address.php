<?php

class Address {
    public $address;
    private $city;
    private $addressPlaceCache;

    public function __construct( $address ) {
        $this->address = $address;
        $this->addressPlaceCache = new AddressPlaceCache();
    }

    public function getCity() {
        if ( $this->city ) {
            return $this->city;
        }

        $matches = null;
        preg_match( '/, [0-9 ]+ ([^0-9,-]+)/',  $this->address, $matches);
        $this->city = $matches[1];
        return $this->city;
    }

    /**
     * Returns average place for this address
     */
    public function getPlace() {
        $city = $this->getCity();
        if ( !$city ) {
            return null;
        }
        return $this->addressPlaceCache->getPlace( $city );
    }
}