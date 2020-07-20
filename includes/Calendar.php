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
     * Used in global functions like getAddresses or getTags
     */
    private function getAllEvents() {
        $events = new Events( $this->getAllEventsInternal(), $this->ical );
        return $events->getEvents();
    }

    private function getAllEventsInternal() {
        $from = new DateTime('-1 month');
        $to = new DateTime('+11 months');
        return $this->ical->eventsFromRange(
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );
    }

    /**
     * Get all tags used in events in this calendar
     */
    public function getTags() {
        $events = $this->getAllEvents();
        $tags = [];
        foreach ( $events as $event ) {
            $tags = array_merge( $tags, $event->getTags() );
        }
        return $tags;
    }

    /**
     * Gets all addresses used in events in this calendar
     *
     * @return array
     */
    public function getAddresses() {
        $places = [];
        $events = $this->getAllEventsInternal();
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
        $events = $this->getAllEventsInternal();
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
        $events = new Events(
            $this->ical->eventsFromRange(
                $from->format('Y-m-d'),
                $to->format('Y-m-d')
            ),
            $this->ical,
            $this->maxEvents
        );
        return $events->getEvents( $tags );
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