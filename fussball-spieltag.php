<?php
/**
 * Plugin Name: Fussball Spieltag Widget
 * Plugin URI:  https://github.com/joschamoritz/fussball-spieltag-wpplugin
 * Description: Spieltag-Widget für Fußballvereine auf fussball.de – Nächstes Spiel, Formkurve, Tabelle, Spielplan und Banner-Texte als Shortcodes.
 * Version:     5.0.0
 * Author:      joschamoritz
 * Author URI:  https://github.com/joschamoritz
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fussball-spieltag
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FSW_VERSION', '5.0.0' );
define( 'FSW_CACHE',   1800 );          // Cache-Dauer in Sekunden (30 Minuten)
define( 'FSW_DIR',     plugin_dir_path( __FILE__ ) );
define( 'FSW_URL',     plugin_dir_url( __FILE__ ) );

require_once FSW_DIR . 'includes/helpers.php';
require_once FSW_DIR . 'includes/api.php';
require_once FSW_DIR . 'includes/admin.php';
require_once FSW_DIR . 'includes/shortcodes.php';

/**
 * Aktivierungs-Hook: Setzt Demo-Defaults (FC Schalke 04) beim ersten Aktivieren.
 * Bestehende Werte werden nicht überschrieben.
 */
register_activation_hook( __FILE__, 'fsw_activate' );
function fsw_activate() {
	if ( ! get_option( 'fsw_api_base' ) ) {
		update_option( 'fsw_api_base', 'https://api-fussball.de/api' );
	}
	if ( ! get_option( 'fsw_club_id' ) ) {
		update_option( 'fsw_club_id', '00ES8GN8OC00001LVV0AG08LVUPGND5I' ); // FC Schalke 04
	}
	if ( ! get_option( 'fsw_team_ids' ) ) {
		update_option( 'fsw_team_ids', [
			[ 'name' => '1. Mannschaft', 'id' => '011MIA6VDK000000VTVG0001VTR8C1K7', 'show' => '1' ],
		] );
	}
	if ( ! get_option( 'fsw_club_name' ) ) {
		update_option( 'fsw_club_name', 'Schalke' );
	}
	if ( ! get_option( 'fsw_color_primary' ) ) {
		update_option( 'fsw_color_primary', '#29166f' );
	}
	if ( ! get_option( 'fsw_color_accent' ) ) {
		update_option( 'fsw_color_accent', '#d4a843' );
	}
	if ( false === get_option( 'fsw_load_fonts' ) ) {
		update_option( 'fsw_load_fonts', '1' );
	}
}

/**
 * CSS, dynamische Farben, Fonts und Tab-Script einbinden.
 */
add_action( 'wp_enqueue_scripts', function () {
	// Haupt-Stylesheet
	wp_enqueue_style( 'fsw-spieltag', FSW_URL . 'assets/style.css', [], FSW_VERSION );

	// Dynamische Farben als Inline-Style direkt nach dem Stylesheet (kein separater wp_head-Block)
	$primary = sanitize_hex_color( get_option( 'fsw_color_primary', '#29166f' ) ) ?: '#29166f';
	$accent  = sanitize_hex_color( get_option( 'fsw_color_accent',  '#d4a843' ) ) ?: '#d4a843';
	$dark    = fsw_adjust_brightness( $primary, -30 );
	$light   = fsw_adjust_brightness( $primary, 30 );
	$faint   = fsw_hex_to_rgba( $primary, 0.07 );
	$faint2  = fsw_hex_to_rgba( $primary, 0.08 );
	wp_add_inline_style( 'fsw-spieltag',
		':root{'
		. '--fsw-primary:'        . esc_attr( $primary ) . ';'
		. '--fsw-dark:'           . esc_attr( $dark )    . ';'
		. '--fsw-light:'          . esc_attr( $light )   . ';'
		. '--fsw-accent:'         . esc_attr( $accent )  . ';'
		. '--fsw-primary-faint:'  . $faint               . ';'
		. '--fsw-primary-faint2:' . $faint2              . ';'
		. '}'
	);

	// Google Fonts – optional (Standard: an)
	if ( get_option( 'fsw_load_fonts', '1' ) !== '0' ) {
		wp_enqueue_style(
			'fsw-fonts',
			'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap',
			[],
			null
		);
	}

	// Tab-Widget-Script (extern, cachebar, mit Arrow-Key-Support)
	wp_enqueue_script( 'fsw-tabs', FSW_URL . 'assets/tabs.js', [], FSW_VERSION, true );
} );

// Preconnect für Google Fonts – DNS-Lookup und TLS-Handshake vorziehen (spart 100–300ms)
add_action( 'wp_head', function () {
	if ( get_option( 'fsw_load_fonts', '1' ) !== '0' ) {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}
}, 1 );
