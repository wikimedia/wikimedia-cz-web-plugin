<?php

use ICal\ICal;
use maxh\Nominatim\Nominatim;

require_once 'place.php';
require_once 'calendar-cache.php';

class WmczCalendar {
    protected $ical;
    protected $url;
    protected $maxEvents;
    protected $knownAddresses;
    private $calendarCache;

   public function __construct($url, $maxEvents = null)
    {
        $this->url = $url;
        $this->maxEvents = $maxEvents;
        $this->calendarCache = new WmczCalendarCache( $this->url );
        $this->ical = $this->calendarCache->getIcalObj();
    }

    /**
     * Formats passed events to a shorer array used by callers
     * 
     * @return array
     */
    protected function formatEvents( $events ) {
        $res = [];

        $limit = $this->maxEvents ?? count( $events );
        for ($i=0; $i < $limit; $i++) {
            if ( isset( $events[$i] ) ) {
                $event = $events[$i];
                $startDate = $this->ical->iCalDateToDateTime($event->dtstart_array[3]);
                $endDate = $this->ical->iCalDateToDateTime($event->dtend_array[3]);
                $matches = null;
                preg_match( '/, [0-9 ]+ ([^0-9,-]+)/',  $event->location, $matches);
                $city = $matches[1];
                preg_match('/^(\[([^]]+)\])?\s*(.*)$/', $event->summary, $matches);
                if ( $matches[2] === "" ) {
                    $tags = [];
                } else {
                    $tags = explode( ', ', $matches[2] );
                }
                $clearSummary = $matches[3];
                $res[] = [
                    'displayDatetime' => $startDate->format('d. m. Y'),
                    'startDatetime' => $startDate->format('d. m. Y h:m'),
                    'endDatetime' => $endDate->format('d. m. Y h:m'),
                    'location' => $event->location,
                    'city' => $city,
                    'title' => $clearSummary,
                    'description' => $event->description,
                    'id' => hash('md5', $event->title . $event->dtstart . $event->dtend),
                    'tags' => $tags
                ];
            } else {
                break;
            }
        }
        return $res;
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

    /**
     * Converts given address to a place
     *
     * @return Place
     */
    protected function addressToPlace( $address ) {
        $matches = null;
        preg_match( '/, [0-9 ]+ ([^0-9,-]+)/',  $address, $matches);
        $city = $matches[1];
        if ( !$city ) {
            return null;
        }
        $knownAddresses = $this->getKnownAddresses();
        if ( array_key_exists( $city, $knownAddresses ) ) {
            return $knownAddresses[$city];
        }
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org" );
        $search = $nominatim->newSearch();
        $search->query( $city );
        $result = $nominatim->find($search);
        $place = new Place( $result[0]["lat"], $result[0]["lon"] );
        $this->setKnownAddress( $city, $place );
        return $place;
    }

    /**
     * Gets all places used in events in this calendar
     *
     * @return array
     */
    public function getPlaces() {
        $places = [];
        $from = new DateTime('-1 month');
        $to = new DateTime('+11 months');
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        foreach ( $events as $event ) {
            if ( !is_null( $event->location ) ) {
                $place = $this->addressToPlace( $event->location );
                if ( $place ) {
                    $places[] = $place;
                }
            }
        }
        return $places;
    }

    /**
     * Returns events happening in certain time period
     *
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     */
    public function getEvents(DateTime $from, DateTime $to) {
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        return $this->formatEvents( $events );
    }

    /**
     * Returns events happening in upcoming month
     *
     * @return array
     */
    public function getEventsNow() {
        return $this->getEvents(new DateTime(), new DateTime( '+1 months' ));
    }

    /**
     * Returns events happening in next month
     *
     * @return array
     */
    public function getEventsNext() {
        return $this->getEvents(new DateTime('+1 month'), new DateTime( '+2 months' ));
    }
}