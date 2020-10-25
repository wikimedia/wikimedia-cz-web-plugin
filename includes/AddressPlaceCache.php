<?php

use maxh\Nominatim\Nominatim;

class AddressPlaceCache extends FileCache {
    public function __construct() {
        parent::__construct( 'known-addresses', 0 );
    }

    /**
     * @return Place
     */
    public function getPlace( $address ) {
        $cachedVal = $this->get($address);
        if ( $cachedVal ) {
            return $cachedVal;
        }
        $res = $this->getPlaceInternal( $address );
        $this->set( $address, $res );
        return $res;
    }

    /**
     * @return Place
     */
    private function getPlaceInternal( $address ) {
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org" );
        $search = $nominatim->newSearch();
        $search->query( $address );
        $result = $nominatim->find($search);
        return new Place( $result[0]["lat"], $result[0]["lon"], $address );
    }
}