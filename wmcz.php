<?php
/**
* Plugin Name: Wikimedia Czech Republic
* Plugin URI: https://wikimedia.cz
* Description: Customizations for WMCZ
* Version: 0.1
* Author: Martin Urbanec
* Author URI: https://meta.wikimedia.org/wiki/User:Martin_Urbanec
**/

spl_autoload_register(function ($class) {
    $filename = str_replace( 'wmcz', '', str_replace( 'Wmcz', '', $class ) );
    $path = dirname( __FILE__ ) . '/includes/' . $filename . '.php';

    if ( file_exists( $path ) ) {
        include $path;
        return true;
    } else {
        $filename = str_replace( 'wmcz', '', strtolower($class) );
        $path = dirname( __FILE__ ) . '/includes/' . $filename . '.php';

        if ( file_exists( $path ) ) {
            include $path;
            return true;
        }
    }

    return false;
});
require_once 'vendor/autoload.php';

function wmcz_block_render_calendar( $cols, $rows, $events, $class ) {
    $html = '<div id="wmcz-calendar-' . $class . '" class="wmcz-calendar-set wp-block-columns has-' . $cols . '-columns">';
    for ($i=0; $i < $cols; $i++) { 
        $html .= '<div class="wp-block-column">';
        $sliced = array_slice($events, $i*$rows, $rows);
        foreach ($sliced as $event) {
            $html .= sprintf(
                '<div data-event-id="%s" class="event-container">
                    <p class="event-datetime" data-start-datetime="%s" data-end-datetime="%s">%s</p>
                    <p class="event-location" data-location="%s">%s</p>
                    <p class="event-title" data-description="%s">%s</p>
                </div>',
                esc_html( $event['id'] ),
                esc_html( $event['startDatetime'] ),
                esc_html( $event['endDatetime'] ),
                esc_html( $event['displayDatetime'] ),
                esc_html( $event['location'] ),
                esc_html( $event['city'] ),
                esc_html( $event['description'] ),
                esc_html( $event['title'] )
            );
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function wmcz_block_calendar_render_callback( $attributes ) {
    $cols = (int)$attributes['cols'];
    $rows = (int)$attributes['rows'];
    $calendar = new WmczCalendar($attributes['ical'], $cols*$rows);
    $now = $calendar->getEventsNow();
    $next = $calendar->getEventsNext();
    $html = '';
    $html .= wmcz_block_render_calendar( $cols, $rows, $now, "this-month" );
    $html .= wmcz_block_render_calendar( $cols, $rows, $next, "next-month" );
    return '<div class="block-wmcz-calendar">
    <h2>kalendář akcí</h2>
    <div class="wmcz-calendar-controls">
        <button id="wmcz-calendar-control-this-month" class="wmcz-calendar-control">tento měsíc</button>
        <button id="wmcz-calendar-control-next-month" class="wmcz-calendar-control">příští měsíc</button>
    </div>
    ' . $html . '</div>';
}

function wmcz_block_calendar_register() {
    wp_register_script(
        'wmcz-calendar',
        plugin_dir_url(__FILE__) . 'blocks/calendar.js',
        array( 'wp-blocks', 'wp-element', 'wp-data' )
    );

    register_block_type( 'wmcz/calendar', array(
        'editor_script' => 'wmcz-calendar',
        'render_callback' => 'wmcz_block_calendar_render_callback'
    ) );
}

function wmcz_block_map_render_callback( $attributes ) {
    $id = uniqid();
    $calendar = new WmczCalendar( $attributes['ical'] );
    $data = [
        'points' => $calendar->getPlaces(),
        'defaults' => [
            'lat' => (float)$attributes['lat'],
            'lon' => (float)$attributes['lon'],
            'zoom' => (int)$attributes['zoom'],
        ]
    ];
    return '<div class="wmcz-map-container" data-id="' . $id . '">
    <div class="wmcz-map-data" data-id="' . $id . '">' . esc_html( json_encode( $data ) ) . '</div>
    <div class="wmcz-map" data-id="' . $id . '" id="map-' . $id . '"></div>';
}

function wmcz_block_map_register() {
    wp_register_script(
        'wmcz-map',
        plugin_dir_url(__FILE__) . 'blocks/map.js',
        array( 'wp-blocks', 'wp-element', 'wp-data' )
    );

    register_block_type( 'wmcz/map', array(
        'editor_script' => 'wmcz-map',
        'render_callback' => 'wmcz_block_map_render_callback'
    ) );
}

function wmcz_block_events_caurosel_render_callback( $attributes ) {
    global $wpdb;

    $id = uniqid();
    $events = $wpdb->get_results( "SELECT id, name, description, photo_id FROM {$wpdb->prefix}wmcz_caurosel WHERE published=1 ORDER BY added DESC", OBJECT );
    $numOfEvents = $wpdb->num_rows;
    $headline = esc_html( $events[0]->name );
    $description = esc_html( $events[0]->description );
    $headlines = [];
    $descriptions = [];
    $images = [];
    foreach ( $events as $event ) {
        $headlines[] = esc_html( $event->name );
        $descriptions[] = esc_html( $event->description );
        $images[] =  wp_get_attachment_url( $event->photo_id );
    }
    $headlinesJson = json_encode( $headlines );
    $descriptionsJson = json_encode( $descriptions );
    $imagesJson = json_encode( $images );
    $dataAttrs = "data-index='0' data-headlines='$headlinesJson' data-descriptions='$descriptionsJson' data-images='$imagesJson'";
    $menu = '<div data-caurosel-id="' . $id . '" class="wmcz-caurosel-menu"><ul>';
    for ($i = 0; $i < $numOfEvents; $i++) {
        $classes = "wmcz-caurosel-menu-dot";
        if ( $i == 0 ) {
            $classes .= " wmcz-caurosel-menu-dot-active";
        }
        $menu .= '<li><div data-caurosel-id="' . $id . '" data-index="' . $i . '" class="' . $classes . '"></div></li>';
    }
    $menu .= '</ul></div>';
    $html = '
    <div data-caurosel-id="' . $id . '" class="wmcz-caurosel-container">
        <div data-caurosel-id="' . $id . '" class="wmcz-caurosel-left">
            <img src="' .  wp_get_attachment_url( $events[0]->photo_id ) . '" alt="">
        </div>
        <div data-caurosel-id="' . $id . '" ' . $dataAttrs . ' class="wmcz-caurosel-right-colored">
            ' . $menu . '
            <h2>' . $headline . '</h2>
            <p>' . $description . '</p>
        </div>
    </div>';
    return $html;
}

function wmcz_block_events_caurosel_register() {
    wp_register_script(
        'wmcz-events-caurosel',
        plugin_dir_url(__FILE__) . 'blocks/events-caurosel.js',
        array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' )
    );
    register_block_type( 'wmcz/events-caurosel', [
        'editor_script' => 'wmcz-events-caurosel',
        'render_callback' => 'wmcz_block_events_caurosel_render_callback'
    ] );
}


function wmcz_block_calendar_list_render_callback( $attributes ) {
    // Parse GET params
    $from = filter_input( INPUT_GET, 'from', FILTER_SANITIZE_SPECIAL_CHARS );
    $to = filter_input( INPUT_GET, 'to', FILTER_SANITIZE_SPECIAL_CHARS );
    // TODO: Rewrite to something...more PHPy?
    $tags = [];
    if ( isset( $_GET['tags'] ) ) {
        foreach ( $_GET['tags'] as $tag ) {
            $filtered = filter_var( $tag, FILTER_VALIDATE_INT );
            if ( $filtered !== false ) {
                $tags[] = $filtered;
            }
        }
    }
    $selectedCities = [];
    if ( isset( $_GET['cities'] ) ) {
        foreach ($_GET['cities'] as $place ) {
            $selectedCities[] = $place;
        }
    }
    if ($from == null) {
        $tmp = new DateTime();
        $from = $tmp->format('Y-m-d');
    }
    if ($to == null) {
        $tmp = new DateTime('+1 month');
        $to = $tmp->format('Y-m-d');
    }

    // Parse Gutenberg attributes
    $icals = json_decode( $attributes['icals'] );
    if( $icals === null ) {
        return;
    }

    // Construct calendar
    $calendars = new WmczCalendars( $icals->urls );
    $calendar = $calendars->getCalendar( $tags );

    // Construct tags
    if ( count( $icals->names ) > 1 ) {
        $tagsHtml = '<span class="wmcz-events-select-tags">Vyberte tagy</span>';
        for ($i=0; $i < count($icals->names); $i++) {
            $class = strtolower( $icals->names[$i] );
            $selected = '';
            if ( in_array( $i, $tags ) ) {
                $selected = 'checked';
            }
            $tagsHtml .= '<input class="' . $class . '" type="checkbox" ' . $selected . ' name="tags[]" value="' . $i . '" id="wmcz-events-tag-' . $i . '">';
            $tagsHtml .= '<label for="wmcz-events-tag-' . $i . '">' . $icals->names[$i] . '</label>';
        }
    }

    // Construct cities
    $placesHtml = '';
    $cities = [];
    foreach ($calendar->getAddresses() as $address) {
        if ( !in_array( $address->getCity(), $cities ) ) {
            $city = $address->getCity();
            $cities[] = $city;
            $selected = '<span class="wmcz-events-select-cities">Vyberte města</span>';
            if ( in_array( $city, $selectedCities ) ) {
                $selected = 'checked';
            }
            $placesHtml .= '<input type="checkbox" name="cities[]" id="wmcz-city-' . $city . '" value="' . $city . '" ' . $selected . '>';
            $placesHtml .= '<label for="wmcz-city-' . $city . '">' . $city . '</label>';
        }
    }


    $events = $calendar->getEvents( new DateTime($from), new DateTime($to) );
    $eventsHtml = '<div class="wmcz-events-list-events">';
    foreach ( $events as $event ) {
        if ( count( $selectedCities ) > 0 && !in_array( $event['city'], $selectedCities ) ) {
            continue;
        }
        $tagClasses = [];
        foreach ( $event['tags'] as $tag ) {
            $tagClasses[] = "wmcz-events-tag-$tag";
        }
        $eventsHtml .= sprintf(
            '<div class="wmcz-events-list-event-name %s">%s</div>
            <div class="wmcz-events-list-event-time">%s, %s</div>
            <div class="wmcz-events-list-event-description">%s</div>',
            esc_attr( implode( ' ', $tagClasses ) ),
            esc_html( $event['title'] ),
            esc_html($event['startDatetime']),
            esc_html($event['city']),
            esc_html($event['description'])
        );
    }
    $eventsHtml .= '</div>';

    return '<div class="wmcz-events-list">
        <div class="wmcz-events-list-controls">
            <form>
                <label for="from">From</label>
                <input type="date" name="from" id="from" value="' . $from . '">
                <label for="to">To</label>
                <input type="date" name="to" id="to" value="' . $to . '">
                ' . $tagsHtml . '
                ' . $placesHtml . '
                <input type="submit" value="Odeslat" />
            </form>
        </div>
        ' . $eventsHtml . '
    </div>';
}

function wmcz_block_calendar_list_register() {
    wp_register_script(
        'wmcz-calendar-list',
        plugin_dir_url(__FILE__) . 'blocks/calendar-list.js',
        array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' )
    );
    register_block_type( 'wmcz/calendar-list', [
        'editor_script' => 'wmcz-calendar-list',
        'render_callback' => 'wmcz_block_calendar_list_render_callback'
    ] );
}

function wmcz_block_donate_render_callback() {
    return '<div class="wmcz-donate" data-darujme-widget-token="whwzii6mzx8ks66t">&nbsp;</div>
    <script type="text/javascript">
    +function(w, d, s, u, a, b) {
    w["DarujmeObject"] = u;
    w[u] = w[u] || function () { (w[u].q = w[u].q || []).push(arguments) };
    a = d.createElement(s); b = d.getElementsByTagName(s)[0];
    a.async = 1; a.src = "https:\/\/www.darujme.cz\/assets\/scripts\/widget.js";
    b.parentNode.insertBefore(a, b);
    }(window, document, "script", "Darujme");
    Darujme(1, "whwzii6mzx8ks66t", "render", "https:\/\/www.darujme.cz\/widget?token=whwzii6mzx8ks66t", "270px");
    </script>';
}

function wmcz_block_donate_register() {
    wp_register_script(
        'wmcz-donate',
        plugin_dir_url( __FILE__ ) . 'blocks/donate.js',
        [ 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' ]
    );
    register_block_type( 'wmcz/donate', [
        'editor_script' => 'wmcz-donate',
        'render_callback' => 'wmcz_block_donate_render_callback'
    ] );
}

function wmcz_excerpt_more() {
    return '…';
}

function wmcz_block_latest_news_register() {
    wp_register_script(
        'wmcz-latest-news',
        plugin_dir_url( __FILE__ ) . 'blocks/latest-news.js',
        [ 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' ]
    );
    register_block_type( 'wmcz/latest-news', [
        'editor_script' => 'wmcz-latest-news',
        'render_callback' => 'wmcz_block_latest_news_render_callback'
    ] );
}

function wmcz_block_latest_news_render_callback( $attributes ) {
    if ( is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false ) {
        return '';
    }

    $args = [];
    if ( $attributes['tag'] != '' ) {
        $args['tag'] = $attributes['tag'];
    }
    $q = new WP_Query( $args );
    echo '<div class="wmcz-posts-container">
        <div class="wmcz-posts-head">';
    get_search_form();
    echo '</div><div class="wmcz-posts">';
    while ( $q->have_posts() ) {
        $q->the_post();
        get_template_part( 'template-parts/content-snip', get_post_type() );
    }
    the_posts_navigation();
    echo '</div></div>';
    return '';
}

global $wmcz_db_version;
$wmcz_db_version = 1;

function wmcz_install() {
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . "wmcz_caurosel";
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        added datetime DEFAULT current_timestamp NOT NULL,
        name tinytext NOT NULL,
        description text NOT NULL,
        photo_id mediumint(9) NOT NULL,
        published boolean DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";
    dbDelta( $sql );
}

if ( is_admin() ) {
    require_once 'wmcz_admin.php';
}

function wmcz_custom_excerpt_length() {
    return 10;
}

add_action( 'init', 'wmcz_block_calendar_register' );
add_action( 'init', 'wmcz_block_map_register' );
add_action( 'init', 'wmcz_block_events_caurosel_register' );
add_action( 'init', 'wmcz_block_calendar_list_register' );
add_action( 'init', 'wmcz_block_donate_register' );
add_action( 'init', 'wmcz_block_latest_news_register' );
add_filter('excerpt_more', 'wmcz_excerpt_more');
add_filter('excerpt_length', 'wmcz_custom_excerpt_length');
register_activation_hook( __FILE__, 'wmcz_install' );

if (!is_admin()) {
    wp_enqueue_script('leaflet', plugins_url( 'static/leaflet/dist/leaflet.js', __FILE__ ) );
    wp_enqueue_style('leaflet', plugins_url( 'static/leaflet/dist/leaflet.css', __FILE__ ) );
    wp_enqueue_style('wmcz-plugin', plugins_url( 'static/stylesheet.css', __FILE__ ) );
    wp_enqueue_script('wmcz-plugin-map', plugins_url( 'static/map.js', __FILE__ ) );
    wp_enqueue_script('wmcz-plugin-calendar', plugins_url( 'static/calendar.js', __FILE__ ) );
    wp_enqueue_script('wmcz-plugin-caurosel', plugins_url( 'static/caurosel.js', __FILE__ ) );
}
