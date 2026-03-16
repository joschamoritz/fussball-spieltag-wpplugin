<?php
/**
 * Fussball Spieltag Widget – API-Zugriff
 *
 * Verwaltet alle Anfragen an api-fussball.de inkl. Transient-Caching
 * und SSL-Fallback. Responses werden 30 Minuten gecacht.
 *
 * @package fussball-spieltag
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Führt einen API-Request durch und gibt die gecachte Response zurück.
 *
 * @param  string $ep API-Endpoint, z.B. "/team/next_games/{team_id}".
 * @return array|WP_Error  Dekodierte JSON-Response oder WP_Error.
 */
function fsw_api( $ep ) {
	$tok = get_option( 'fsw_api_token', '' );
	if ( ! $tok ) return new WP_Error( 'no_token', 'API-Token fehlt. Einstellungen → Fussball Spieltag Widget.' );

	$key = 'fsw_' . md5( $ep );
	$c   = get_transient( $key );
	if ( false !== $c ) return $c;

	$url  = rtrim( get_option( 'fsw_api_base', 'https://api-fussball.de/api' ), '/' ) . $ep;
	$args = [ 'timeout' => 15, 'headers' => [ 'x-auth-token' => $tok ] ];
	$r    = wp_remote_get( $url, $args );

	// SSL-Fallback: www.api-fussball.de hat kein gültiges Zertifikat,
	// deshalb bei SSL-Fehler zwischen www und non-www wechseln.
	if ( is_wp_error( $r ) && strpos( $r->get_error_message(), 'SSL' ) !== false ) {
		$alt = strpos( $url, '://www.' ) !== false
			? str_replace( '://www.', '://', $url )
			: str_replace( '://', '://www.', $url );
		$r = wp_remote_get( $alt, $args );
	}

	if ( is_wp_error( $r ) ) return new WP_Error( 'api', 'Verbindung: ' . $r->get_error_message() );
	if ( wp_remote_retrieve_response_code( $r ) !== 200 ) {
		return new WP_Error( 'api', 'HTTP ' . wp_remote_retrieve_response_code( $r ) );
	}

	$d = json_decode( wp_remote_retrieve_body( $r ), true );
	if ( ! is_array( $d ) ) return new WP_Error( 'json', 'Ungültige API-Antwort.' );

	set_transient( $key, $d, FSW_CACHE );
	return $d;
}

/**
 * Löscht alle API-Transients des Plugins aus der Datenbank.
 *
 * @return int|false Anzahl gelöschter Zeilen oder false bei Fehler.
 */
function fsw_clear_cache() {
	global $wpdb;
	$like1 = $wpdb->esc_like( '_transient_fsw_' )         . '%';
	$like2 = $wpdb->esc_like( '_transient_timeout_fsw_' ) . '%';
	return $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$like1,
			$like2
		)
	);
}
