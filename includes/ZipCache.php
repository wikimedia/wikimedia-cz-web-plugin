<?php

class ZipCache extends FileCache {
    /** @var ApistorePscClient */
    private $apistoreClient;

    public function __construct() {
        parent::__construct('zip-codes', 0);

        $this->apistoreClient = new ApistorePscClient();
    }

    private function standardizeZip($zip) {
        if (is_string($zip)) {
            $zip = str_replace( ' ', '', $zip );
        }
        return (int)$zip;
    }

    /**
     * Parse ZIP into a city, cache is used
     *
     * @param string|int $zip
     * @return string|false City name when successful, false otherwise
     */
    public function getCity($zip) {
        $zip = $this->standardizeZip($zip);

        $cachedVal = $this->get($zip);
        if ($cachedVal !== null) {
            return $cachedVal;
        }

        $city = $this->getCityInternal($zip);
        $this->set($zip, $city);
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
}