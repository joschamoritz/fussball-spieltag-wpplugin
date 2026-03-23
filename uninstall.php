<?php
/**
 * Fussball Spieltag Widget – Deinstallations-Routine
 *
 * Wird von WordPress ausgeführt wenn das Plugin über das Backend gelöscht wird.
 * Entfernt alle Plugin-Optionen und Transients aus der Datenbank.
 *
 * @package fussball-spieltag
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// ── Plugin-Optionen löschen ────────────────────────────────────────────────
$options = [
	'fsw_api_token',
	'fsw_api_base',
	'fsw_club_id',
	'fsw_club_name',
	'fsw_team_ids',
	'fsw_own_logo',
	'fsw_color_primary',
	'fsw_color_accent',
	'fsw_load_fonts',
];
foreach ( $options as $opt ) {
	delete_option( $opt );
}

// ── API-Transients löschen (fsw_<md5>) ────────────────────────────────────
global $wpdb;

$like1 = $wpdb->esc_like( '_transient_fsw_' ) . '%';
$like2 = $wpdb->esc_like( '_transient_timeout_fsw_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$like1,
		$like2
	)
);

// ── Logo-Mediathek-Anhänge löschen (wp_posts attachment-Einträge + Dateien) ─
// I4: Beim Deinstallieren auch die Mediathek-Anhänge und physischen Dateien entfernen
$like3       = $wpdb->esc_like( 'fsw_logo_' ) . '%';
$logo_values = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value NOT IN ('__failed__')",
		$like3
	)
);
if ( $logo_values ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	foreach ( $logo_values as $raw ) {
		$data      = maybe_unserialize( $raw );
		$attach_id = 0;

		if ( is_array( $data ) && ! empty( $data['id'] ) ) {
			// Neues Format (>= v5.4.0): Attachment-ID direkt verfügbar
			$attach_id = (int) $data['id'];
		} elseif ( is_string( $data ) && ! empty( $data ) ) {
			// Altes Format (< v5.4.0): nur URL gespeichert → ID über URL ermitteln
			$attach_id = (int) attachment_url_to_postid( $data );
		}

		if ( $attach_id ) {
			wp_delete_attachment( $attach_id, true );   // true = physische Datei mitlöschen
		}
	}
}

// ── Logo-Cache-Einträge löschen (fsw_logo_<md5>) ──────────────────────────
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$like3
	)
);
