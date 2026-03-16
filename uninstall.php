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

// ── Logo-Cache-Einträge löschen (fsw_logo_<md5>) ──────────────────────────
$like3 = $wpdb->esc_like( 'fsw_logo_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$like3
	)
);
