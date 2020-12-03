<?php
/**
* Plugin Name: WMCZ website addon
* Plugin URI: https://wikimedia.cz
* Text Domain: wmcz-plugin
* Description: Customizations for WMCZ
* Version: 0.1
* Author: Martin Urbanec
* Author URI: https://meta.wikimedia.org/wiki/User:Martin_Urbanec
**/

require_once 'autoload.php';

function wmcz_block_render_calendar( $cols, $rows, $events, $class ) {
    $html = '<div id="wmcz-calendar-' . $class . '" class="wmcz-calendar-set wp-block-columns has-' . $cols . '-columns">';
    for ($i=0; $i < $cols; $i++) { 
        $html .= '<div class="wp-block-column">';
        $sliced = array_slice($events, $i*$rows, $rows);
        foreach ($sliced as $event) {
            $tagClassesArray = [];
            foreach ( $event->getTags() as $tag ) {
                $tagClassesArray[] = "wmcz-tag-$tag";
            }
            $tagClasses = implode(' ', $tagClassesArray);
            $html .= sprintf(
                '<div data-event-id="%s" class="event-container">
                    <p class="event-datetime %s" data-start-datetime="%s" data-end-datetime="%s">%s</p>
                    <p class="event-location" data-location="%s">%s</p>
                    <p class="event-title" data-description="%s">%s</p>
                </div>',
                esc_html( $event->getId() ),
                esc_html( $tagClasses ),
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
        <button id="wmcz-calendar-control-this-month" class="wmcz-calendar-control active">' . __('this month', 'wmcz-plugin') . '</button>
        <button id="wmcz-calendar-control-next-month" class="wmcz-calendar-control">' . __('next month', 'wmcz-plugin') . '</button>
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
    return '<div class="wmcz-map-container" data-id="' . $id . '">
    <div class="wmcz-map" data-id="' . $id . '" id="map-' . $id . '"></div><script type="text/javascript">
        console.log("map id: ' . $id . '");
        wmczInitMap("' . $id . '", "' . $attributes['ical'] . '", ' . var_export( (bool)$attributes['gesturehandling'], true ) . ', {
            lat: ' . (float)$attributes['lat'] . ',
            lon: ' . (float)$attributes['lon'] . ',
            zoom: ' . (int)$attributes['zoom'] . ',
        });
    </script>';
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
    $events = $wpdb->get_results( "SELECT id, name, description, photo_id, link FROM {$wpdb->prefix}wmcz_caurosel WHERE published=1 ORDER BY ordering_key DESC, added DESC", OBJECT );

    $html = '<div class="wmcz-caurosel-container" id="wmcz-caurosel-container-' . $id . '">' . "\n";
    foreach ( $events as $event ) {
        $headlineInnerHtml = $event->name;
        if ( $event->link != '' ) {
            $headlineInnerHtml = '<a href="' . $event->link . '">' . $event->name . '</a>';
        }
        $readMore = '';
        if ( $event->link !== '' ) {
            $readMore = ' <a href="' . $event->link . '">' . __( 'Read more...', 'wmcz-plugin' ) . '</a>';
        }
        $html .= '
            <div class="gallery-entry">
                <div class="wmcz-caurosel-left">
                    <a href="' . $event->link . '">
                        <img src="' .  wp_get_attachment_url( $event->photo_id ) . '" alt="">
                    </a>
                </div>
                <div class="wmcz-caurosel-right-colored">
                    <h2>' . $headlineInnerHtml . '</h2>' .'
                    <p>
                        ' . $event->description . $readMore .'
                    </p>
                </div>
            </div>
        ';
    }
    $html .= '</div>
    <script>
        var flkty = new Flickity( "#wmcz-caurosel-container-' . $id . '", {
            // options
            cellAlign: "left",
            autoPlay: true,
            contain: true
        });
    </script>';
    return $html;

    $numOfEvents = $wpdb->num_rows;
    $headline = esc_html( $events[0]->name );
    $description = esc_html( $events[0]->description );
    $link = $events[0]->link;
    $hasLink = $link != '';
    $headlines = [];
    $descriptions = [];
    $images = [];
    $links = [];
    foreach ( $events as $event ) {
        $headlines[] = esc_html( $event->name );
        $descriptions[] = esc_html( $event->description );
        $images[] =  wp_get_attachment_url( $event->photo_id );
        $links[] = esc_html( $event->link );
    }
    $headlinesJson = json_encode( $headlines );
    $descriptionsJson = json_encode( $descriptions );
    $imagesJson = json_encode( $images );
    $linksJson = json_encode( $links );
    $dataAttrs = "data-index='0' data-headlines='$headlinesJson' data-descriptions='$descriptionsJson' data-images='$imagesJson' data-links='$linksJson'";
    $menu = '<div data-caurosel-id="' . $id . '" class="wmcz-caurosel-menu"><ul>';
    for ($i = 0; $i < $numOfEvents; $i++) {
        $classes = "wmcz-caurosel-menu-dot";
        if ( $i == 0 ) {
            $classes .= " wmcz-caurosel-menu-dot-active";
        }
        $menu .= '<li><div data-caurosel-id="' . $id . '" data-index="' . $i . '" class="' . $classes . '"></div></li>';
    }
    $menu .= '</ul></div>';
    if (!$hasLink) {
        $link = "#"; // Empty href should be a noop-link
    }
    $headlineInnerHtml = $headline;
    if ( $hasLink ) {
        $headlineInnerHtml = '<a href="' . $link . '">' . $headline . '</a>';
    }
    $html = '
    <div data-caurosel-id="' . $id . '" class="wmcz-caurosel-container">
        <div data-caurosel-id="' . $id . '" class="wmcz-caurosel-left">
            <a data-caurosel-id="' . $id . '" href="' . $link . '">
                <img src="' .  wp_get_attachment_url( $events[0]->photo_id ) . '" alt="">
            </a>
        </div>
        <div data-caurosel-id="' . $id . '" ' . $dataAttrs . ' class="wmcz-caurosel-right-colored">
            ' . $menu . '
            <h2>' . $headlineInnerHtml . '</h2>
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
    $eventsBatch = $calendar->getEventsBatch( new DateTime($from), new DateTime($to), $selectedTags );
    $tags = $calendar->getTags();

    // Construct tags
    if ( count( $tags ) > 0 ) {
        $tagsHtml = '';
        for ($i=0; $i < count($tags); $i++) {
            $class = strtolower( $tags[$i] );
            $selected = '';
            if ( is_array( $selectedTags ) && in_array( $tags[$i], $selectedTags, true ) ) {
                $selected = 'checked';
            }
            $tagsHtml .= '<span><input class="' . $class . '" type="checkbox" ' . $selected . ' name="tags[]" value="' . $tags[$i] . '" id="wmcz-events-tag-' . $i . '">';
            $tagsHtml .= '<label for="wmcz-events-tag-' . $i . '">' .$tags[$i] . '</label></span>';
        }
        $i = count($tags);
        $selected = '';
        if ( is_array( $selectedTags ) && in_array( 'other', $selectedTags, true ) ) {
            $selected = 'checked';
        }
        $tagsHtml .= '<span><input type="checkbox" ' . $selected . ' name="tags[]" value="other" id="wmcz-events-tag-' . $i . '">';
        $tagsHtml .= '<label for="wmcz-events-tag-' . $i . '">' . __( 'other', 'wmcz-plugin' ). '</label></span>';
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

    // Hack for online events
    if ( $eventsBatch->hasOnline() ) {
        $cities[] = 'online';
        $selected = '';
        if ( in_array( 'online', $selectedCities ) ) {
            $selected = 'checked';
        }
        $placesHtml .= '<span><input type="checkbox" name="cities[]" id="wmcz-city-online" value="online" ' . $selected . '>';
        $placesHtml .= '<label for="wmcz-city-online">' . __( 'Online', 'wmcz-plugin' ) . '</label></span>';
    }


    $events = $eventsBatch->getEvents();
    $eventsHtml = '<div class="wmcz-events-list-events">';
    $letOnline = in_array( 'online', $selectedCities );
    foreach ( $events as $event ) {
        if (
            count( $selectedCities ) > 0 &&
            !(
                in_array( $event->getCity(), $selectedCities ) ||
                (
                    $letOnline && $event->isOnline()
                )
            )
        ) {
            continue;
        }
        $tagClasses = [];
        foreach ( $event->getTags() as $tag ) {
            $tagClasses[] = "wmcz-tag-$tag";
        }
        $eventsHtml .= sprintf(
            '<div class="wmcz-events-list-event">
                <div class="wmcz-events-list-event-name %s">%s</div>
                <div class="wmcz-events-list-event-time">%s, %s</div>
                <div class="wmcz-events-list-event-description">
                    <div class="wmcz-events-list-event-summary">
                        <ul>
                            <li>' . __('Start', 'wmcz-plugin') . ': %s</li>
                            <li>' . __('End', 'wmcz-plugin') . ': %s</li>
                            <li>' . __('Location', 'wmcz-plugin') . ': %s</li>
                        </ul>
                    </div>
                    <div class="wmcz-events-event-description-raw">
                        %s
                    </div>
                </div>
            </div>',
            esc_attr( implode( ' ', $tagClasses ) ),
            esc_html( $event->getTitle() ),
            esc_html($event->getStartDatetime()),
            esc_html($event->getCity()),
            esc_html( $event->getStartDatetime() ),
            esc_html( $event->getEndDatetime() ),
            esc_html( $event->getLocation() ),
            html_entity_decode( $event->getDescription() )
        );
    }
    $eventsHtml .= '</div>';

    return '<div class="wmcz-events-list">
        <div class="wmcz-events-list-controls">
            <form>
                <strong>' . __('Search for events', 'wmcz-plugin') . '</strong>
                <label for="from">' . __('from', 'wmcz-plugin') . '</label>
                <input type="date" name="from" id="from" value="' . $from . '">
                <label for="to">' . __('to', 'wmcz-plugin') . '</label>
                <input type="date" name="to" id="to" value="' . $to . '">
                <div><label for="cities">' . __('by program', 'wmcz-plugin') . '</label>' . $tagsHtml . '
                </div><div><label for="cities">' . __('by location', 'wmcz-plugin') . '</label>' . $placesHtml . '
                </div><div>
                <input type="submit" value="' . __('Filter', 'wmcz-plugin') . '" /></div>
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
    global $post;
    return '… <a href="'. get_permalink($post->ID) . '">' . 'Číst více &raquo;' . '</a>';
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
        <h3>' . __('Archive', 'wmcz-plugin') .  '</h3>' .
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
        link tinytext NOT NULL DEFAULT '',
        published boolean DEFAULT 0 NOT NULL,
        ordering_key mediumint(9) NOT NULL DEFAULT 0,
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

function wmcz_register_i18n() {
    $rel_path = basename( dirname( __FILE__ ) ) . '/i18n';
    load_plugin_textdomain( 'wmcz-plugin', false, $rel_path );

}

add_action( 'init', 'wmcz_block_calendar_register' );
add_action( 'init', 'wmcz_block_map_register' );
add_action( 'init', 'wmcz_block_events_caurosel_register' );
add_action( 'init', 'wmcz_block_calendar_list_register' );
add_action( 'init', 'wmcz_block_donate_register' );
add_action( 'init', 'wmcz_block_latest_news_register' );
add_action( 'plugins_loaded', 'wmcz_register_i18n' );
add_filter('excerpt_more', 'wmcz_excerpt_more');
add_filter('excerpt_length', 'wmcz_custom_excerpt_length');
register_activation_hook( __FILE__, 'wmcz_install' );

if (!is_admin()) {
    wp_enqueue_script('leaflet', plugins_url( 'static/vendor/leaflet/dist/leaflet.js', __FILE__ ) );
    wp_enqueue_style('leaflet', plugins_url( 'static/vendor/leaflet/dist/leaflet.css', __FILE__ ) );
    wp_enqueue_script( 'leaflet.gesturehandling', plugins_url( 'static/vendor/leaflet.gesturehandling/dist/leaflet-gesture-handling.min.js', __FILE__ ) );
    wp_enqueue_style( 'leaflet.gesturehandling', plugins_url( 'static/vendor/leaflet.gesturehandling/dist/leaflet-gesture-handling.min.css', __FILE__ ) );
    wp_enqueue_style('wmcz-plugin', plugins_url( 'static/stylesheet.css', __FILE__ ) );
    wp_enqueue_script('wmcz-plugin-map', plugins_url( 'static/map.js', __FILE__ ) );
    wp_localize_script( 'wmcz-plugin-map', 'jsVars', [
        'api' => plugins_url( 'api.php', __FILE__ )
    ] );
    wp_enqueue_script('wmcz-plugin-calendar', plugins_url( 'static/calendar.js', __FILE__ ) );
    wp_enqueue_style( 'wmcz-plugin-caurosel-flickity', plugins_url( 'static/vendor/flickity/flickity.css', __FILE__ ) );
    wp_enqueue_script( 'wmcz-plugin-caurosel-flickity', plugins_url( 'static/vendor/flickity/flickity.pkgd.min.js', __FILE__ ) );
}
