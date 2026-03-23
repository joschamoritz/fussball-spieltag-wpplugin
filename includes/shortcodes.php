<?php
/**
 * Fussball Spieltag Widget – Shortcodes
 *
 * Registriert alle Shortcodes des Plugins. Alle Shortcodes geben reines HTML zurück
 * (kein echo) und sind transparent (kein Hintergrund) – es sei denn, style="box" ist gesetzt.
 *
 * @package fussball-spieltag
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/* ================================================================
   MATCH DISPLAY – geteilt von [fsw_naechstes_spiel] + [fsw_letztes_ergebnis]
   ================================================================ */

/**
 * Rendert ein Spiel im einheitlichen Layout:
 *   Logo + Name | Score/Uhrzeit | Logo + Name
 *   + Meta-Zeile darunter (Liga, Datum)
 *
 * @param  array $opts {
 *     @type string $home_name    Heimteam-Name
 *     @type string $away_name    Auswärtsteam-Name
 *     @type string $home_logo    URL Heimteam-Logo (optional)
 *     @type string $away_logo    URL Auswärtsteam-Logo (optional)
 *     @type string $center       Mitte: Uhrzeit oder Score (z.B. "15:00" oder "1 : 2")
 *     @type string $center_class CSS-Klasse für Mitte (z.B. "fsw-score-w")
 *     @type string $meta         Meta-Zeile (z.B. "Sonntag, 01.03.2026 · Bezirksliga")
 *     @type bool   $is_box       Box-Stil mit Kopfzeile
 *     @type string $title        Titel der Kopfzeile (z.B. "Nächstes Spiel")
 *     @type string $league       Liga-Name für die Kopfzeile
 *     @type string $class        Zusätzliche CSS-Klasse für den Wrapper
 * }
 * @return string HTML.
 */
function fsw_render_match( $opts ) {
	$hm           = $opts['home_name'];
	$aw           = $opts['away_name'];
	$hl           = $opts['home_logo']     ?? '';
	$al           = $opts['away_logo']     ?? '';
	$center       = $opts['center'];
	$center_label = $opts['center_label']  ?? '';
	$meta         = $opts['meta']          ?? '';
	$center_class = $opts['center_class']  ?? '';
	$is_box       = $opts['is_box']        ?? false;
	$title        = $opts['title']         ?? '';
	$league       = $opts['league']        ?? '';
	$cls_extra    = $opts['class']         ?? '';

	ob_start();
	?>
	<div class="fsw-match <?php echo esc_attr( $cls_extra ); ?><?php echo $is_box ? '' : ' fsw-transparent'; ?>">
		<?php if ( $is_box && $title ) : ?>
		<div class="fsw-hdr">
			<span class="fsw-badge"><?php echo esc_html( $title ); ?></span>
			<?php if ( $league ) : ?><span class="fsw-league"><?php echo esc_html( $league ); ?></span><?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="fsw-mu">
			<div class="fsw-t<?php echo fsw_hl( $hm ) ? ' fsw-hl' : ''; ?>">
				<div class="fsw-crest"><?php echo fsw_crest( $hm, $hl ); ?></div>
				<div class="fsw-tn"><?php echo esc_html( $hm ); ?></div>
			</div>
			<div class="fsw-mid">
				<div class="fsw-center <?php echo esc_attr( $center_class ); ?>"<?php echo $center_label ? ' aria-label="' . esc_attr( $center_label ) . '"' : ''; ?>><?php echo esc_html( $center ); ?></div>
			</div>
			<div class="fsw-t<?php echo fsw_hl( $aw ) ? ' fsw-hl' : ''; ?>">
				<div class="fsw-crest"><?php echo fsw_crest( $aw, $al ); ?></div>
				<div class="fsw-tn"><?php echo esc_html( $aw ); ?></div>
			</div>
		</div>
		<?php if ( $meta ) : ?>
		<div class="fsw-match-meta"><?php echo esc_html( $meta ); ?></div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}


/* ================================================================
   [fsw_naechstes_spiel]
   ================================================================ */

/**
 * Zeigt das nächste anstehende Spiel an.
 *
 * @param  array $atts Shortcode-Attribute: team, style.
 * @return string      HTML.
 */
function fsw_next_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '', 'style' => '' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	if ( ! $tid ) return fsw_err( 'Keine Team-ID konfiguriert. Einstellungen → Spieltag Widget.' );
	$d    = fsw_api( '/team/next_games/' . $tid );
	if ( is_wp_error( $d ) ) return fsw_err( $d->get_error_message() );
	$gs = fsw_data( $d );
	if ( empty( $gs ) ) return fsw_err( 'Keine anstehenden Spiele.' );

	// Liga aus erstem Pflichtspiel ermitteln (nicht aus Freundschaftsspielen)
	$g_comp = fsw_first_competitive_game( $gs );
	$lg     = $g_comp ? ( $g_comp['competition'] ?? '' ) : '';
	$g      = $gs[0];
	$dt     = fsw_dt( $g['date'] ?? '', $g['time'] ?? '' );
	$is_box = ( $atts['style'] === 'box' );

	// Meta-Zeile nur im transparenten Modus (Box hat Kopfzeile)
	$meta = '';
	if ( ! $is_box ) {
		$parts = [];
		if ( $dt['wdl'] ) $parts[] = $dt['wdl'] . ', ' . $dt['d'] . ( $dt['ts'] ? wp_date( 'Y', $dt['ts'] ) : '' );
		if ( $lg ) $parts[]        = $lg;
		$meta = implode( ' · ', $parts );
	}

	return fsw_render_match( [
		'home_name'    => $g['homeTeam'] ?? '—',
		'away_name'    => $g['awayTeam'] ?? '—',
		'home_logo'    => $g['homeLogo'] ?? '',
		'away_logo'    => $g['awayLogo'] ?? '',
		'center'       => $dt['t'],
		'center_label' => 'Anstoß ' . $dt['t'] . ' Uhr',
		'center_class' => 'fsw-kick',
		'meta'         => $meta,
		'is_box'       => $is_box,
		'title'        => 'Nächstes Spiel',
		'league'       => $lg,
		'class'        => 'fsw-next',
	] );
}
add_shortcode( 'fsw_naechstes_spiel', 'fsw_next_sc' );


/* ================================================================
   [fsw_letztes_ergebnis]
   ================================================================ */

/**
 * Zeigt das letzte Spielergebnis an (gleiches Layout wie nächstes Spiel, Score farbig).
 *
 * @param  array $atts Shortcode-Attribute: team, style.
 * @return string      HTML.
 */
function fsw_last_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '', 'style' => '' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	if ( ! $tid ) return '';
	$d    = fsw_api( '/team/prev_games/' . $tid );
	if ( is_wp_error( $d ) ) return '';

	// Nur Spiele mit Score (abgeschlossene Spiele)
	$gs = array_filter( fsw_data( $d ), function ( $g ) {
		return ( $g['homeScore'] ?? '' ) !== '';
	} );
	if ( empty( $gs ) ) return '';

	// Neuestes zuerst sortieren (API-Reihenfolge nicht garantiert)
	$gs = fsw_sort_prev_games( array_values( $gs ) );
	$g  = $gs[0];

	$hm = $g['homeTeam'] ?? '—';
	$aw = $g['awayTeam'] ?? '—';
	$hg = $g['homeScore'] ?? '?';
	$ag = $g['awayScore'] ?? '?';
	$dt = fsw_dt( $g['date'] ?? '', $g['time'] ?? '' );
	$lg = $g['competition'] ?? '';

	// Ergebnis aus Sicht des eigenen Teams bestimmen
	$ih  = fsw_hl( $hm );
	$own = $ih ? intval( $hg ) : intval( $ag );
	$opp = $ih ? intval( $ag ) : intval( $hg );
	$rc  = ( $own > $opp ) ? 'w' : ( ( $own < $opp ) ? 'l' : 'd' );

	$is_box = ( $atts['style'] === 'box' );
	$meta   = '';
	if ( ! $is_box ) {
		$parts = [];
		if ( $dt['wdl'] ) $parts[] = $dt['wdl'] . ', ' . $dt['d'] . ( $dt['ts'] ? wp_date( 'Y', $dt['ts'] ) : '' );
		if ( $lg ) $parts[]        = $lg;
		$meta = implode( ' · ', $parts );
	}

	return fsw_render_match( [
		'home_name'    => $hm,
		'away_name'    => $aw,
		'home_logo'    => $g['homeLogo'] ?? '',
		'away_logo'    => $g['awayLogo'] ?? '',
		'center'       => $hg . ' : ' . $ag,
		'center_label' => 'Ergebnis: ' . $hg . ' zu ' . $ag,
		'center_class' => 'fsw-score fsw-score-' . $rc,
		'meta'         => $meta,
		'is_box'       => $is_box,
		'title'        => 'Letztes Spiel',
		'league'       => $lg,
		'class'        => 'fsw-last',
	] );
}
add_shortcode( 'fsw_letztes_ergebnis', 'fsw_last_sc' );


/* ================================================================
   [fsw_formkurve]
   ================================================================ */

/**
 * Zeigt die Formkurve der letzten Spiele als Linien-Diagramm oder Dots.
 *
 * @param  array $atts Shortcode-Attribute: team, anzahl, style.
 * @return string      HTML.
 */
function fsw_form_sc( $atts ) {
	$atts   = shortcode_atts( [ 'team' => '', 'anzahl' => 5, 'style' => '' ], $atts );
	$tid    = fsw_tid( $atts['team'] );
	if ( ! $tid ) return '';
	$d      = fsw_api( '/team/prev_games/' . $tid );
	if ( is_wp_error( $d ) ) return '';
	$gs     = fsw_data( $d );
	if ( empty( $gs ) ) return '';

	$target = intval( $atts['anzahl'] );

	// Alle Spiele mit gültigem Score
	$with_score = array_filter( $gs, function ( $g ) {
		return ( $g['homeScore'] ?? '' ) !== '' && ( $g['awayScore'] ?? '' ) !== '';
	} );

	// Pflichtspiele bevorzugen; Fallback: alle Spiele wenn zu wenige Pflichtspiele vorhanden
	$competitive = array_filter( $with_score, function ( $g ) {
		return fsw_is_competitive( $g );
	} );
	$gs = ( count( $competitive ) >= $target )
		? array_values( $competitive )
		: array_values( $with_score );

	// Chronologisch sortieren (ältestes zuerst) für die Diagramm-Darstellung
	usort( $gs, function ( $a, $b ) {
		$da = fsw_dt( $a['date'] ?? '', $a['time'] ?? '' );
		$db = fsw_dt( $b['date'] ?? '', $b['time'] ?? '' );
		return $da['ts'] - $db['ts'];
	} );

	$gs = array_slice( $gs, -$target );

	// Dot-Daten aufbereiten
	$dots = [];
	foreach ( $gs as $g ) {
		$hm      = $g['homeTeam'] ?? '';
		$aw      = $g['awayTeam'] ?? '';
		$hg      = intval( $g['homeScore'] );
		$ag      = intval( $g['awayScore'] );
		$ih      = fsw_hl( $hm );
		$own     = $ih ? $hg : $ag;
		$opp_g   = $ih ? $ag : $hg;
		$opp_n   = $ih ? $aw : $hm;
		$sc      = $hg . ':' . $ag;
		$logo_url = fsw_cached_logo_url( $ih ? ( $g['awayLogo'] ?? '' ) : ( $g['homeLogo'] ?? '' ) );
		$dt      = fsw_dt( $g['date'] ?? '', $g['time'] ?? '' );
		$is_comp = fsw_is_competitive( $g );

		if ( $own > $opp_g )      $res = [ 'r' => 'S', 'c' => 'w' ];
		elseif ( $own < $opp_g )  $res = [ 'r' => 'N', 'c' => 'l' ];
		else                      $res = [ 'r' => 'U', 'c' => 'd' ];

		// Freundschaftsspiele grau darstellen
		if ( ! $is_comp ) $res['c'] = 'f';

		$dots[] = array_merge( $res, [
			's'    => $sc,
			'v'    => $opp_n,
			'logo' => $logo_url,
			'd'    => $dt['d'],
			'comp' => $is_comp,
		] );
	}
	if ( empty( $dots ) ) return '';

	$is_box  = ( $atts['style'] === 'box' );
	$is_dots = ( $atts['style'] === 'dots' );

	// === Einfache Dots (für Box-Modus oder explizit) ===
	if ( $is_dots || $is_box ) {
		ob_start();
		?>
		<div class="fsw-form-dots<?php echo $is_box ? '' : ' fsw-transparent'; ?>">
			<span class="fsw-form-lbl">Form</span>
			<div class="fsw-dots-row">
				<?php foreach ( $dots as $dd ) : ?>
				<span class="fsw-dot fsw-dot-<?php echo $dd['c']; ?>"
				      title="<?php echo esc_attr( $dd['s'] . ' vs ' . $dd['v'] . ( $dd['comp'] ? '' : ' (Test)' ) ); ?>">
					<?php echo $dd['r']; ?>
				</span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// === Gievenbeck-Stil: Linien-Diagramm ===
	$count = count( $dots );
	ob_start();
	?>
	<div class="fsw-formchart">
		<?php echo fsw_section_title( 'Die letzten Spiele' ); ?>
		<?php
		// Screen-Reader-Text: Formkurve als lesbare Zusammenfassung
		$sr_parts = [];
		foreach ( $dots as $dd ) {
			$r_txt      = $dd['r'] === 'S' ? 'Sieg' : ( $dd['r'] === 'U' ? 'Unentschieden' : 'Niederlage' );
			$sr_parts[] = $dd['d'] . ' ' . $r_txt . ' ' . $dd['s'] . ' gegen ' . $dd['v'];
		}
		?>
		<p class="screen-reader-text"><?php echo esc_html( 'Formkurve: ' . implode( ', ', $sr_parts ) ); ?></p>
		<div class="fsw-fc-graph">
			<div class="fsw-fc-y">
				<span>S</span><span>U</span><span>N</span>
			</div>
			<div class="fsw-fc-area">
				<!-- Horizontale Gridlinien für S / U / N -->
				<div class="fsw-fc-gridlines">
					<div class="fsw-fc-gl"></div>
					<div class="fsw-fc-gl"></div>
					<div class="fsw-fc-gl"></div>
				</div>
				<!-- SVG Verbindungslinie zwischen den Spielpunkten -->
				<svg class="fsw-fc-svg" viewBox="0 0 <?php echo $count * 100; ?> 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<?php
					// Koordinaten berechnen: S = oben (y=10), U = mitte (y=100), N = unten (y=190)
					// 5% Puffer verhindert, dass Logos am Rand abgeschnitten werden
					$pts = [];
					foreach ( $dots as $i => $dd ) {
						$x    = $i * 100 + 50;
						$y    = ( $dd['r'] === 'S' ) ? 10 : ( ( $dd['r'] === 'U' ) ? 100 : 190 );
						$pts[] = [ 'x' => $x, 'y' => $y, 'comp' => $dd['comp'] ];
					}
					// Linien segmentweise zeichnen: gestrichelt für Freundschaftsspiele
					for ( $i = 0; $i < count( $pts ) - 1; $i++ ) :
						$is_friendly_segment = ! $pts[ $i ]['comp'] || ! $pts[ $i + 1 ]['comp'];
						$dash = $is_friendly_segment ? 'stroke-dasharray="6,4" opacity="0.4"' : '';
					?>
					<line x1="<?php echo $pts[ $i ]['x']; ?>" y1="<?php echo $pts[ $i ]['y']; ?>"
					      x2="<?php echo $pts[ $i + 1 ]['x']; ?>" y2="<?php echo $pts[ $i + 1 ]['y']; ?>"
					      stroke="var(--fsw-primary)" stroke-width="3"
					      stroke-linecap="round" <?php echo $dash; ?> />
					<?php endfor; ?>
				</svg>
				<!-- Gegner-Logos als absolut positionierte Punkte -->
				<div class="fsw-fc-points">
					<?php foreach ( $dots as $i => $dd ) :
						// Vertikale Position: S=5%, U=50%, N=95% – 5% Puffer verhindert Abschneiden am Rand
						$top      = ( $dd['r'] === 'S' ) ? '5%' : ( ( $dd['r'] === 'U' ) ? '50%' : '95%' );
						$left     = ( ( $i * 100 + 50 ) / ( $count * 100 ) ) * 100;
						$fr_cls   = $dd['comp'] ? '' : ' fsw-fc-friendly';
						$logo_alt = esc_attr( 'Wappen ' . $dd['v'] );
					?>
					<div class="fsw-fc-point<?php echo $fr_cls; ?>" style="left:<?php echo $left; ?>%;top:<?php echo $top; ?>;">
						<?php if ( ! empty( $dd['logo'] ) ) : ?>
						<img src="<?php echo esc_url( $dd['logo'] ); ?>" alt="<?php echo $logo_alt; ?>" loading="lazy" decoding="async" width="32" height="32">
						<?php else : ?>
						<span class="fsw-fc-fallback fsw-dot-<?php echo $dd['c']; ?>"></span>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<!-- Ergebnis-Badges und Datum unter dem Diagramm -->
		<div class="fsw-fc-results">
			<?php foreach ( $dots as $dd ) : ?>
			<div class="fsw-fc-res<?php echo $dd['comp'] ? '' : ' fsw-fc-res-friendly'; ?>">
				<span class="fsw-fcr fsw-fcr-<?php echo $dd['c']; ?>"><?php echo esc_html( $dd['s'] ); ?></span>
				<span class="fsw-fcr-date"><?php echo esc_html( $dd['d'] ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'fsw_formkurve', 'fsw_form_sc' );


/* ================================================================
   [fsw_tabelle]
   ================================================================ */

/**
 * Zeigt die Ligatabelle an – standardmäßig als 5-Zeilen-Ausschnitt zentriert um das eigene Team.
 *
 * @param  array $atts Shortcode-Attribute: team, full, rows, style, title.
 * @return string      HTML.
 */
function fsw_table_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '', 'full' => '0', 'rows' => 5, 'style' => '', 'title' => '1' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	if ( ! $tid ) return fsw_err( 'Keine Team-ID konfiguriert. Einstellungen → Spieltag Widget.' );
	$d    = fsw_api( '/team/table/' . $tid );
	if ( is_wp_error( $d ) ) return fsw_err( $d->get_error_message() );
	$rows = fsw_data( $d );
	if ( empty( $rows ) ) return fsw_err( 'Keine Tabelle.' );

	// Eigenes Team in der Tabelle finden
	$own_i = null;
	foreach ( $rows as $i => $r ) {
		if ( fsw_hl( $r['team'] ?? '' ) ) { $own_i = $i; break; }
	}

	$show   = $rows;
	$exc    = false;
	$target = intval( $atts['rows'] );

	// Ausschnitt: $target Zeilen zentriert um das eigene Team
	if ( $atts['full'] !== '1' && $own_i !== null ) {
		$total = count( $rows );
		$half  = floor( ( $target - 1 ) / 2 );
		$s     = $own_i - $half;
		$e     = $own_i + ( $target - 1 - $half );
		if ( $s < 0 )       { $e = min( $total - 1, $e + abs( $s ) ); $s = 0; }
		if ( $e >= $total ) { $s = max( 0, $s - ( $e - $total + 1 ) ); $e = $total - 1; }
		$show  = array_slice( $rows, $s, $e - $s + 1, true );
		$exc   = true;
	}

	$is_box = ( $atts['style'] === 'box' );
	ob_start();
	?>
	<div class="fsw-tbl<?php echo $is_box ? '' : ' fsw-transparent'; ?>">
		<?php if ( $is_box ) : ?>
		<div class="fsw-hdr">
			<span class="fsw-badge">Tabelle</span>
			<?php if ( $exc ) : ?><span class="fsw-sm">Ausschnitt</span><?php endif; ?>
		</div>
		<?php elseif ( $atts['title'] === '1' ) : ?>
		<?php echo fsw_section_title( 'Aktuelle Tabelle' ); ?>
		<?php endif; ?>
		<table>
			<thead><tr>
				<th class="fsw-pos">Pl.</th>
				<th class="fsw-al">Team</th>
				<th>Sp.</th><th>S</th><th>U</th><th>N</th>
				<th>Tore</th><th>Diff.</th><th class="fsw-pts">Pkt.</th>
			</tr></thead>
			<tbody>
			<?php foreach ( $show as $r ) :
				$nm       = $r['team'] ?? '—';
				$is_own   = fsw_hl( $nm );
				$logo_url = fsw_cached_logo_url( $r['img'] ?? '' );
				// Eigenes Logo für das eigene Team verwenden
				if ( $is_own ) {
					$own_logo = get_option( 'fsw_own_logo', '' );
					if ( $own_logo ) $logo_url = $own_logo;
				}
				$diff     = $r['goalDifference'] ?? '';
				$diff_str = is_numeric( $diff ) ? ( ( $diff > 0 ) ? '+' . $diff : (string) $diff ) : $diff;
			?>
			<tr<?php echo $is_own ? ' class="fsw-our"' : ''; ?>>
				<td class="fsw-pos"><?php echo esc_html( $r['place'] ?? '' ); ?></td>
				<td class="fsw-al">
					<div class="fsw-tc">
						<?php if ( $logo_url ) : ?>
						<img class="fsw-tbl-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( 'Wappen ' . $nm ); ?>" loading="lazy" decoding="async" width="22" height="22">
						<?php endif; ?>
						<?php echo esc_html( $nm ); ?>
					</div>
				</td>
				<td><?php echo esc_html( $r['games'] ?? '' ); ?></td>
				<td><?php echo esc_html( $r['won'] ?? '' ); ?></td>
				<td><?php echo esc_html( $r['draw'] ?? '' ); ?></td>
				<td><?php echo esc_html( $r['lost'] ?? '' ); ?></td>
				<td><?php echo esc_html( $r['goal'] ?? '' ); ?></td>
				<td><?php echo esc_html( $diff_str ); ?></td>
				<td class="fsw-pts"><?php echo esc_html( $r['points'] ?? '' ); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'fsw_tabelle', 'fsw_table_sc' );


/* ================================================================
   [fsw_spieltag_tabs] – Tab-Widget für die Startseite
   ================================================================ */

/**
 * Zeigt alle Mannschaften als Tab-Widget (Nächstes Spiel + Formkurve + Tabelle je Tab).
 *
 * @param  array $atts Shortcode-Attribute: (keine).
 * @return string      HTML.
 */
function fsw_spieltag_tabs_sc( $atts ) {
	$teams = array_values( array_filter(
		get_option( 'fsw_team_ids', [] ),
		function ( $t ) { return ! empty( $t['show'] ) && ! empty( $t['id'] ); }
	) );
	if ( empty( $teams ) ) return fsw_err( 'Keine Mannschaften konfiguriert. Einstellungen → Spieltag Widget.' );

	static $wid = 0;
	$wid++;
	$prefix = 'fsw-w' . $wid;

	// Script nur laden wenn dieses Widget tatsächlich gerendert wird
	wp_enqueue_script( 'fsw-tabs', FSW_URL . 'assets/tabs.js', [], FSW_VERSION, true );

	ob_start();
	?>
	<div class="fsw-w fsw-home" lang="de">
		<div class="fsw-tabs" role="tablist" aria-label="Mannschaften">
			<?php foreach ( $teams as $i => $t ) : ?>
			<button class="fsw-tab<?php echo $i === 0 ? ' fsw-active' : ''; ?>"
			        role="tab"
			        id="<?php echo esc_attr( $prefix . '-tab-' . $i ); ?>"
			        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
			        aria-controls="<?php echo esc_attr( $prefix . '-panel-' . $i ); ?>"
			        tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"
			        data-i="<?php echo $i; ?>"><?php echo esc_html( $t['name'] ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php foreach ( $teams as $i => $t ) : ?>
		<div class="fsw-panel<?php echo $i === 0 ? ' fsw-show' : ''; ?>"
		     role="tabpanel"
		     id="<?php echo esc_attr( $prefix . '-panel-' . $i ); ?>"
		     aria-labelledby="<?php echo esc_attr( $prefix . '-tab-' . $i ); ?>"
		     data-i="<?php echo $i; ?>">
			<?php echo fsw_spieltag_sc( [ 'team' => $t['id'] ] ); ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'fsw_spieltag_tabs', 'fsw_spieltag_tabs_sc' );


/* ================================================================
   [fsw_spieltag] – Kombinierte Box-Variante
   ================================================================ */

/**
 * Zeigt Nächstes Spiel + Formkurve + Tabelle (oder Letztes Ergebnis) in einer Box.
 *
 * @param  array $atts Shortcode-Attribute: team, mode (full|compact).
 * @return string      HTML.
 */
function fsw_spieltag_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '', 'mode' => 'full' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	$h    = '<div class="fsw-w fsw-full">';
	$h   .= fsw_next_sc( [ 'team' => $tid, 'style' => 'box' ] );
	$h   .= fsw_form_sc( [ 'team' => $tid, 'style' => 'box' ] );
	$h   .= ( $atts['mode'] === 'compact' )
		? fsw_last_sc(  [ 'team' => $tid, 'style' => 'box' ] )
		: fsw_table_sc( [ 'team' => $tid, 'style' => 'box' ] );
	$h   .= '</div>';
	return $h;
}
add_shortcode( 'fsw_spieltag', 'fsw_spieltag_sc' );


/* ================================================================
   [fsw_spielplan] – Alle nächsten Spiele des Vereins
   ================================================================ */

/**
 * Zeigt die nächsten Spiele aller Vereinsteams als Liste.
 *
 * @param  array $atts Shortcode-Attribute: anzahl, filter (junioren|senioren).
 * @return string      HTML.
 */
function fsw_sched_sc( $atts ) {
	$atts = shortcode_atts( [ 'anzahl' => 5, 'filter' => '' ], $atts );
	$d    = fsw_api( '/club/next_games/' . get_option( 'fsw_club_id', '' ) );
	if ( is_wp_error( $d ) ) return fsw_err( $d->get_error_message() );
	$gs = fsw_data( $d );

	// Optionaler Filter nach Altersgruppe (API-Feld: ageGroup, z.B. "Herren", "D-Junioren")
	if ( ! empty( $atts['filter'] ) ) {
		$f  = strtolower( $atts['filter'] );
		$gs = array_values( array_filter( $gs, function ( $g ) use ( $f ) {
			$age      = strtolower( trim( $g['ageGroup'] ?? '' ) );
			$is_youth = ( strpos( $age, 'junioren' ) !== false );
			return ( $f === 'junioren' ) ? $is_youth : ! $is_youth;
		} ) );
	}

	$gs = array_slice( $gs, 0, intval( $atts['anzahl'] ) );
	if ( empty( $gs ) ) return fsw_err( 'Keine Spiele.' );

	$badge = ( strtolower( $atts['filter'] ) === 'junioren' ) ? 'Nächste Jugendspiele' : 'Nächste Spiele';

	ob_start();
	?>
	<div class="fsw-w fsw-sched">
		<div class="fsw-hdr"><span class="fsw-badge"><?php echo esc_html( $badge ); ?></span></div>
		<ul class="fsw-sr-list">
		<?php foreach ( $gs as $g ) :
			$hm    = $g['homeTeam'] ?? '—';
			$aw    = $g['awayTeam'] ?? '—';
			$dt    = fsw_dt( $g['date'] ?? '', $g['time'] ?? '' );
			$lg    = $g['competition'] ?? '';
			// Team-Label ermitteln (z.B. "2. Mannschaft" für "Schalke II")
			$svh_n = fsw_hl( $hm ) ? $hm : ( fsw_hl( $aw ) ? $aw : '' );
			$label = fsw_team_label_for_game( $svh_n );
		?>
		<li class="fsw-sr">
			<div class="fsw-sd">
				<span class="fsw-swd"><?php echo esc_html( $dt['wd'] ); ?></span>
				<span class="fsw-sdd"><?php echo esc_html( $dt['d'] ); ?></span>
			</div>
			<div class="fsw-st-time"><?php echo esc_html( $dt['t'] ); ?></div>
			<div class="fsw-sm-match">
				<span class="<?php echo fsw_hl( $hm ) ? 'fsw-hl' : ''; ?>"><?php echo esc_html( $hm ); ?></span> –
				<span class="<?php echo fsw_hl( $aw ) ? 'fsw-hl' : ''; ?>"><?php echo esc_html( $aw ); ?></span>
				<?php if ( $label ) : ?>
				<span class="fsw-team-label"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $lg ) : ?><div class="fsw-slg"><?php echo esc_html( $lg ); ?></div><?php endif; ?>
		</li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'fsw_spielplan', 'fsw_sched_sc' );


/* ================================================================
   [fsw_banner_datum] + [fsw_banner_liga] – Banner-Texte
   ================================================================ */

/**
 * Gibt Datum und Uhrzeit des nächsten Spiels als Inline-Text zurück (z.B. für Hero-Banner).
 *
 * @param  array $atts Shortcode-Attribute: team.
 * @return string      HTML <span>.
 */
function fsw_banner_datum_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	if ( ! $tid ) return '';
	$d    = fsw_api( '/team/next_games/' . $tid );
	if ( is_wp_error( $d ) ) return '';
	$gs = fsw_data( $d );
	if ( empty( $gs ) ) return '';
	$g  = $gs[0];
	$dt = fsw_dt( $g['date'] ?? '', $g['time'] ?? '' );
	$wd = strtoupper( substr( $dt['wd'], 0, 2 ) ) . '.';
	return '<span class="fsw-txt-datum">' . esc_html( $wd . ' ' . $dt['d'] . ' - ' . $dt['t'] . ' Uhr' ) . '</span>';
}
add_shortcode( 'fsw_banner_datum', 'fsw_banner_datum_sc' );

/**
 * Gibt den Liga-Namen des nächsten Pflichtspiels als Inline-Text zurück.
 *
 * @param  array $atts Shortcode-Attribute: team.
 * @return string      HTML <span>.
 */
function fsw_banner_liga_sc( $atts ) {
	$atts = shortcode_atts( [ 'team' => '' ], $atts );
	$tid  = fsw_tid( $atts['team'] );
	if ( ! $tid ) return '';
	$d    = fsw_api( '/team/next_games/' . $tid );
	if ( is_wp_error( $d ) ) return '';
	$gs = fsw_data( $d );
	if ( empty( $gs ) ) return '';
	$g  = fsw_first_competitive_game( $gs );
	$lg = $g ? ( $g['competition'] ?? '' ) : '';
	if ( ! $lg ) return '';
	return '<span class="fsw-txt-liga">' . esc_html( $lg ) . '</span>';
}
add_shortcode( 'fsw_banner_liga', 'fsw_banner_liga_sc' );
