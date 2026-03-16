# Changelog – Fussball Spieltag Widget

Alle wesentlichen Änderungen an diesem Plugin werden hier dokumentiert.
Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

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
