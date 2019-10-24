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

function wmcz_block_render_events( $cols, $rows, $events, $class ) {
	$html = '<div id="wmcz-events-' . $class . '" class="wmcz-events-set wp-block-columns has-' . $cols*2 . '-columns">';
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

function wmcz_block_events_render_callback( $attributes ) {
	$cols = (int)$attributes['cols'];
	$rows = (int)$attributes['rows'];
	$calendar = new WmczCalendar($attributes['ical'], $cols*$rows);
	$now = $calendar->getEventsNow();
	$next = $calendar->getEventsNext();
	$html = '';
	$html .= wmcz_block_render_events( $cols, $rows, $now, "this-month" );
	$html .= wmcz_block_render_events( $cols, $rows, $next, "next-month" );
	return '<div class="block-wmcz-events">
	<h2>kalendář akcí</h2>
	<div class="wmcz-events-controls">
		<button id="wmcz-events-control-this-month" class="wmcz-events-control">tento měsíc</button>
		<button id="wmcz-events-control-next-month" class="wmcz-events-control">příští měsíc</button>
	</div>
	' . $html . '</div>';
}

function wmcz_block_events_register() {
	wp_register_script(
		'wmcz-events',
		plugin_dir_url(__FILE__) . 'blocks/events.js',
		array( 'wp-blocks', 'wp-element', 'wp-data' )
	);

	register_block_type( 'wmcz/events', array(
		'editor_script' => 'wmcz-events',
		'render_callback' => 'wmcz_block_events_render_callback'
	) );
}

function wmcz_block_event_map_render_callback( $attributes ) {
	$id = uniqid();
	$calendar = new WmczCalendar( $attributes['ical'] );
	$data = [
		'points' => $calendar->getPlaces(),
		'defaults' => [
			'lat' => 50.03861,
			'lon' => 15.77916,
			'zoom' => 8
		]
	];
	return '<div class="wmcz-map-container" data-id="' . $id . '">
	<div class="wmcz-map-data" data-id="' . $id . '">' . esc_html( json_encode( $data ) ) . '</div>
	<div class="wmcz-map" data-id="' . $id . '" id="map-' . $id . '"></div>';
}

function wmcz_block_event_map_register() {
	wp_register_script(
		'wmcz-event-map',
		plugin_dir_url(__FILE__) . 'blocks/event-map.js',
		array( 'wp-blocks', 'wp-element', 'wp-data' )
	);

	register_block_type( 'wmcz/event-map', array(
		'editor_script' => 'wmcz-event-map',
		'render_callback' => 'wmcz_block_event_map_render_callback'
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
			<h2>' . $headline . '</h2>
			<p>' . $description . '</p>

			' . $menu . '
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

add_action( 'init', 'wmcz_block_events_register' );
add_action( 'init', 'wmcz_block_event_map_register' );
add_action( 'init', 'wmcz_block_caurosel_register' );

wp_enqueue_script('leaflet', plugins_url( 'static/leaflet/dist/leaflet.js', __FILE__ ) );
wp_enqueue_style('leaflet', plugins_url( 'static/leaflet/dist/leaflet.css', __FILE__ ) );
wp_enqueue_style('wmcz-plugin', plugins_url( 'static/stylesheet.css', __FILE__ ) );
wp_enqueue_script('wmcz-plugin', plugins_url( 'static/map.js', __FILE__ ) );
wp_enqueue_script('wmcz-plugin-events', plugins_url( 'static/events.js', __FILE__ ) );
wp_enqueue_script('wmcz-plugin-caurosel', plugins_url( 'static/caurosel.js', __FILE__ ) );