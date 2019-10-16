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

function wmcz_block_events_render_callback( $attributes ) {
	$cols = (int)$attributes['cols'];
	$rows = (int)$attributes['rows'];
	$calendar = new WmczCalendar($cols*$rows, $attributes['ical']);

	$now = $calendar->getEventsNow();
	$html = '<h2>kalendář akcí</h2>
	<div class="wp-block-columns has-' . $cols*2 . '-columns">';
	for ($i=0; $i < $cols; $i++) { 
		$html .= '<div class="wp-block-column">';
		$sliced = array_slice($now, $i*$rows, $rows);
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
	return "<div class=\"block-wmcz-events\">$html</div>";
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
	$id = uniqid("map");
	return '<div class="wmcz-map" data-ical="' . $attributes['ical'] . '" id="' . $id . '"></div>';
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

function wmcz_block_caurosel_render_callback( $attributes ) {
	return file_get_contents( plugin_dir_url(__FILE__) . 'static/caurosel.html');
}

function wmcz_block_caurosel_register() {
	wp_register_script(
		'wmcz-caurosel',
		plugin_dir_url(__FILE__) . 'blocks/caurosel.js',
		array( 'wp-blocks', 'wp-element', 'wp-data' )
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