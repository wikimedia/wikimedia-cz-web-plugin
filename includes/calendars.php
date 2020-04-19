<?php

class WmczCalendars {
    public $urls = [];

    public function __construct( array $urls = [], $maxEvents = null ) {
        $this->urls = $urls;
    }

    /**
     * Merge multiple iCal files
     *
     * @param array $urls
     * @return string Path to the file
     */
    public function mergeIcals( array $urls ) {
        sort( $urls );
        $textUrls = '';
        foreach ( $urls as $url ) {
            $textUrls .= $url;
        }
        $filename = dirname( __FILE__ ) . '/../data/calendars-cached-' .  hash( 'md5', $textUrls ) . '.ical';
        if (
            !file_exists( $filename ) ||
            (time()-filemtime( $filename  )) > 24 * 3600 ||
            filesize( $filename ) < 10
        ) {
            file_put_contents( $filename, $this->mergeIcalsRaw( $urls ) );
        }
        return $filename;
    }

    /**
     * Merge multiple iCal files identified by URLS (uncached)
     *
     * @see Based on https://shortener.wikimedia.cz/G4tqM
     * @param array $urls
     * @return string
     */
    private function mergeIcalsRaw( array $urls ) {
        // Strip last line of first iCal file
        $firstUrl = array_shift($urls);
        $firstFull = file_get_contents( $firstUrl );
        $matches = null;
        preg_match('/.*END:VEVENT/s', $firstFull, $matches);
        if ( count( $matches ) === 0 ) {
            $result = str_replace( "\r\nEND:VCALENDAR", '', $firstFull );
        } else {
            $result = $matches[0];
        }

        foreach ( $urls as $url ) {
            $contentFull = file_get_contents( $url );
            $matches = null;
            preg_match('/BEGIN:VTIMEZONE.*END:VEVENT/s', $contentFull, $matches);
            if ( count( $matches ) == 0 ) {
                continue;
            }
            $result .= $matches[0];
        }
        $result .= "\r\nEND:VCALENDAR";

        return $result;
    }

    /**
     * Get WMczCalendar object
     *
     * @param array|null $names Indexes of calendars you want to get
     */
    public function getCalendar($indexes = null) {
        if ( $indexes === null ) {
            $urls = $this->urls;
        } else {
            $urls = [];
            foreach ( $indexes as $i ) {
                $urls[] = $this->urls[$i];
            }
        }
        $icalPath = $this->mergeIcals( $urls );
        return new WmczCalendar( $icalPath );
    }
}