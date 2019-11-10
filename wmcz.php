<?php
/**
* Plugin Name: Wikimedia Czech Republic
* Plugin URI: https://wikimedia.cz
* Description: Customizations for WMCZ
* Version: 0.1
* Author: Martin Urbanec
* Author URI: https://meta.wikimedia.org/wiki/User:Martin_Urbanec
**/

require_once 'vendor/autoload.php';
require_once 'includes/calendar.php';

function wmcz_block_render_calendar( $cols, $rows, $events, $class ) {
	$html = '<div id="wmcz-calendar-' . $class . '" class="wmcz-calendar-set wp-block-columns has-' . $cols*2 . '-columns">';
	for ($i=0; $i < $cols; $i++) { 
		$html .= '<div class="wp-block-column">';
		$sliced = array_slice($events, $i*$rows, $rows);
		foreach ($sliced as $event) {
			$html .= sprintf(
				'<div class="event-container event-place-datetime">
					<p class="event-datetime">%s</p>
					<p class="event-place">%s</p>
				</div>',
				esc_html( $event['datetime'] ),
				esc_html( $event['place'] )
			);
		}
		$html .= '</div>';

		$html .= '<div class="wp-block-column">';
		foreach ($sliced as $event) {
			$html .= sprintf(
				'<div class="event-container">
					<p class="event-title">%s</p>
				</div>',
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

function wmcz_escape_array( $ar ) {
	$res = [];
	foreach ($ar as $value ) {
		$res[] = esc_html( $value );
	}
	return $res;
}

function wmcz_block_caurosel_render_callback( $attributes ) {
	$id = uniqid();
	$headline = esc_html( $attributes['headline'][0] );
	$description = esc_html( $attributes['description'][0] );
	$headlinesJson = json_encode( wmcz_escape_array( $attributes['headline'] ) );
	$descriptionsJson = json_encode( wmcz_escape_array( $attributes['description'] ) );
	$dataAttrs = "data-index='0' data-headlines='$headlinesJson' data-descriptions='$descriptionsJson'";
	$menu = '<div data-caurosel-id="' . $id . '" class="wmcz-caurosel-menu"><ul>';
	for ($i = 0; $i < count( $attributes['headline'] ); $i++) {
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
			<img src="https://upload.wikimedia.org/wikipedia/commons/8/84/Example.svg" alt="">
		</div>
		<div data-caurosel-id="' . $id . '" ' . $dataAttrs . ' class="wmcz-caurosel-right-colored">
			' . $menu . '
			<h2>' . $headline . '</h2>
			<p>' . $description . '</p>
		</div>
	</div>';
	return $html;
}

function wmcz_block_caurosel_register() {
	wp_register_script(
		'wmcz-caurosel',
		plugin_dir_url(__FILE__) . 'blocks/caurosel.js',
		array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' )
	);
	register_block_type( 'wmcz/caurosel', [
		'editor_script' => 'wmcz-caurosel',
		'render_callback' => 'wmcz_block_caurosel_render_callback'
	] );
}

function wmcz_block_news_register() {
	wp_register_script(
		'wmcz-news',
		plugin_dir_url(__FILE__) . 'blocks/news.js',
		array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' )
	);
	register_block_type( 'wmcz/news', [
		'editor_script' => 'wmcz-news',
		'render_callback' => 'wmcz_block_news_render_callback'
	] );
}

function wmcz_format_new( $new, $extraClass="", $readMore=null ) {
	$html = '<div class="wmcz-new ' . $extraClass . '">';
	$html .= '<h3>' . $new->title . '</h3>';
	$html .= '<p>Published: ' . $new->added . '</p>';
	$html .= '<p>' . $new->description . '</p>';
	if ( $readMore ) {
		$html .= '<a class="wmcz-new-read-more" href="' . $readMore . '?wmcz-new-id=' . $new->id . '">Více</a>';
	}
	$html .= '</div>';
	return $html;
}

function wmcz_block_news_render_callback( $attributes ) {
	global $wpdb;
	$news =  $wpdb->get_results( "SELECT id, title, description, photo, added FROM {$wpdb->prefix}wmcz_news ORDER BY added DESC LIMIT 4", OBJECT );
	$html = '<div class="wmcz-news">
	<h2>Novinky</h2>
	<div class="wp-block-columns has-4-columns wmcz-news-inner">';
	foreach ( $news as $new ) {
		$html .= wmcz_format_new( $new, "wp-block-column", $attributes['more'] );
	}
	$html .= '</div>
	<div class="wp-block-button"><a class="wp-block-button__link has-background no-border-radius" href="' . $attributes['more'] . '" style="background-color:#339966">Další novinky</a></div>
	</div>';
	return $html;
}

function wmcz_block_news_list_register() {
	wp_register_script(
		'wmcz-news-list',
		plugin_dir_url(__FILE__) . 'blocks/news-list.js',
		array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-data' )
	);
	register_block_type( 'wmcz/news-list', [
		'editor_script' => 'wmcz-news-list',
		'render_callback' => 'wmcz_block_news_list_render_callback'
	] );
}

function wmcz_block_news_list_render_callback( $attributes ) {
	global $wpdb;

	if ( isset( $_GET['wmcz-new-id'] ) ) {
		$query = $wpdb->prepare( "SELECT id, title, description, photo, added FROM {$wpdb->prefix}wmcz_news WHERE id=%d", (int)$_GET['wmcz-new-id'] );
		$new = $wpdb->get_row( $query, OBJECT );
		return wmcz_format_new( $new );
	} else {
		$news =  $wpdb->get_results( "SELECT id, title, description, photo, added FROM {$wpdb->prefix}wmcz_news ORDER BY added DESC", OBJECT );
		$html = '<div class="wmcz-news-list-container">';

		foreach ( $news as $new ) {
			$html .= wmcz_format_new( $new );
		}
		$html .= '</div>';
		return $html;
	}
}

/**
 * Renders the <code>core/latest-posts</code> block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_block_wmcz_latest_posts( $attributes ) {
	$args = array(
		'numberposts' => $attributes['postsToShow'],
		'post_status' => 'publish',
		'order'       => $attributes['order'],
		'orderby'     => $attributes['orderBy'],
	);

	if ( isset( $attributes['categories'] ) ) {
		$args['category'] = $attributes['categories'];
	}

	$recent_posts = wp_get_recent_posts( $args );

	$list_items_markup = '';

	foreach ( $recent_posts as $post ) {
		$post_id = $post['ID'];

		$title = get_the_title( $post_id );
		if ( ! $title ) {
			$title = __( '(Untitled)' );
		}
		
		$list_items_markup .= sprintf(
			'<li>
			<a href="%2$s">%3$s</a><p>%4$s <a href="%5$s">Více</a></p>%1$s',
			get_the_post_thumbnail( $post_id, 'post-thumbnail' ),
			esc_url( get_permalink( $post_id ) ),
			esc_html( $title ), 
			get_the_excerpt( $post_id ),
			esc_url( get_permalink( $post_id ) )
		);
				
		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$list_items_markup .= sprintf(
				'<time datetime="%1$s" class="wp-block-latest-posts__post-date">%2$s</time>',
				esc_attr( get_the_date( 'c', $post_id ) ),
				esc_html( get_the_date( '', $post_id ) )
			);
		}
		$list_items_markup .= "</li>\n";
	}

	$class = 'wp-block-latest-posts';
	if ( isset( $attributes['align'] ) ) {
		$class .= ' align' . $attributes['align'];
	}

	if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' is-grid';
	}

	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' columns-' . $attributes['columns'];
	}

	if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
		$class .= ' has-dates';
	}

	if ( isset( $attributes['className'] ) ) {
		$class .= ' ' . $attributes['className'];
	}

	$block_content = sprintf(
		'<ul class="wp-block-latest-posts wp-block-latest-posts__list wmcz-latest-posts %1$s">%2$s</ul>',
		esc_attr( $class ),
		$list_items_markup
	);

	return $block_content;
}

/**
 * Registers the <code>core/latest-posts</code> block on server.
 */
function register_block_wmcz_latest_posts() {
	register_block_type(
		'wmcz/latest-posts',
		array(
			'attributes'      => array(
				'categories'      => array(
					'type' => 'string',
				),
				'className'       => array(
					'type' => 'string',
				),
				'postsToShow'     => array(
					'type'    => 'number',
					'default' => 5,
				),
				'displayPostDate' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'postLayout'      => array(
					'type'    => 'string',
					'default' => 'list',
				),
				'columns'         => array(
					'type'    => 'number',
					'default' => 3,
				),
				'align'           => array(
					'type' => 'string',
				),
				'order'           => array(
					'type'    => 'string',
					'default' => 'desc',
				),
				'orderBy'         => array(
					'type'    => 'string',
					'default' => 'date',
				),
			),
			'render_callback' => 'render_block_wmcz_latest_posts',
		)
	);
}

global $wmcz_db_version;
$wmcz_db_version = 1;

function wmcz_install() {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "wmcz_news";
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		added datetime DEFAULT current_timestamp NOT NULL,
		title tinytext NOT NULL,
		description text NOT NULL,
		photo text NOT NULL,
		published boolean DEFAULT 0 NOT NULL,
		PRIMARY KEY  (id)
	  ) $charset_collate;";
	dbDelta( $sql );

	$table_name = $wpdb->prefix . "wmcz_tags";
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		PRIMARY KEY (id)
	  ) $charset_collate;";
	dbDelta( $sql );

	$table_name = $wpdb->prefix . "wmcz_tags_news";
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		tag_id mediumint(9) NOT NULL,
		new_id mediumint(9) NOT NULL,
		PRIMARY KEY (id),
		FOREIGN KEY (tag_id) REFERENCES {$wpdb->prefix}wmcz_tags(id),
		FOREIGN KEY (new_id) REFERENCES {$wpdb->prefix}wmcz_news(id)
	  ) $charset_collate;";
	dbDelta( $sql );
}

require_once 'wmcz_admin.php';

add_action( 'init', 'wmcz_block_calendar_register' );
add_action( 'init', 'wmcz_block_map_register' );
add_action( 'init', 'wmcz_block_caurosel_register' );
add_action( 'init', 'wmcz_block_news_register' );
add_action( 'init', 'wmcz_block_news_list_register' );
add_action( 'init', 'register_block_wmcz_latest_posts' );
register_activation_hook( __FILE__, 'wmcz_install' );

if (!is_admin()) {
	wp_enqueue_script('leaflet', plugins_url( 'static/leaflet/dist/leaflet.js', __FILE__ ) );
	wp_enqueue_style('leaflet', plugins_url( 'static/leaflet/dist/leaflet.css', __FILE__ ) );
	wp_enqueue_style('wmcz-plugin', plugins_url( 'static/stylesheet.css', __FILE__ ) );
	wp_enqueue_script('wmcz-plugin', plugins_url( 'static/map.js', __FILE__ ) );
	wp_enqueue_script('wmcz-plugin-events', plugins_url( 'static/calendar.js', __FILE__ ) );
	wp_enqueue_script('wmcz-plugin-caurosel', plugins_url( 'static/caurosel.js', __FILE__ ) );
}