<?php

use ICal\ICal;
use maxh\Nominatim\Nominatim;

require_once 'place.php';

class WmczCalendar {
    protected $ical;
    protected $url;
    protected $maxEvents;

   public function __construct($url, $maxEvents = null)
    {
        $this->url = $url;
        $this->ical = new ICal( $url );
        $this->maxEvents = $maxEvents;
    }

    protected function formatEvents( $events ) {
        $res = [];

        for ($i=0; $i < $this->maxEvents ?? count( $events ); $i++) { 
            if ( isset( $events[$i] ) ) {
                $event = $events[$i];
                $date = $this->ical->iCalDateToDateTime($event->dtstart_array[3]);
                $place = explode( ', ', $event->location );
                $res[] = [
                    'datetime' => $date->format('d. m. Y H:i'),
                    'place' => $place[0],
                    'title' => $event->summary
                ];
            } else {
                break;
            }
        }
        return $res;
    }

    protected function addressToPlace( $address ) {
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org" );
        $search = $nominatim->newSearch();
        $addressExploded = explode( ', ', $address );
        $search->query( implode( ', ', array_slice( $addressExploded, 0, 2 ) ) ); // HACK
        $result = $nominatim->find($search);
        return new Place( $result[0]["lat"], $result[0]["lon"] );
    }

    public function getPlacesFetch() {
        $places = [];
        $from = new DateTime();
        $to = new DateTime('+1 months');
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        foreach ( $events as $event ) {
            if ( $event->location ) {
                $places[] = $this->addressToPlace( $event->location );
            }
        }
        return $places;
    }

    public function getPlaces() {
        $file = dirname( __FILE__ ) .  '/../data/calendar-places-' . hash( "md5", $this->url ) . '.json';
        if ( !file_exists( $file ) ) {
            $places = $this->getPlacesFetch();
            file_put_contents( $file, json_encode( $places ) );
            return $places;
        }
        return json_decode( file_get_contents( $file ) );
    }

    public function getEventsNow() {
        $from = new DateTime();
        $to = new DateTime('+1 months');
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        return $this->formatEvents( $events );
    }

    public function getEventsNext() {
        $from = new DateTime('+1 month');
        $to = new DateTime('+2 months');
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        return $this->formatEvents( $events );
    }
}