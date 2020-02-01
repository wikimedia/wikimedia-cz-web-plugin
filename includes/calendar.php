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
        $this->filename = dirname( __FILE__ ) . '/../data/calendar-ical-serialized-' . hash( "md5", $this->url ) . '.txt';
        $this->maxEvents = $maxEvents;

        if (
            !file_exists( $this->filename ) ||
            (time()-filemtime( $this->filename  )) > 24 * 3600 ||
            filesize( $this->filename ) < 10
        ) {
            $this->ical = new ICal( $this->url );
            file_put_contents( $this->filename, serialize( $this->ical ) );
        } else {
            $this->ical = unserialize(file_get_contents( $this->filename ));
        }
    }

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

    protected function addressToPlace( $address ) {
        $nominatim = new Nominatim( "https://nominatim.openstreetmap.org" );
        $search = $nominatim->newSearch();
        $matches = null;
        preg_match( '/, [0-9 ]+ ([^0-9,-]+)/',  $address, $matches);
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

    public function getEvents(DateTime $from, DateTime $to) {
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        return $this->formatEvents( $events );
    }

    public function getEventsNow() {
        return $this->getEvents(new DateTime(), new DateTime( '+1 months' ));
    }

    public function getEventsNext() {
        return $this->getEvents(new DateTime('+1 month'), new DateTime( '+2 months' ));
    }
}