<?php

class Address {
    public $address;
    private $city;
    private $zipCode;
    private $addressPlaceCache;

    /** @var ZipCache */
    private $zipCache;

    public function __construct( $address ) {
        $this->address = $address;
        $this->addressPlaceCache = new AddressPlaceCache();
        $this->zipCache = new ZipCache();
    }

    /**
     * Get ZIP code
     */
    public function getZip() {
        if ( $this->zipCode ) {
            return $this->zipCode;
        }

        $matches = null;
        preg_match( '/[0-9]{3} ?[0-9]{2}/', $this->address, $matches );
        $this->zipCode = $matches[0] ?? '';
        return $this->zipCode;
    }

    public function getCity() {
        if ( $this->city ) {
            return $this->city;
        }

        $this->city = $this->parseCity();

        /*$this->city = $this->zipCache->getCity( $this->getZip() );
        if (!$this->city) {
            // Use legacy parsing
            $this->city = $this->parseCity();
        }*/
        return $this->city;
    }

    /**
     * Parse city via regexes
     * 
     * When zip code parsing fails...
     */
    private function parseCity() {
        $matches = null;
        preg_match_all( '/[0-9 ]+ ([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+)/',  $this->address, $matches);
        $city = trim( end( end( $matches ) ) );
        if ( $city == "" ) {
            preg_match_all( '/[0-9 \/]+, ([0-9 ]* ?([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+))/', $this->address, $matches );
            $city = trim( end( end( $matches ) ) );
        }
        return $city;
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