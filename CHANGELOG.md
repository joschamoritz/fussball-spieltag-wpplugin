# Changelog – Fussball Spieltag Widget

Alle wesentlichen Änderungen an diesem Plugin werden hier dokumentiert.
Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

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
