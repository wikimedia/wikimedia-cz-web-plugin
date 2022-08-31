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
        $startDate = (new DateTime())->setTimestamp( $ical->iCalDateToUnixTimestamp($event->dtstart_array[3]) );
        $startDate->setTimezone( new DateTimeZone( "Europe/Prague" ) );
        $endDate = (new DateTime())->setTimestamp( $ical->iCalDateToUnixTimestamp($event->dtend_array[3]) );
        $endDate->setTimezone( new DateTimeZone( "Europe/Prague" ) );

        $matches = null;
        preg_match('/^(\[([^]]+)\])?\s*(.*)$/', $event->summary, $matches);
        if ( $matches[2] === "" ) {
            $this->tags = [];
        } else {
            $this->tags = explode( ', ', $matches[2] );
        }
        $this->title = $matches[3];

        $this->displayDatetime = $startDate->format('d. m. Y');
        $this->startDatetime = $startDate->format('d. m. Y H:i');
        $this->endDatetime = $endDate->format('d. m. Y H:i');
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

    /**
     * Returns raw location
     *
     * @see Event::getAddress if you want to have machine-readable form of an address
     */
    public function getLocation() {
        return $this->location;
    }

    /**
     * Is this event online?
     *
     * @return bool
     */
    public function isOnline() {
        return strtolower( $this->getLocation() ) === "online";
    }

    /**
     * Return an instance of Address from location
     *
     * @return Address
     */
    public function getAddress() {
        return new Address( $this->getLocation() );
    }

    public function getCity() {
        if ( $this->city !== null ) {
            return $this->city;
        }

        $this->city = $this->getAddress()->getCity();

        return $this->city;
    }

    public function getTitle() {
        return $this->title;
    }

    /**
     * Return a link to the videocall, if any
     *
     * @return string|null
     */
    public function getVideocallLink() {
        $matches = null;
        preg_match_all( '/https:\/\/meet.google.com\/[a-z-]*/', $this->description, $matches );
        return $matches[0][0] ?? null;
    }

    /**
     * Get event description
     *
     * This comes from the event description set in
     * iCal. Google's addition about Google Meet link is removed,
     * and replaced with custom description.
     *
     * @return string
     */
    public function getDescription() {
        $matches = null;

        // remove Google-added stuff about Google Meet
        $description = preg_replace( '/Tato událost má videohovor..Připojit se:.*/s', '', $this->description );

        // if there is any videocall, mention how to connect
        $videoCallLink = $this->getVideocallLink();
        if ( $videoCallLink ) {
            $description .= 'Tato událost má videohovor. Připojit se: ' . $videoCallLink;
        }

        // wrap all links in <a>, to make them clickable
        preg_match_all( '/(^|\s+)(https?:\/\/[a-zA-Z.0-9\/%:_?&#=-]+)/', $description, $matches );
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
