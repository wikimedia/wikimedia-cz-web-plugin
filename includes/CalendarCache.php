<?php

use ICal\ICal;

class WmczCalendarCache {
    private $url;
    private $filename;
    private $ical;
    private $cacheExpiry;

    public function __construct( $url, $cacheExpiry = 24 * 3600 ) {
        $this->url = $url;
        $this->filename = dirname( __FILE__ ) . '/../data/calendar-ical-serialized-' . hash( "md5", $this->url ) . '.txt';
        $this->cacheExpiry = $cacheExpiry;
    }

    public function getIcalObj() {
        if ( $this->ical ) {
            return $this->ical;
        }

        if (
            !file_exists( $this->filename ) ||
            (time()-filemtime( $this->filename  )) > 24 * 3600 ||
            filesize( $this->filename ) < 10
        ) {
            $this->ical = new ICal( $this->url );
            file_put_contents( $this->filename, serialize( $this->ical ) );
            file_put_contents( $this->filename . '.url', $this->url );
        } else {
            $this->ical = unserialize(file_get_contents( $this->filename ));
        }

        return $this->ical;
    }
}
