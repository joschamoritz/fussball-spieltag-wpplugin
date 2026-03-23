# Changelog – Fussball Spieltag Widget

Alle wesentlichen Änderungen an diesem Plugin werden hier dokumentiert.
Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [5.4.0] – 2026-03-23

### Sicherheit & Korrektheit
- **Preconnect-Links** mit `esc_url()` escaped (WordPress Coding Standard)
- **`fsw_clear_cache()`** prüft jetzt Kontext: nur im Admin oder via `update_option_*`-Hook aufrufbar
- **Checkbox-Bug `fsw_load_fonts`**: Hidden-Input davor verhindert, dass Google Fonts nicht deaktiviert werden können
- **`fsw_load_fonts` Sanitization**: Erzwingt exakt `'1'` oder `'0'` statt beliebigem String

### Performance
- **Statisches Option-Caching**: `fsw_club_name_opt()` und `fsw_team_ids_opt()` vermeiden mehrfache `get_option()`-Aufrufe pro Request
- **Logo-Attachment-ID gespeichert**: `fsw_do_logo_download()` speichert `['id' => ..., 'url' => ...]` statt nur der URL – zuverlässigeres Löschen beim Deinstallieren

### Stabilität
- **Timezone-Fallback in `fsw_dt()`**: `strtotime()` ersetzt durch `DateTime` mit WordPress-Zeitzone
- **Post/Redirect/Get**: Cache-Leerung leitet nach Ausführung weiter – verhindert Doppelausführung bei Browser-Reload
- **Uninstall**: Versteht beide Logo-Formate (alt: URL-String, neu: Array mit ID)

---

## [5.3.0] – 2026-03-23

### Accessibility
- **[C1] ARIA-Tab-Pattern**: Tab-Widget hat jetzt vollständiges WAI-ARIA-Pattern (`role="tablist"`, `role="tab"`, `aria-selected`, `aria-controls`, `tabindex`, `role="tabpanel"`, `aria-labelledby`)
- **[C2] `role="alert"`**: Fehlermeldungen werden per `role="alert" aria-live="assertive"` von Screenreadern sofort vorgelesen
- **[I5] Formkurve Screenreader**: Unsichtbare Textzusammenfassung (`screen-reader-text`) der Formkurven-Ergebnisse für Screenreader
- **[I6] Score aria-label**: Score-Anzeige und Anstoßzeit haben beschreibendes `aria-label` (z.B. „Ergebnis: 2 zu 1", „Anstoß 15:00 Uhr")
- **[I4] Kontrast erhöht**: Opacity inaktiver Tabs und Labels von 0.55 auf 0.70 angehoben (WCAG AA)
- **[3.3] prefers-reduced-motion**: Animationen (Dots, Tabs) werden bei Systemeinstellung deaktiviert
- **[I2] Spielplan semantisch**: `<div class="fsw-sr">` → `<ul class="fsw-sr-list"><li>` für korrekte Listen-Semantik

### Korrektheit
- **[I1] WordPress-Zeitzone**: `date()` → `wp_date()` in allen Datum-/Zeit-Ausgaben – respektiert jetzt die in WP konfigurierte Zeitzone
- **[C3] Formkurve Logo-Clipping**: S/N-Punkte auf 5%/95% gesetzt statt 0%/100% – verhindert Abschneiden am Rand

### HTML-Standards
- **[C4] `display:flex` auf `<td>`**: Team-Zelle in der Tabelle enthält jetzt ein inneres `<div class="fsw-tc">` statt `flex` direkt auf `<td>` (Firefox-Kompatibilität)

### Performance
- **[I3] tabs.js bedingt laden**: Tab-Script wird nur noch auf Seiten eingebunden, die `[fsw_spieltag_tabs]` verwenden
- **[3.5] Eigenes Logo priorisiert**: Vereinslogo lädt mit `loading="eager" fetchpriority="high"` (LCP-Verbesserung)
- **[3.4] `decoding="async"`**: Alle Gegner-Logos und Tabellen-Logos entlasten den Haupt-Thread

### CSS
- **[3.2] `transition: all` entfernt**: Spezifische Properties statt `all` in Tab-Transitions
- **[3.7] `lang="de"`**: Tab-Widget-Container hat `lang="de"` für korrekte Screenreader-Aussprache

---

## [5.2.0] – 2026-03-16

### Sicherheit
- **[C1] SVG-Logos abgelehnt**: SVG-Dateien werden nicht mehr in die Mediathek heruntergeladen (potenzielles XSS)
- **[C1] WP_Filesystem**: Logo-Download schreibt jetzt via `WP_Filesystem` statt `file_put_contents` (Kompatibilität mit gehärteten Hosts)
- **[C2] MIME-Typ-Validierung**: Echter Dateiinhalt via `finfo` geprüft, nicht der HTTP-`Content-Type`-Header
- **[C3] Cache-Flush bei Token-Wechsel**: `update_option_fsw_api_token` löst automatisch `fsw_clear_cache()` aus
- **[I7] Separate Nonce-Parameter**: Cache-Leeren (`_fsw_nonce_clear`) und Debug (`_fsw_nonce_debug`) verwenden getrennte Parameter

### Performance & Stabilität
- **[I1] Response-Größen-Limit**: Logo-Downloads auf 512 KB begrenzt – kein RAM-Erschöpfungsrisiko mehr
- **[I2] Asynchrone Logo-Downloads**: Logos werden jetzt per WP-Cron im Hintergrund geladen, kein TTFB-Block mehr beim ersten Seitenaufruf
- **[I5] Input-Validierung**: `fsw_adjust_brightness()` und `fsw_hex_to_rgba()` prüfen Hex-Input via `ctype_xdigit()` vor der Verarbeitung

### Admin
- **[I6] Debug-Ansicht leert Cache nicht mehr**: Kein unbeabsichtigter Cache-Clear für Frontendbesucher beim Öffnen der Debug-Ansicht

### Shortcodes
- **[I3] Early Return bei fehlender Team-ID**: `[fsw_naechstes_spiel]` und `[fsw_tabelle]` zeigen sofort eine klare Fehlermeldung statt einen unnötigen API-Request zu machen

### Deinstallation
- **[I4] Mediathek aufräumen**: `uninstall.php` löscht jetzt auch die Mediathek-Anhänge (wp_posts-Einträge + physische Dateien) der gecachten Logos

---

## [5.1.0] – 2026-03-16

### Performance & Stabilität
- **In-Process-Cache** in `api.php`: Wiederholte API-Aufrufe innerhalb desselben Seitenaufrufs werden nicht mehr doppelt ausgeführt (statische Variable)
- **Negativer Transient-Cache** in `api.php`: API-Fehler (Verbindung, HTTP-Status, ungültiges JSON) werden 2 Minuten gecacht – verhindert Hammering bei Serverausfällen
- **Logo-Timeout** in `helpers.php`: Reduziert von 10 s auf 3 s – fehlerhafte Logo-Downloads blockieren das Frontend nicht mehr lange
- **Failed-Marker** für Logos: Fehlgeschlagene Logo-Downloads werden markiert (`__failed__`) – kein erneuter Versuch bis Cache geleert wird

### Admin
- **Auto Cache Flush**: API-Cache wird automatisch geleert wenn `fsw_team_ids` oder `fsw_club_id` im Backend geändert werden (kein manuelles Cache leeren nötig)

### Frontend (v5.0.x)
- Externes Tab-Script `assets/tabs.js` ersetzt Inline-`<script>` – Arrow-Key-Navigation, cachebar, mehrere Widgets pro Seite möglich
- Admin Color Picker mit Live-Preview via CSS Custom Properties
- `color-mix()` ersetzt durch server-seitig berechnete `rgba()`-Variablen (`--fsw-primary-faint`, `--fsw-primary-faint2`) für bessere Browser-Kompatibilität
- `width`/`height`-Attribute auf allen Logo- und Icon-`<img>`-Tags (CLS-Prävention)
- `.fsw-err` jetzt sichtbar als rote Fehlermeldung
- Tabellen-Scroll auf kleinen Bildschirmen (`overflow-x: auto`)
- `@media (max-width: 380px)`: U/Tore/Diff-Spalten ausgeblendet

---

## [5.0.0] – 2025

### Neu
- Plugin vollständig verallgemeinert – nutzbar für **jeden Verein auf fussball.de**
- Neuer Plugin-Name: „Fussball Spieltag Widget"
- Neuer Prefix: `fsw_` (Funktionen, Options, Transients, CSS-Klassen, Shortcodes)
- **Admin: Vereinseinstellungen** – Vereinsname (Suchbegriff), eigenes Logo
- **Admin: Farb-Einstellungen** – Color Picker für Primär- und Akzentfarbe
- Dynamische CSS-Custom-Properties über `wp_head` – Hell-/Dunkel-Variante wird automatisch abgeleitet
- **Google Fonts optional** – Admin-Checkbox zum Deaktivieren von Oswald + Source Sans 3
- Schalke 04 als Demo-Defaults beim Erstinstallieren (Club-ID + Team-ID vorausgefüllt)
- `uninstall.php` – räumt alle Plugin-Daten bei Deinstallation vollständig auf
- `fsw_adjust_brightness()` – Hilfsfunktion für automatische Farbableitung

### Geändert
- Alle Shortcodes von `[svh_...]` auf `[fsw_...]` umbenannt
- `svh_hl()` → `fsw_hl()` liest Vereinsname jetzt aus Admin-Option statt hardcoded
- Keine Hochlar-spezifischen Defaults mehr im Code

### Sicherheit
- Cache-Löschung und Debug-Ansicht mit WordPress-Nonces gegen CSRF abgesichert
- Alle SQL-Queries über `$wpdb->prepare()` + `$wpdb->esc_like()`
- Konsequentes Output-Escaping (`esc_html`, `esc_attr`, `esc_url`) in allen Shortcodes
- `ABSPATH`-Check in jeder Datei

---

## [4.0.0] – 2024

### Neu
- Modulare Dateistruktur: `helpers.php`, `api.php`, `admin.php`, `shortcodes.php`
- Transparente Bausteine – Shortcodes ohne festen Hintergrund, frei im Layout platzierbar
- `[svh_spieltag]` – kombinierte Box mit Nächstes Spiel + Formkurve + Tabelle
- `[svh_spieltag_tabs]` – Tab-Widget für Startseite mit allen Mannschaften
- Formkurve im Gievenbeck-Stil: SVG-Liniendiagramm mit Gegner-Logos als Punkte
- Shared `svh_render_match()` für einheitliches Spiel-Layout
- Logo-Caching in der WordPress-Mediathek (verhindert externe fussball.de-Requests)
- Pflichtspiel-Filter: Freundschaftsspiele werden grau dargestellt und aus der Formkurve ausgeblendet
- `[svh_spielplan filter="junioren"]` – Spielplan nach Altersgruppe filterbar

### Geändert
- CSS vollständig auf CSS Custom Properties umgestellt
- Responsive-Breakpoints bei 640px und 400px

---

## [3.x] – 2024

### Fixes & Verbesserungen
- Freundschaftsspiel-Erkennung robuster gemacht (erkennt: „Freundschaft", „Testspiel", „Turnier")
- Spielsortierung für vergangene Spiele (neuestes zuerst) korrigiert
- Logo-Kreis entfernt – Logos werden ohne Hintergrundkreis dargestellt
- Text-Fallback bei fehlendem Logo als Kreis mit Vereinsfarbe
- Tabellen-Ausschnitt: zentriert um das eigene Team, korrekte Randbehandlung
- Team-Label-Zuordnung (II/III/Jugend) in `[svh_spielplan]` verbessert
- Wochentage und Datumsformat konsistent auf Deutsch

---

## [2.2] – 2023

### Neu
- Erste stabile Version mit echten API-Daten von api-fussball.de
- Transient-Caching (30 Minuten) für alle API-Anfragen
- SSL-Fallback: automatischer Wechsel zwischen www und non-www bei SSL-Fehlern
- Admin-Seite mit API-Token, Club-ID, Team-IDs und Debug-Ansicht

---

## [1.0] – 2023

### Neu
- Initiale interne Version für den SV Hochlar 28 e.V.
- Shortcodes: Nächstes Spiel, Letztes Ergebnis, Formkurve (einfache Dots), Tabelle
- Hardcoded auf SV Hochlar 28 (Club-ID, Team-IDs, Vereinsfarben)
