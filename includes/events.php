<?php

class Events {
    private $events;
    private $maxEvents;

    public function __construct( array $events, $ical, $maxEvents = null ) {
        $this->maxEvents = $maxEvents;
        $this->events = [];
        foreach ( $events as $event ) {
            $this->events[] = new Event( $event, $ical );
        }
    }

    public function getEvents( $tags = null ) {
        if ( !is_array( $tags ) || ( is_array( $tags ) && count( $tags ) === 0 ) ) {
            return $this->events;
        }

        $res = [];
        $i = 0;
        foreach ( $this->events as $event ) {
            if ( $this->maxEvents !== null && $i >= $this->maxEvents ) {
                return $res;
            }
            foreach ( $tags as $tag ) {
                if ( in_array( $tag, $event->getTags() ) ) {
                    $res[] = $event;
                }
            }
        }
        return $res;
    }
}