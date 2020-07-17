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
                esc_html( $event->getId() ),
                esc_html( $event->getStartDatetime() ),
                esc_html( $event->getEndDatetime() ),
                esc_html( $event->getDisplayDatetime() ),
                esc_html( $event->getLocation() ),
                esc_html( $event->getCity() ),
                esc_html( $event->getDescription() ),
                esc_html( $event->getTitle() )
            );
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;}

function wmcz_block_calendar_render_callback( $attributes ) {
    $cols = (int)$attributes['cols'];
    $rows = (int)$attributes['rows'];
    $calendar = new WmczCalendar($attributes['ical'], $cols*$rows);
    $tags = null;
    if ( $attributes['tag'] != null ) {
        $tags = [ $attributes['tag'] ];
    }
    $now = $calendar->getEventsNow( $tags );
    $next = $calendar->getEventsNext( $tags );
    $html = '';
    $html .= wmcz_block_render_calendar( $cols, $rows, $now, "this-month" );
    $html .= wmcz_block_render_calendar( $cols, $rows, $next, "next-month" );
    return '<div class="block-wmcz-calendar">
    <h2>kalendář akcí</h2>
    <div class="wmcz-calendar-controls">
        <button id="wmcz-calendar-control-this-month" class="wmcz-calendar-control active">tento měsíc</button>
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
    $selectedTags = null;
    if ( isset( $_GET['tags'] ) ) {
        $selectedTags = [];
        foreach ( $_GET['tags'] as $tag ) {
            $selectedTags[] = $tag;
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
    $ical = $attributes['ical'];

    // Construct calendar
    $calendar = new WmczCalendar( $ical );
    $tags = ['edu', 'glam']; // TODO unhardcode this

    // Construct tags
    if ( count( $tags ) > 0 ) {
        // $tagsHtml = '<span class="wmcz-events-select-tags">Vyberte tagy</span>';
        for ($i=0; $i < count($tags); $i++) {
            $class = strtolower( $tags[$i] );
            $selected = '';
            if ( is_array( $selectedTags ) && in_array( $i, $selectedTags ) ) {
                $selected = 'checked';
            }
            $tagsHtml .= '<span><input class="' . $class . '" type="checkbox" ' . $selected . ' name="tags[]" value="' . $tags[$i] . '" id="wmcz-events-tag-' . $i . '">';
            $tagsHtml .= '<label for="wmcz-events-tag-' . $i . '">' .$tags[$i] . '</label></span>';
        }
    }

    // Construct cities
    $placesHtml = '';
    $cities = [];
    foreach ($calendar->getAddresses() as $address) {
        if ( !in_array( $address->getCity(), $cities ) ) {
            $city = $address->getCity();
            $cities[] = $city;
            $selected = '';
            if ( in_array( $city, $selectedCities ) ) {
                $selected = 'checked';
            }
            $placesHtml .= '<span><input type="checkbox" name="cities[]" id="wmcz-city-' . $city . '" value="' . $city . '" ' . $selected . '>';
            $placesHtml .= '<label for="wmcz-city-' . $city . '">' . $city . '</label></span>';
        }
    }


    $events = $calendar->getEvents( new DateTime($from), new DateTime($to), $selectedTags );
    $eventsHtml = '<div class="wmcz-events-list-events">';
    foreach ( $events as $event ) {
        if ( count( $selectedCities ) > 0 && !in_array( $event->getCity(), $selectedCities ) ) {
            continue;
        }
        $tagClasses = [];
        foreach ( $event->getTags() as $tag ) {
            $tagClasses[] = "wmcz-events-tag-$tag";
        }
        $eventsHtml .= sprintf(
            '<div class="wmcz-events-list-event">
                <div class="wmcz-events-list-event-name %s">%s</div>
                <div class="wmcz-events-list-event-time">%s, %s</div>
                <div class="wmcz-events-list-event-description">%s</div>
            </div>',
            esc_attr( implode( ' ', $tagClasses ) ),
            esc_html( $event->getTitle() ),
            esc_html($event->getStartDatetime()),
            esc_html($event->getCity()),
            esc_html($event->getDescription())
        );
    }
    $eventsHtml .= '</div>';

    return '<div class="wmcz-events-list">
        <div class="wmcz-events-list-controls">
            <form>
                <b>Filtr</b>
                <label for="from">od</label>
                <input type="date" name="from" id="from" value="' . $from . '">
                <label for="to">do</label>
                <input type="date" name="to" id="to" value="' . $to . '">
                <div><label for="cities">dle programů</label>' . $tagsHtml . '
                </div><div><label for="cities">dle místa</label>' . $placesHtml . '
                </div><div>
                <input type="submit" value="Filtrovat" /></div>
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
    if ( $attributes['tag'] != '' && $attributes['tag'] != null ) {
        $args['tag'] = $attributes['tag'];
    }
    $maxNews = (int)$attributes['maxNews'];
    if ( $maxNews > 0 ) {
        $args['posts_per_page'] = $maxNews;
    }
    $q = new WP_Query( $args );
    $result = '<div class="wmcz-posts-container">';
    if ( $maxNews == 0 ) {
        $result .= '<div class="wmcz-posts-head">' . get_search_form( false ) .
        '<div class="wmcz-archive">
        <h3>Archive</h3>' .
        wp_custom_archive( '', false ) .
        '</div></div>';
    }
    $result .= '<div class="wmcz-posts">';
    while ( $q->have_posts() ) {
        $q->the_post();
        $result .= load_template_part( 'template-parts/content-snip', get_post_type() );
    }
    $result .= get_the_posts_navigation();
    $result .= '</div>';
    if ($q->found_posts > $maxNews && $maxNews != 0 && $attributes['tag'] != null && $attributes['tag'] != '') {
        $tag = get_term_by( 'slug', $attributes['tag'], 'post_tag' );
        $result .= '<a href="' . get_tag_link( $tag->term_id ) . '">Všechny novinky</a>';
    }
    $result .= '</div>';
    return $result;
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

function load_template_part($template_name, $part_name=null) {
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

function wp_custom_archive($args = '', $echo = true) {
    global $wpdb, $wp_locale;
 
    $defaults = array(
        'limit' => '',
        'format' => 'html', 'before' => '',
        'after' => '', 'show_post_count' => false,
        'echo' => 1
    );
 
    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
 
    if ( '' != $limit ) {
        $limit = absint($limit);
        $limit = ' LIMIT '.$limit;
    }
 
    // over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
    $archive_date_format_over_ride = 0;
 
    // options for daily archive (only if you over-ride the general date format)
    $archive_day_date_format = 'Y/m/d';
 
    // options for weekly archive (only if you over-ride the general date format)
    $archive_week_start_date_format = 'Y/m/d';
    $archive_week_end_date_format   = 'Y/m/d';
 
    if ( !$archive_date_format_over_ride ) {
        $archive_day_date_format = get_option('date_format');
        $archive_week_start_date_format = get_option('date_format');
        $archive_week_end_date_format = get_option('date_format');
    }
 
    //filters
    $where = apply_filters('customarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'", $r );
    $join = apply_filters('customarchives_join', "", $r);
 
    $output = '<ul>';
 
        $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC $limit";
        $key = md5($query);
        $cache = wp_cache_get( 'wp_custom_archive' , 'general');
        if ( !isset( $cache[ $key ] ) ) {
            $arcresults = $wpdb->get_results($query);
            $cache[ $key ] = $arcresults;
            wp_cache_set( 'wp_custom_archive', $cache, 'general' );
        } else {
            $arcresults = $cache[ $key ];
        }
        if ( $arcresults ) {
            $afterafter = $after;
            foreach ( (array) $arcresults as $arcresult ) {
                $url = get_month_link( $arcresult->year, $arcresult->month );
                /* translators: 1: month name, 2: 4-digit year */
                $text = sprintf(__('%s'), $wp_locale->get_month($arcresult->month));
                $year_text = sprintf('<li>%d</li>', $arcresult->year);
                if ( $show_post_count )
                    $after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
                $output .= ( $arcresult->year != $temp_year ) ? $year_text : '';
                $output .= get_archives_link($url, $text, $format, $before, $after);
 
                $temp_year = $arcresult->year;
            }
        }
 
    $output .= '</ul>';
 
    if ( $echo )
        echo $output;
    else
        return $output;
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
