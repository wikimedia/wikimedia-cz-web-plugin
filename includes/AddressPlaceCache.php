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
            return new Place( $cachedVal["lat"], $cachedVal["lon"], $cachedVal["address"] );
        }
        $res = $this->getPlaceInternal( $address );
        $this->set( $address, $res );
        return $res;
    }

    /**
     * @return Place
     */
    private function getPlaceInternal( $address ) {
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org", [
            'User-Agent' => 'Wikimedia Czech Republic website/1.0 (https://wikimedia.cz; root@wikimedia.cz) via maxh/php-nominatim'
        ] );
        $search = $nominatim->newSearch();
        $search->query( $address );
        $result = $nominatim->find($search);
        return new Place( $result[0]["lat"], $result[0]["lon"], $address );
    }
}