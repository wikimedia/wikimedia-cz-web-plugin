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
    private $tags = [];

    public function __construct( $event, $ical ) {
        $startDate = (new DateTime())->setTimestamp( $ical->iCalDateToUnixTimestamp($event->dtstart_array[3]) );
        $startDate->setTimezone( new DateTimeZone( "Europe/Prague" ) );
        $endDate = (new DateTime())->setTimestamp( $ical->iCalDateToUnixTimestamp($event->dtend_array[3]) );
        $endDate->setTimezone( new DateTimeZone( "Europe/Prague" ) );

        $matches = null;
        preg_match('/^(\[([^]]+)\])?\s*(.*)$/', $event->summary, $matches);

        if ( $matches[2] !== '' ) {
            $this->tags = array_map( static function( $tagCode ) {
                return WmczTag::newFromCode( $tagCode );
            }, explode( ', ', $matches[2] ) );
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

    /**
     * Get city the event is in
     *
     * @return string
     */
    public function getCity() {
        if ( $this->city !== null ) {
            return $this->city;
        }

        $this->city = $this->getAddress()->getCity();

        return $this->city;
    }

    /**
     * Get event title
     *
     * @return string
     */
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

        // decode HTML and reove <html-blob> wrapper
        $description = html_entity_decode( $this->description );
        $description = preg_replace( '/<html-blob ?\/?>/', '', $description );

        // remove Google-added stuff about Google Meet
        $description = preg_replace( '/(Tato událost má videohovor..)?Připojit se: https:\/\/meet.google.com.*/s', '', $description );

        // turn <br> into paragraphs
        if ( $description !== '' ) {
            $description = '<p> ' . preg_replace( '/(<br ?\/?>){2}/', ' <p/> <p> ', $description ) . ' </p>';
        }

        // if there is any videocall, mention how to connect
        $videoCallLink = $this->getVideocallLink();
        if ( $videoCallLink ) {
            $description .= '<p>Tato událost má videohovor. Připojit se: ' . $videoCallLink . '</p>';
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

    /**
     * Tags added to the event
     *
     * @return WmczTag[]
     */
    public function getTags() {
        return $this->tags;
    }
}
