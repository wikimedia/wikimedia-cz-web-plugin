<?php

class Events {
    private $events;

    public function __construct( array $events, $ical ) {
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
        foreach ( $this->events as $event ) {
            foreach ( $tags as $tag ) {
                if ( in_array( $tag, $event->getTags() ) ) {
                    $res[] = $event;
                }
            }
        }
        return $res;
    }
}