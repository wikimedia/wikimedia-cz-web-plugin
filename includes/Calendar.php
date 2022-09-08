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

        // add all mandatory tags
        $mandatoryTags = WmczConfiguration::singleton()->get( 'mandatoryTags' );
        foreach ( $mandatoryTags as $tag ) {
            $tags[] = WmczTag::newFromCode( $tag );
        }

        // add the special "other" tag
        $tags[] = new WmczTag( 'other', __( 'other', 'wmcz-plugin' ) );

        return array_values( array_unique( $tags ) );
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
     *
     * @param string|null $linkPrefix Link prefix if the places should have one; average location
     *                                name will be appended.
     */
    public function getPlaces($linkPrefix = null) {
        $places = [];
        $events = $this->getAllEventsInternal();
        foreach ( $events as $event ) {
            if ( !is_null( $event->location ) ) {
                $address = new Address( $event->location );
                $place = $address->getAveragePlace();
                if ( $place ) {
                    if ( $linkPrefix !== null ) {
                        $place->setLink( $linkPrefix . $place->address );
                    }

                    if (!in_array( $place, $places )) {
                        $places[] = $place;
                    }
                }
            }
        }
        return $places;
    }

    public function getEventsBatch(DateTime $from, DateTime $to) {
        return new Events(
            $this->ical->eventsFromRange(
                $from->format('Y-m-d'),
                $to->format('Y-m-d')
            ),
            $this->ical,
            $this->maxEvents
        );
    }

    /**
     * Returns events happening in certain time period
     *
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     */
    public function getEvents(DateTime $from, DateTime $to, $tags = null) {
        return $this->getEventsBatch( $from, $to, $tags )->getEvents( $tags );
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