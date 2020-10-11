<?php

class ZipCache {
    /** @var array */
    private $knownZipCodes;

    /** @var ApistorePscClient */
    private $apistoreClient;

    public function __construct() {
        $this->apistoreClient = new ApistorePscClient();
    }

    /**
     * Parse ZIP into a city, cache is used
     *
     * @param string|int $zip
     * @return string|false City name when successful, false otherwise
     */
    public function getCity($zip) {
        $cachedVal = $this->getZipCode($zip);
        if ($cachedVal) {
            return $cachedVal;
        }

        $city = $this->getCityInternal($zip);
        $this->setZipCode($zip, $city);
        return $city;
    }

    /**
     * Parse ZIP code into a city, internal code
     *
     * No cache used
     *
     * @param string|int $zip
     * @return string|false City name when successful, false otherwise
     */
    private function getCityInternal($zip) {
        return $this->apistoreClient->parseZip( $zip );
    }

    private function getCacheFile() {
        return dirname( __FILE__ ) .  '/../data/zip-codes.json';
    }

    /**
     * Helper function to get known ZIP codes
     */
    private function getKnownZipCodes() {
        if ( $this->knownZipCodes ) {
            return $this->knownZipCodes;
        }

        $file = $this->getCacheFile();
        if ( !file_exists( $file ) ) {
            file_put_contents( $file, '[]' );
            return [];
        }
        $this->knownZipCodes = json_decode( file_get_contents( $file ), true );
        return $this->knownZipCodes;
    }

    /**
     * @param string|int $zip
     * @return string|false City name when successful, false otherwise
     */
    private function getZipCode($zip) {
        if (is_string($zip)) {
            $zip = str_replace( ' ', '', $zip );
        }
        $zip = (int)$zip;

        $cached = $this->getKnownZipCodes();
        if (array_key_exists($zip, $cached)) {
            return $cached[$zip];
        }
        return false;
    }

    /**
     * @param string|int $zip
     * @param string $city
     */
    private function setZipCode($zip, $city) {
        if (is_string($zip)) {
            $zip = str_replace( ' ', '', $zip );
        }
        $zip = (int)$zip;

        $knownZips = $this->getKnownZipCodes();
        $knownZips[$zip] = $city;
        $this->knownZipCodes = $knownZips;
        file_put_contents($this->getCacheFile(), json_encode($knownZips));
    }
}