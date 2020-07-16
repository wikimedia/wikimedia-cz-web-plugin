<?php

use ICal\ICal;
use maxh\Nominatim\Nominatim;

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
                    'city' => trim( $city ),
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
     * Gets all addresses used in events in this calendar
     *
     * @return array
     */
    public function getAddresses() {
        $places = [];
        $from = new DateTime('-1 month');
        $to = new DateTime('+11 months');
        $events = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        foreach ( $events as $event ) {
            if ( !is_null( $event->location ) ) {
                $address = new Address( $event->location );
                if ( $address->getPlace() ) {
                    $places[] = $address;
                }
            }
        }
        return $places;
    }

    /**
     * Gets all places used in events in this calendar
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
                $address = new Address( $event->location );
                if ( $address->getPlace() ) {
                    $places[] = $address->getPlace();
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
    public function getEvents(DateTime $from, DateTime $to, $tags = null) {
        $eventsOriginal = $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
        if ( is_array( $tags ) && count( $tags ) > 0 ) {
            $events = [];
            foreach ($eventsOriginal as $event) {
                $matches = null;
                preg_match( '/^(\[([^]]+)\])?\s*(.*)$/', $event->summary, $matches );
                $tag = $matches[2];
                if ( in_array( $tag, $tags ) ) {
                    $events[] = $event;
                }
            }
        } else {
            $events = $eventsOriginal;
        }
        return $this->formatEvents( $events );
    }

    /**
     * Returns events happening in upcoming month
     *
     * @return array
     */
    public function getEventsNow( $tags = null ) {
        return $this->getEvents(new DateTime(), new DateTime( '+1 months' ), $tags);
    }

    /**
     * Returns events happening in next month
     *
     * @return array
     */
    public function getEventsNext( $tags = null ) {
        return $this->getEvents(new DateTime('+1 month'), new DateTime( '+2 months' ), $tags);
    }
}