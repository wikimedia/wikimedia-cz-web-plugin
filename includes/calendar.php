<?php

use ICal\ICal;

require_once 'place.php';

class WmczCalendar {
    protected $ical;
    protected $maxEvents;

    public function __construct($maxEvents, $url)
    {
        $this->ical = new ICal( $url );
        $this->maxEvents = $maxEvents;
    }


    protected function formatEvent($event) {
        $date = $this->ical->iCalDateToDateTime($event->dtstart_array[3]);
        $place = explode( ', ', $event->location );
        return [
            'datetime' => $date->format('d. m. Y H:i'),
            'place' => $place[0],
            'title' => $event->summary
        ];
    }

    public function getPlaces() {
        return [
            new Place(50.03861, 15.77916)
        ];
    }

    public function getEventsNow() {
        $res = [];
        $events = $this->ical->eventsFromInterval('1 month');

        for ($i=0; $i < $this->maxEvents; $i++) {
            $res[] = $this->formatEvent($events[$i]);
        }
        return $res;
    }

    public function getEventsNext() {
        return [];
    }
}