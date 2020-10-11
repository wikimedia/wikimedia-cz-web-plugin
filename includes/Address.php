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
        preg_match_all( '/[0-9 ]+ ([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+)/',  $this->address, $matches);
        $this->city = trim( end( end( $matches ) ) );
        if ( $this->city == "" ) {
            preg_match_all( '/[0-9 \/]+, ([0-9 ]* ?([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+))/', $this->address, $matches );
            $this->city = trim( end( end( $matches ) ) );
        }
        return $this->city;
    }

    public function getAveragePlace() {
        $city = $this->getCity();
        if ( !$city ) {
            return null;
        }
        return $this->addressPlaceCache->getPlace( $city );
    }

    /**
     * Returns average place for this address
     *
     * @deprecated use getAveragePlace instead
     */
    public function getPlace() {
        return $this->getAveragePlace();
    }
}