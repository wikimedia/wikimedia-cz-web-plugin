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
        $events = new Events(
            $this->ical->eventsFromRange(
                $from->format('Y-m-d'),
                $to->format('Y-m-d')
            ),
            $this->ical
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