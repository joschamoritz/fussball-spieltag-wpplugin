<?php
/**
 * Fussball Spieltag Widget – Hilfsfunktionen
 *
 * Enthält allgemeine Utilities: Datum-Parsing, Vereinserkennung,
 * Logo-Caching, Spiel-Filterung und Darstellungs-Helfer.
 *
 * @package fussball-spieltag
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gibt das Standard-Team-Array zurück (leer – Defaults werden beim Aktivieren gesetzt).
 *
 * @return array
 */
function fsw_def_teams() {
	return [];
}

/**
 * Bereinigt das Team-Array aus dem Admin-Formular.
 *
 * @param  mixed $in Roheingabe aus dem Formular.
 * @return array     Bereinigtes Team-Array.
 */
function fsw_sanitize_teams( $in ) {
	if ( ! is_array( $in ) ) return fsw_def_teams();
	$out = [];
	foreach ( $in as $t ) {
		if ( empty( $t['id'] ) && empty( $t['name'] ) ) continue;
		$out[] = [
			'name' => sanitize_text_field( $t['name'] ?? '' ),
			'id'   => sanitize_text_field( $t['id'] ?? '' ),
			'show' => ! empty( $t['show'] ) ? '1' : '',
		];
	}
	return $out;
}

/**
 * Extrahiert das data-Array aus einer API-Response.
 *
 * @param  array $d API-Response-Array.
 * @return array    Spielliste oder leeres Array.
 */
function fsw_data( $d ) {
	return isset( $d['data'] ) && is_array( $d['data'] ) ? $d['data'] : [];
}

/**
 * Parst Datum und Uhrzeit aus den API-Feldern in ein einheitliches Array.
 *
 * @param  string $date_str Datum im Format "dd.mm.yyyy".
 * @param  string $time_str Uhrzeit im Format "HH:MM" (optional).
 * @return array {
 *     @type string $d    Datum als "dd.mm."
 *     @type string $t    Uhrzeit als "HH:MM"
 *     @type string $wd   Wochentag kurz (z.B. "So")
 *     @type string $wdl  Wochentag lang (z.B. "Sonntag")
 *     @type int    $ts   Unix-Timestamp
 *     @type string $full Vollständiges Datum (z.B. "So, 01.03.2026")
 * }
 */
function fsw_dt( $date_str, $time_str = '' ) {
	if ( ! $date_str ) return [ 'd' => '—', 't' => '—', 'wd' => '', 'wdl' => '', 'ts' => 0, 'full' => '—' ];
	$combined = $date_str . ( $time_str ? ' ' . $time_str : '' );
	$ts = 0;
	foreach ( [ 'd.m.Y H:i', 'd.m.Y', 'Y-m-d H:i:s' ] as $fmt ) {
		$o = DateTime::createFromFormat( $fmt, $combined );
		if ( $o ) { $ts = $o->getTimestamp(); break; }
	}
	if ( ! $ts ) $ts = strtotime( $combined );
	if ( ! $ts ) return [ 'd' => $date_str, 't' => $time_str ?: '', 'wd' => '', 'wdl' => '', 'ts' => 0, 'full' => $date_str ];

	$ws   = [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ];
	$wl   = [ 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' ];
	$widx = (int) date( 'w', $ts );
	return [
		'd'   => date( 'd.m.', $ts ),
		't'   => date( 'H:i', $ts ),
		'wd'  => $ws[ $widx ],
		'wdl' => $wl[ $widx ],
		'ts'  => $ts,
		'full' => $ws[ $widx ] . ', ' . date( 'd.m.Y', $ts ),
	];
}

/**
 * Prüft ob ein Teamname zum konfigurierten Verein gehört.
 * Der Suchbegriff wird in den Plugin-Einstellungen unter "Vereinsname" hinterlegt.
 *
 * @param  string $n Teamname aus der API.
 * @return bool
 */
function fsw_hl( $n ) {
	$club = get_option( 'fsw_club_name', '' );
	if ( ! $club ) return false;
	return stripos( $n, $club ) !== false;
}

/**
 * Ermittelt die Team-ID: aus Shortcode-Attribut oder erster konfigurierter Mannschaft.
 *
 * @param  string $a Optionale Team-ID aus dem Shortcode-Attribut.
 * @return string    Team-ID oder leerer String (API gibt dann Fehler zurück).
 */
function fsw_tid( $a = '' ) {
	if ( $a ) return sanitize_text_field( $a );
	$t = get_option( 'fsw_team_ids', [] );
	return ! empty( $t[0]['id'] ) ? $t[0]['id'] : '';
}

/**
 * Gibt eine formatierte Fehlermeldung als HTML zurück.
 *
 * @param  string $m Fehlermeldung.
 * @return string    HTML.
 */
function fsw_err( $m ) {
	return '<div class="fsw-w fsw-err"><p>' . esc_html( $m ) . '</p></div>';
}

/**
 * Hellt eine Hex-Farbe auf (+) oder dunkelt sie ab (-).
 *
 * @param  string $hex    Hex-Farbe (mit oder ohne #).
 * @param  int    $amount Betrag (positiv = heller, negativ = dunkler).
 * @return string         Angepasste Hex-Farbe mit #.
 */
function fsw_adjust_brightness( $hex, $amount ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) return '#000000';
	$r = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $amount ) );
	$g = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $amount ) );
	$b = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $amount ) );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Konvertiert eine Hex-Farbe in einen rgba()-String.
 *
 * @param  string $hex   Hex-Farbe (mit oder ohne #).
 * @param  float  $alpha Alpha-Wert (0–1).
 * @return string        rgba()-String, z.B. "rgba(41,22,111,0.07)".
 */
function fsw_hex_to_rgba( $hex, $alpha = 1.0 ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) return 'rgba(0,0,0,1)';
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
}

/**
 * Gibt die lokale URL eines gecachten Logos zurück.
 * Beim ersten Aufruf wird ein asynchroner Download per WP-Cron eingeplant (I2: kein TTFB-Block).
 * Bis der Cron-Job läuft wird die Remote-URL als Fallback zurückgegeben.
 *
 * @param  string $remote_url URL des externen Logos.
 * @return string             Lokale URL des gecachten Logos oder Remote-URL als Fallback.
 */
function fsw_cached_logo_url( $remote_url ) {
	if ( empty( $remote_url ) ) return '';
	$cache_key = 'fsw_logo_' . md5( $remote_url );
	$local_url = get_option( $cache_key );
	if ( $local_url ) {
		// '__failed__' = vorheriger Download fehlgeschlagen → Remote-URL als Fallback
		if ( '__failed__' === $local_url ) return $remote_url;
		return $local_url;
	}

	// Noch kein gecachtes Logo → Download asynchron per WP-Cron einplanen
	if ( ! wp_next_scheduled( 'fsw_download_logo', [ $remote_url ] ) ) {
		wp_schedule_single_event( time(), 'fsw_download_logo', [ $remote_url ] );
	}

	return $remote_url;   // Einstweilen Remote-URL als Fallback zurückgeben
}

/**
 * Führt den Logo-Download durch – wird ausschließlich per WP-Cron aufgerufen.
 * Prüft MIME-Typ anhand des Dateiinhalts (C2), lehnt SVG ab (C1),
 * begrenzt die Dateigröße auf 512 KB (I1) und schreibt via WP_Filesystem (C1).
 *
 * @param  string $remote_url URL des externen Logos.
 * @return void
 */
function fsw_do_logo_download( $remote_url ) {
	if ( empty( $remote_url ) ) return;
	$cache_key = 'fsw_logo_' . md5( $remote_url );
	if ( get_option( $cache_key ) ) return;   // Inzwischen schon gecacht

	$response = wp_remote_get( $remote_url, [
		'timeout'             => 10,          // Im Cron-Kontext kein TTFB-Problem
		'limit_response_size' => 512 * 1024,  // I1: max. 512 KB
	] );
	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
		update_option( $cache_key, '__failed__', false );
		return;
	}

	$image_data = wp_remote_retrieve_body( $response );

	// C2: MIME-Typ aus dem echten Dateiinhalt ermitteln, nicht aus dem HTTP-Header
	$allowed = [ 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif' ];
	if ( function_exists( 'finfo_buffer' ) ) {
		$finfo     = new finfo( FILEINFO_MIME_TYPE );
		$real_mime = $finfo->buffer( $image_data );
	} else {
		// Fallback wenn finfo fehlt: Content-Type-Header mit Whitelist (SVG ausgeschlossen)
		$ct        = wp_remote_retrieve_header( $response, 'content-type' );
		$real_mime = strtok( $ct, ';' );
	}

	// C1: SVG und alle nicht erlaubten Typen ablehnen
	if ( ! isset( $allowed[ $real_mime ] ) ) {
		update_option( $cache_key, '__failed__', false );
		return;
	}
	$ext = $allowed[ $real_mime ];

	$filename   = sanitize_file_name( 'fsw-logo-' . substr( md5( $remote_url ), 0, 8 ) . '.' . $ext );
	$upload_dir = wp_upload_dir();
	$file_path  = trailingslashit( $upload_dir['path'] ) . $filename;
	$file_url   = trailingslashit( $upload_dir['url'] )  . $filename;

	// C1: WP_Filesystem statt file_put_contents
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}
	if ( ! $wp_filesystem->put_contents( $file_path, $image_data, FS_CHMOD_FILE ) ) {
		update_option( $cache_key, '__failed__', false );
		return;
	}

	$attach_id = wp_insert_attachment( [
		'guid'           => $file_url,
		'post_mime_type' => $real_mime,
		'post_title'     => $filename,
		'post_content'   => '',
		'post_status'    => 'inherit',
	], $file_path );

	if ( ! is_wp_error( $attach_id ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $file_path ) );
	}

	update_option( $cache_key, $file_url, false );
}
add_action( 'fsw_download_logo', 'fsw_do_logo_download' );

/**
 * Löscht alle gecachten Logos aus wp_options (fsw_logo_*-Einträge).
 *
 * @return int|false Anzahl gelöschter Zeilen oder false bei Fehler.
 */
function fsw_clear_logo_cache() {
	global $wpdb;
	$like = $wpdb->esc_like( 'fsw_logo_' ) . '%';
	return $wpdb->query(
		$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like )
	);
}

/**
 * Gibt das Wappen-HTML für ein Team zurück.
 * Eigenes Logo (aus Admin) hat Vorrang vor API-Logo; als letzter Fallback ein Text-Kreis.
 *
 * @param  string $name Teamname.
 * @param  string $logo URL des API-Logos (optional).
 * @return string       HTML (<img> oder <span>).
 */
function fsw_crest( $name, $logo = '' ) {
	$alt = 'Wappen ' . trim( $name );

	// Eigenes Logo für den konfigurierten Verein
	if ( fsw_hl( $name ) ) {
		$own = get_option( 'fsw_own_logo', '' );
		if ( $own ) {
			return '<img class="fsw-logo" src="' . esc_url( $own ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" width="72" height="72">';
		}
	}

	// API-Logo lokal gecacht
	if ( $logo ) {
		$logo = fsw_cached_logo_url( $logo );
		return '<img class="fsw-logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" width="72" height="72">';
	}

	// Text-Fallback mit Kreis
	return '<span class="fsw-crest-txt">' . esc_html( mb_strtoupper( mb_substr( trim( $name ), 0, 3 ) ) ) . '</span>';
}

/**
 * Prüft ob ein Spiel ein Pflichtspiel ist (kein Freundschaftsspiel, Testspiel, Turnier).
 *
 * @param  array $game Spieldaten aus der API.
 * @return bool
 */
function fsw_is_competitive( $game ) {
	$comp = strtolower( $game['competition'] ?? '' );
	if ( $comp === '' ) return false;
	$exclude = [ 'freundschaft', 'friendly', 'testspiel', 'turnier', 'vereinsturnier' ];
	foreach ( $exclude as $kw ) {
		if ( strpos( $comp, $kw ) !== false ) return false;
	}
	return true;
}

/**
 * Findet das erste Pflichtspiel aus einer Liste – Fallback auf erstes Spiel.
 *
 * @param  array $games Liste von Spielen.
 * @return array|null   Erstes Pflichtspiel oder erstes Spiel.
 */
function fsw_first_competitive_game( $games ) {
	foreach ( $games as $g ) {
		if ( fsw_is_competitive( $g ) ) return $g;
	}
	return $games[0] ?? null;
}

/**
 * Sortiert vergangene Spiele nach Datum (neuestes zuerst).
 *
 * @param  array $games Unsortierte Spielliste.
 * @return array        Sortierte Spielliste.
 */
function fsw_sort_prev_games( $games ) {
	usort( $games, function ( $a, $b ) {
		$da = fsw_dt( $a['date'] ?? '', $a['time'] ?? '' );
		$db = fsw_dt( $b['date'] ?? '', $b['time'] ?? '' );
		return $db['ts'] - $da['ts'];
	} );
	return $games;
}

/**
 * Ordnet einem Teamnamen das passende Label aus den Admin-Einstellungen zu.
 * Erkennt 1., 2., 3. Mannschaft sowie Jugend-Teams (U19, U17, U15).
 *
 * @param  string $team_name Teamname aus der API (z.B. "FC Schalke 04 II").
 * @return string            Label (z.B. "2. Mannschaft") oder leerer String.
 */
function fsw_team_label_for_game( $team_name ) {
	if ( ! fsw_hl( $team_name ) ) return '';
	$teams = get_option( 'fsw_team_ids', [] );
	foreach ( $teams as $t ) {
		if ( empty( $t['id'] ) ) continue;
		$label = $t['name'];
		if ( strpos( $label, '2.' ) !== false && preg_match( '/\bII\b/i', $team_name ) )   return $label;
		if ( strpos( $label, '3.' ) !== false && preg_match( '/\bIII\b/i', $team_name ) )  return $label;
		if ( stripos( $label, 'U19' ) !== false && stripos( $team_name, 'U19' ) !== false ) return $label;
		if ( stripos( $label, 'U17' ) !== false && stripos( $team_name, 'U17' ) !== false ) return $label;
		if ( stripos( $label, 'U15' ) !== false && stripos( $team_name, 'U15' ) !== false ) return $label;
	}
	// Kein Suffix (II/III/Jugend) → 1. Mannschaft
	if ( ! preg_match( '/\b(II|III|IV|U\d+|[BCDE]-?Jugend|AH|Alte)\b/i', $team_name ) ) {
		foreach ( $teams as $t ) {
			if ( strpos( $t['name'], '1.' ) !== false ) return $t['name'];
		}
	}
	return '';
}

/**
 * Gibt einen Section-Titel im Gievenbeck-Stil zurück: — TEXT —
 *
 * @param  string $text Titeltext.
 * @return string       HTML.
 */
function fsw_section_title( $text ) {
	return '<div class="fsw-section-title"><span>' . esc_html( $text ) . '</span></div>';
}
