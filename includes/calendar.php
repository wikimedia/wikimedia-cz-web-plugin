<?php

use ICal\ICal;
use maxh\Nominatim\Nominatim;

require_once 'place.php';

class WmczCalendar {
    protected $ical;
    protected $url;
    protected $filename;
    protected $maxEvents;

   public function __construct($url, $maxEvents = null)
    {
        $this->url = $url;
        $this->filename = dirname( __FILE__ ) . '/../data/calendar-' . hash( "md5", $this->url ) . '.ical';

        if ( !file_exists( $this->filename ) || (time()-filemtime( $this->filename  )) > 24 * 3600 ) {
            file_put_contents(
                $this->filename,
                fopen( $this->url, 'r' )
            );
        }
        $this->ical = new ICal( $this->filename );
        $this->maxEvents = $maxEvents;
    }

    protected function formatEvents( $events ) {
        $res = [];

        for ($i=0; $i < $this->maxEvents ?? count( $events ); $i++) { 
            if ( isset( $events[$i] ) ) {
                $event = $events[$i];
                $date = $this->ical->iCalDateToDateTime($event->dtstart_array[3]);
                $matches = null;
                preg_match( '/[0-9 ]+ ([^0-9,-]+)/',  $event->location, $matches);
                $city = $matches[1];
                $res[] = [
                    'datetime' => $date->format('d. m. Y'),
                    'place' => $city,
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
        $matches = null;
        preg_match( '/[0-9 ]+ ([^0-9,-]+)/',  $address, $matches);
        $city = $matches[1];
        if ( !$city ) {
            return null;
        }
        $search->query( $city );
        $result = $nominatim->find($search);
        return new Place( $result[0]["lat"], $result[0]["lon"] );
    }

    public function getPlacesFetch() {
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

    public function getPlaces() {
        $file = dirname( __FILE__ ) .  '/../data/calendar-places-' . hash( "md5", $this->url ) . '.json';
        if ( !file_exists( $file ) || (time()-filemtime( $file )) > 7 * 24 * 3600 ) {
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