<?php

class Event {
    private $displayDatetime;
    private $startDatetime;
    private $endDatetime;
    private $location;
    private $city;
    private $title;
    private $description;
    private $id;
    private $tags;

    public function __construct( $event, $ical ) {
        $startDate = $ical->iCalDateToDateTime($event->dtstart_array[3]);
        $endDate = $ical->iCalDateToDateTime($event->dtend_array[3]);

        $matches = null;
        preg_match('/^(\[([^]]+)\])?\s*(.*)$/', $event->summary, $matches);
        if ( $matches[2] === "" ) {
            $this->tags = [];
        } else {
            $this->tags = explode( ', ', $matches[2] );
        }
        $this->title = $matches[3];

        $this->displayDatetime = $startDate->format('d. m. Y');
        $this->startDatetime = $startDate->format('d. m. Y h:m');
        $this->endDatetime = $endDate->format('d. m. Y h:m');
        $this->location = $event->location;
        $this->description = $event->description;
        $this->id = hash('md5', $event->title . $event->dtstart . $event->dtend);
    }

    public function getDisplayDatetime() {
        return $this->displayDatetime;
    }

    public function getStartDatetime() {
        return $this->startDatetime;
    }

    public function getEndDatetime() {
        return $this->endDatetime;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getCity() {
        if ( $this->city !== null ) {
            return $this->city;
        }
        $matches = null;
        preg_match_all( '/[0-9 ]+ ([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+)/',  $this->getLocation(), $matches);
        $this->city = trim( end( end( $matches ) ) );
        if ( $this->city == "" ) {
            preg_match_all( '/[0-9 \/]+, ([0-9 ]* ?([a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ ]+))/', $this->getLocation(), $matches );
            $this->city = trim( end( end( $matches ) ) );
        }
        return $this->city;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        $matches = null;
        preg_match_all( '/(^|\s+)(https?:\/\/[a-zA-Z.0-9\/%:_?&#=-]+)/', $this->description, $matches );
        $description = $this->description;
        foreach ( $matches[0] as $match ) {
            $description = str_replace( $match, "<a href=\"$match\">$match</a>", $description );
        }
        return $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getTags() {
        return $this->tags;
    }
}