<?php

class ApistorePscClient {
    public function __construct() {
    }

    /**
     * Return API key
     */
    private function getApiKey() {
        return 'GbStq2p55I3G9oBxPKlz538YEpHMJH1BaLG3iWnr';
    }

    /**
     * Parse a ZIP code
     *
     * @param string|int $zip
     * @return string|bool City name when successful, false otherwise
     */
    public function parseZip($zip) {
        if (is_string( $zip )) {
            $zip = str_replace( ' ', '', $zip );
        }

        $data = $this->query('/cpost.cz/psc', [
            'where' => [
                'PSC' => $zip
            ]
        ]);

        foreach ( $data->data as $entry ) {
            if ($entry->PSC != $zip) {
                continue; // Sanity check
            }
            return $entry->NAZOBCE;
        }
        return false;
    }

    public function query($path, $filterQuery=null) {
        $url = str_replace( '//', '/', 'https://api.apitalks.store/' . $path);
        if ( is_array( $filterQuery ) ) {
            $url .= '?filter=' . urlencode( json_encode( $filterQuery ) );
        }        

        return $this->requestUrl($url);
    }

    private function requestUrl($url) {
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->getApiKey()
        ] );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch);
        return json_decode($output);
    }
}
