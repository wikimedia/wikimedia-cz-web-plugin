<?php

use maxh\Nominatim\Nominatim;

class AddressPlaceCache {
    private $knownAddresses;

    public function __construct() {
    }

    public function getPlace( $address ) {
        $cachedAddreses = $this->getKnownAddresses();
        if ( array_key_exists( $address, $cachedAddreses ) ) {
            return $cachedAddreses[$address];
        }
        $res = $this->getPlaceInternal( $address );
        $this->setKnownAddress( $address, $res );
        return $res;
    }

    private function getPlaceInternal( $address ) {
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org" );
        $search = $nominatim->newSearch();
        $search->query( $address );
        $result = $nominatim->find($search);
        return new Place( $result[0]["lat"], $result[0]["lon"] );
    }

    /**
     * Helper function for caching known addresses
     *
     * @return string
     */
    private function getKnownAddressesFile() {
        return dirname( __FILE__ ) .  '/../data/known-addresses.json';
    }

    /**
     * Helper function for caching known addresses
     *
     * Returns directory of already known addresses,
     * so we don't contact Nominatim uselessly, when we already
     * have that information.
     *
     * @return array
     */
    private function getKnownAddresses() {
        if ( $this->knownAddresses ) {
            return $this->knownAddresses;
        }
        $file = $this->getKnownAddressesFile();
        if ( !file_exists( $file ) ) {
            file_put_contents( $file, '[]' );
            return [];
        }
        return json_decode( file_get_contents( $file ), true );
    }

    /**
     * Helper function for caching known addresses
     *
     * Adds known address to the cache
     */
    private function setKnownAddress( $address, $point ) {
        $knownAddresses = $this->getKnownAddresses();
        $knownAddresses[$address] = $point;
        $this->knownAddresses = $knownAddresses;
        file_put_contents( $this->getKnownAddressesFile(), json_encode( $knownAddresses ) );
    }
}