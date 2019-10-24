<?php

use ICal\ICal;

require_once 'place.php';

class WmczCalendar {
    protected $ical;
    protected $maxEvents;

   public function __construct($url, $maxEvents = null)
    {
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

    public function getPlaces() {
        return [
            new Place(50.03861, 15.77916)
        ];
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