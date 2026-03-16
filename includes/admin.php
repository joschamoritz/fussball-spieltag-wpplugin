<?php
/**
 * Fussball Spieltag Widget – Admin-Seite
 *
 * Registriert die Einstellungsseite im WordPress-Backend mit allen Feldern:
 * Vereinseinstellungen, API-Konfiguration, Mannschaften und Darstellung.
 *
 * @package fussball-spieltag
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin-Skripte und Color Picker nur auf der Plugin-Seite laden.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( $hook !== 'settings_page_fsw-spieltag' ) return;
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script(
		'fsw-admin',
		FSW_URL . 'assets/admin.js',
		[ 'wp-color-picker', 'jquery' ],
		FSW_VERSION,
		true
	);
} );

// Menüeintrag registrieren
add_action( 'admin_menu', function () {
	add_options_page(
		'Fussball Spieltag Widget',
		'Spieltag Widget',
		'manage_options',
		'fsw-spieltag',
		'fsw_settings_page'
	);
} );

// Einstellungsfelder registrieren
add_action( 'admin_init', function () {

	/* ── Vereinseinstellungen ─────────────────────────────── */
	register_setting( 'fsw_st', 'fsw_club_name', [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'fsw_st', 'fsw_own_logo',  [ 'sanitize_callback' => 'esc_url_raw' ] );

	add_settings_section( 'fsw_club', 'Vereinseinstellungen', null, 'fsw-spieltag' );

	add_settings_field( 'fsw_club_name', 'Vereinsname (Suchbegriff)', function () {
		$v = get_option( 'fsw_club_name', '' );
		echo '<input type="text" name="fsw_club_name" value="' . esc_attr( $v ) . '" class="regular-text">';
		echo '<p class="description">Mit diesem Suchbegriff erkennt das Plugin euer Team in Spielen und Tabellen. '
			. 'Nur ein Wort nötig, Groß-/Kleinschreibung egal. '
			. 'Beispiel: <code>Schalke</code>, <code>Hochlar</code>, <code>Gievenbeck</code>.</p>';
	}, 'fsw-spieltag', 'fsw_club' );

	add_settings_field( 'fsw_own_logo', 'Eigenes Vereinslogo (URL)', function () {
		$v = get_option( 'fsw_own_logo', '' );
		echo '<input type="text" name="fsw_own_logo" value="' . esc_attr( $v ) . '" class="regular-text" placeholder="https://…/logo.png">';
		echo '<p class="description">Hochauflösendes Logo eures Vereins. Wird statt des fussball.de-Logos angezeigt. '
			. 'Leer lassen = Logo aus der API wird verwendet.</p>';
	}, 'fsw-spieltag', 'fsw_club' );

	/* ── API-Einstellungen ────────────────────────────────── */
	register_setting( 'fsw_st', 'fsw_api_token', [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'fsw_st', 'fsw_api_base',  [ 'sanitize_callback' => 'esc_url_raw', 'default' => 'https://api-fussball.de/api' ] );
	register_setting( 'fsw_st', 'fsw_club_id',   [ 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'fsw_st', 'fsw_team_ids',  [ 'sanitize_callback' => 'fsw_sanitize_teams' ] );

	add_settings_section( 'fsw_api', 'API-Einstellungen', null, 'fsw-spieltag' );

	add_settings_field( 'fsw_api_token', 'API-Token', function () {
		$v = get_option( 'fsw_api_token', '' );
		echo '<input type="password" name="fsw_api_token" value="' . esc_attr( $v ) . '" class="regular-text" autocomplete="off">';
		echo '<p class="description">Kostenlosen Token registrieren: '
			. '<a href="https://api-fussball.de/token" target="_blank">api-fussball.de/token</a>. '
			. 'Ohne Token werden keine Daten angezeigt.</p>';
	}, 'fsw-spieltag', 'fsw_api' );

	add_settings_field( 'fsw_api_base', 'API-URL', function () {
		echo '<input type="text" name="fsw_api_base" value="' . esc_attr( get_option( 'fsw_api_base', 'https://api-fussball.de/api' ) ) . '" class="regular-text">';
		echo '<p class="description">Standard: <code>https://api-fussball.de/api</code> – nur ändern wenn nötig. '
			. 'Wichtig: ohne <code>www</code>!</p>';
	}, 'fsw-spieltag', 'fsw_api' );

	add_settings_field( 'fsw_club_id', 'Club-ID', function () {
		echo '<input type="text" name="fsw_club_id" value="' . esc_attr( get_option( 'fsw_club_id', '' ) ) . '" class="regular-text">';
		echo '<p class="description">Die ID eures Vereins auf fussball.de. Zu finden in der URL der Vereinsseite: '
			. '<code>fussball.de/verein/vereinsname/<strong>CLUB_ID</strong>/</code>. '
			. 'Wird für den Spielplan aller Mannschaften benötigt.</p>';
	}, 'fsw-spieltag', 'fsw_api' );

	add_settings_field( 'fsw_team_ids', 'Mannschaften', function () {
		$teams = get_option( 'fsw_team_ids', [] );
		echo '<table class="widefat" style="max-width:700px"><thead><tr>'
			. '<th>Name</th><th>Team-ID</th><th title="Im Tab-Widget anzeigen">Startseite</th>'
			. '</tr></thead><tbody>';
		foreach ( $teams as $i => $t ) {
			echo '<tr>';
			echo '<td><input name="fsw_team_ids[' . $i . '][name]" value="' . esc_attr( $t['name'] ) . '" style="width:150px"></td>';
			echo '<td><input name="fsw_team_ids[' . $i . '][id]" value="' . esc_attr( $t['id'] ) . '" class="regular-text"></td>';
			echo '<td style="text-align:center"><input type="checkbox" name="fsw_team_ids[' . $i . '][show]" value="1" ' . checked( ! empty( $t['show'] ), 1, false ) . '></td>';
			echo '</tr>';
		}
		$n = count( $teams );
		echo '<tr>';
		echo '<td><input name="fsw_team_ids[' . $n . '][name]" placeholder="Neue Mannschaft…" style="width:150px"></td>';
		echo '<td><input name="fsw_team_ids[' . $n . '][id]" placeholder="Team-ID" class="regular-text"></td>';
		echo '<td style="text-align:center"><input type="checkbox" name="fsw_team_ids[' . $n . '][show]" value="1"></td>';
		echo '</tr>';
		echo '</tbody></table>';
		echo '<p class="description">Team-ID: Mannschaftsseite auf fussball.de öffnen → URL enthält '
			. '<code>…/mannschaft/…/<strong>TEAM_ID</strong>/</code>. '
			. '"Startseite" = im Tab-Widget auf der Startseite anzeigen.</p>';
	}, 'fsw-spieltag', 'fsw_api' );

	/* ── Darstellung ──────────────────────────────────────── */
	register_setting( 'fsw_st', 'fsw_color_primary', [ 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#29166f' ] );
	register_setting( 'fsw_st', 'fsw_color_accent',  [ 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#d4a843' ] );
	register_setting( 'fsw_st', 'fsw_load_fonts',    [ 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ] );

	add_settings_section( 'fsw_design', 'Darstellung', null, 'fsw-spieltag' );

	add_settings_field( 'fsw_color_primary', 'Primärfarbe', function () {
		$v = get_option( 'fsw_color_primary', '#29166f' );
		echo '<input type="text" name="fsw_color_primary" value="' . esc_attr( $v ) . '" class="fsw-color-picker" data-default-color="#29166f">';
		echo '<p class="description">Hauptfarbe des Vereins. Wird für Teamnamen, Tabs, Uhrzeit und Akzente verwendet. '
			. 'Hell- und Dunkel-Variante werden automatisch abgeleitet.</p>';
	}, 'fsw-spieltag', 'fsw_design' );

	add_settings_field( 'fsw_color_accent', 'Akzentfarbe', function () {
		$v = get_option( 'fsw_color_accent', '#d4a843' );
		echo '<input type="text" name="fsw_color_accent" value="' . esc_attr( $v ) . '" class="fsw-color-picker" data-default-color="#d4a843">';
		echo '<p class="description">Zweite Farbe für aktive Tabs, Highlights und Banner-Texte. Empfehlung: Gold oder Kontrastfarbe zum Primär.</p>';
	}, 'fsw-spieltag', 'fsw_design' );

	add_settings_field( 'fsw_load_fonts', 'Google Fonts laden', function () {
		$v = get_option( 'fsw_load_fonts', '1' );
		echo '<label><input type="checkbox" name="fsw_load_fonts" value="1" ' . checked( $v, '1', false ) . '> '
			. 'Oswald &amp; Source Sans 3 von Google Fonts laden</label>';
		echo '<p class="description">Deaktivieren wenn euer Theme diese oder ähnliche Schriften bereits lädt – '
			. 'vermeidet doppelte Anfragen. Das Widget funktioniert auch ohne diese Schriften.</p>';
	}, 'fsw-spieltag', 'fsw_design' );
} );

/**
 * Rendert die Admin-Einstellungsseite.
 */
function fsw_settings_page() {
	// Cache leeren (mit Nonce-Schutz gegen CSRF) – I7: eigener Nonce-Parameter
	if ( isset( $_GET['fsw_clear'] ) && current_user_can( 'manage_options' ) ) {
		if ( ! isset( $_GET['_fsw_nonce_clear'] ) || ! wp_verify_nonce( $_GET['_fsw_nonce_clear'], 'fsw_clear_cache' ) ) {
			wp_die( 'Sicherheitsprüfung fehlgeschlagen.' );
		}
		fsw_clear_cache();
		fsw_clear_logo_cache();
	}

	// Debug-Ansicht (mit Nonce-Schutz) – I7: eigener Nonce-Parameter
	$debug = isset( $_GET['fsw_debug'] ) && current_user_can( 'manage_options' );
	if ( $debug && ( ! isset( $_GET['_fsw_nonce_debug'] ) || ! wp_verify_nonce( $_GET['_fsw_nonce_debug'], 'fsw_debug_api' ) ) ) {
		$debug = false;
	}
	?>
	<div class="wrap">
	<h1>⚽ Fussball Spieltag Widget v<?php echo esc_html( FSW_VERSION ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'fsw_st' ); ?>
		<?php do_settings_sections( 'fsw-spieltag' ); ?>
		<?php submit_button(); ?>
	</form>

	<hr>
	<h2>Shortcodes</h2>
	<table class="widefat striped" style="max-width:900px">
		<thead><tr><th style="width:420px">Shortcode</th><th>Beschreibung</th></tr></thead>
		<tbody>
		<tr><td colspan="2" style="background:#f0f0f5;font-weight:600">Startseite</td></tr>
		<tr><td><code>[fsw_spieltag_tabs]</code></td><td>Tabs: alle Mannschaften (als „Startseite" markiert)</td></tr>
		<tr><td><code>[fsw_banner_datum]</code></td><td>Nur Text: Datum + Uhrzeit nächstes Spiel</td></tr>
		<tr><td><code>[fsw_banner_liga]</code></td><td>Nur Text: Liga-Name</td></tr>
		<tr><td colspan="2" style="background:#f0f0f5;font-weight:600">Kombiniert (Box-Variante)</td></tr>
		<tr><td><code>[fsw_spieltag]</code></td><td>Box: Nächstes Spiel + Formkurve + Tabelle</td></tr>
		<tr><td><code>[fsw_spieltag mode="compact"]</code></td><td>Box: Nächstes Spiel + Formkurve + Letztes Ergebnis</td></tr>
		<tr><td colspan="2" style="background:#f0f0f5;font-weight:600">Bausteine (transparent, frei platzierbar)</td></tr>
		<tr><td><code>[fsw_naechstes_spiel]</code></td><td>Nächstes Spiel: Logos + Namen + Uhrzeit</td></tr>
		<tr><td><code>[fsw_letztes_ergebnis]</code></td><td>Letztes Ergebnis: gleiches Layout + farbiger Score</td></tr>
		<tr><td><code>[fsw_formkurve]</code></td><td>Letzte 5 Ligaspiele als Linien-Diagramm</td></tr>
		<tr><td><code>[fsw_formkurve style="dots"]</code></td><td>Einfache S/U/N-Punkte</td></tr>
		<tr><td><code>[fsw_tabelle]</code></td><td>Tabellenausschnitt (5 Zeilen, zentriert um euer Team)</td></tr>
		<tr><td><code>[fsw_tabelle full="1"]</code></td><td>Vollständige Tabelle</td></tr>
		<tr><td><code>[fsw_spielplan anzahl="5"]</code></td><td>Nächste X Spiele aller Vereinsteams</td></tr>
		<tr><td colspan="2" style="background:#f0f0f5;font-weight:600">Parameter (bei allen Shortcodes möglich)</td></tr>
		<tr><td><code>team="TEAM_ID"</code></td><td>Andere Mannschaft anzeigen</td></tr>
		<tr><td><code>style="box"</code></td><td>Box-Stil mit dunkler Kopfzeile erzwingen</td></tr>
		</tbody>
	</table>

	<hr>
	<h2>Cache &amp; Debug</h2>
	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'fsw_clear', '1' ), 'fsw_clear_cache', '_fsw_nonce_clear' ) ); ?>" class="button">
		Cache leeren (API + Logos)
	</a>
	<?php if ( isset( $_GET['fsw_clear'] ) ) : ?>
		<span style="color:green;margin-left:8px">✓ API-Cache + Logo-Cache geleert</span>
	<?php endif; ?>
	&nbsp;
	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'fsw_debug', '1' ), 'fsw_debug_api', '_fsw_nonce_debug' ) ); ?>" class="button">
		API-Debug
	</a>
	<?php
	if ( $debug ) {
		// I6: Cache wird im Debug-Modus NICHT mehr geleert – kein Einfluss auf Frontendbesucher
		$tid = fsw_tid();
		foreach ( [ 'next_games', 'prev_games', 'table' ] as $ep ) {
			echo '<h4>' . esc_html( $ep ) . '</h4>';
			echo '<pre style="background:#f5f5f5;padding:12px;max-height:250px;overflow:auto;font-size:11px;border:1px solid #ddd">';
			$r = fsw_api( '/team/' . $ep . '/' . $tid );
			echo esc_html( is_wp_error( $r ) ? $r->get_error_message() : json_encode( $r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			echo '</pre>';
		}
	}
	?>
	</div>
	<?php
}

// API-Cache automatisch leeren wenn relevante Einstellungen geändert werden
add_action( 'update_option_fsw_team_ids',  'fsw_clear_cache' );
add_action( 'update_option_fsw_club_id',   'fsw_clear_cache' );
add_action( 'update_option_fsw_api_token', 'fsw_clear_cache' );  // C3: Cache bei Token-Wechsel leeren

// Admin-Bar-Eintrag
add_action( 'admin_bar_menu', function ( $b ) {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$b->add_node( [
		'id'    => 'fsw-st',
		'title' => '⚽ Spieltag',
		'href'  => admin_url( 'options-general.php?page=fsw-spieltag' ),
	] );
}, 101 );
