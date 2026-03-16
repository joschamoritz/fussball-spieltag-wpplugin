# ⚽ Fussball Spieltag Widget

Ein WordPress-Plugin für Fußballvereine, die ihre Spieltagsdaten von **fussball.de** direkt auf der Vereinswebsite einbinden möchten – als Shortcodes, ohne Programmierkenntnisse.

**Nächstes Spiel, letztes Ergebnis, Formkurve, Tabellenausschnitt, Spielplan** – alles automatisch aktuell, direkt aus den offiziellen fussball.de-Daten.

---

## Screenshots

> *(folgen nach dem ersten Release)*

---

## Voraussetzungen

- WordPress 5.0 oder neuer
- PHP 7.4 oder neuer
- Kostenlosen API-Token von [api-fussball.de](https://api-fussball.de/token) (kostenlos, sofort verfügbar)
- Euer Verein muss auf [fussball.de](https://www.fussball.de) gelistet sein

---

## Installation

### Option A – ZIP hochladen (empfohlen)

1. Auf der [Releases-Seite](https://github.com/joschamoritz/fussball-spieltag-wpplugin/releases) die aktuelle ZIP-Datei herunterladen
2. Im WordPress-Backend: **Plugins → Neu hinzufügen → Plugin hochladen**
3. ZIP auswählen und installieren
4. Plugin aktivieren

### Option B – Manuell per FTP

1. Dieses Repository als ZIP herunterladen oder klonen
2. Den Ordner `fussball-spieltag/` in `/wp-content/plugins/` hochladen
3. Im WordPress-Backend unter **Plugins** aktivieren

---

## Einrichtung

### Schritt 1: API-Token besorgen

1. Auf [api-fussball.de/token](https://api-fussball.de/token) registrieren
2. Den erhaltenen Token kopieren
3. Im WordPress-Backend: **Einstellungen → Spieltag Widget**
4. Token unter **API-Token** eintragen

### Schritt 2: Club-ID eures Vereins herausfinden

1. Euren Verein auf [fussball.de](https://www.fussball.de) suchen und die Vereinsseite öffnen
2. Die URL der Vereinsseite sieht so aus:
   ```
   https://www.fussball.de/verein/vereinsname/XXXXXXXXXXXXXXXXXXXXXXXXXX/
   ```
3. Den langen Code am Ende (die **Club-ID**) kopieren
4. Im Plugin unter **Club-ID** eintragen

### Schritt 3: Team-IDs der Mannschaften herausfinden

Für jede Mannschaft (1. Mannschaft, 2. Mannschaft, Jugend…) braucht ihr eine eigene Team-ID:

1. Auf fussball.de die Mannschaftsseite öffnen (z.B. „1. Herren")
2. Die URL sieht so aus:
   ```
   https://www.fussball.de/mannschaft/…/XXXXXXXXXXXXXXXXXXXXXXXXXX/
   ```
3. Den langen Code am Ende (die **Team-ID**) kopieren
4. Im Plugin unter **Mannschaften** eintragen – Name vergeben und Team-ID einfügen

### Schritt 4: Vereinsname eintragen

Unter **Vereinsname (Suchbegriff)** ein eindeutiges Wort aus eurem Teamnamen eintragen, z.B.:
- `Hochlar` (für „SV Hochlar 28")
- `Gievenbeck` (für „SC Gievenbeck")
- `Schalke` (für „FC Schalke 04")

Das Plugin nutzt diesen Begriff, um euer Team in Spielen und Tabellen zu erkennen und hervorzuheben.

### Schritt 5: Shortcodes in Seiten einbauen

Jetzt könnt ihr die Shortcodes in beliebige Seiten, Beiträge oder Widget-Bereiche einfügen (siehe unten).

---

## Shortcodes

### Kombinierte Widgets

| Shortcode | Beschreibung |
|-----------|-------------|
| `[fsw_spieltag_tabs]` | **Startseiten-Widget:** Alle Mannschaften als Tabs, je Tab: Nächstes Spiel + Formkurve + Tabelle |
| `[fsw_spieltag]` | **Box:** Nächstes Spiel + Formkurve + Tabelle (eine Mannschaft) |
| `[fsw_spieltag mode="compact"]` | **Box:** Nächstes Spiel + Formkurve + Letztes Ergebnis |

### Einzelne Bausteine

Alle Bausteine sind transparent und können frei im Layout platziert werden.

| Shortcode | Beschreibung |
|-----------|-------------|
| `[fsw_naechstes_spiel]` | Nächstes Spiel: Vereinswappen, Teamnamen, Anstoßzeit |
| `[fsw_letztes_ergebnis]` | Letztes Ergebnis: gleiches Layout + farbiger Score (Sieg/Unentschieden/Niederlage) |
| `[fsw_formkurve]` | Letzte 5 Ligaspiele als Linien-Diagramm mit Gegner-Logos |
| `[fsw_formkurve style="dots"]` | Letzte 5 Spiele als einfache S/U/N-Punkte |
| `[fsw_tabelle]` | Tabellenausschnitt (5 Zeilen, zentriert um euer Team) |
| `[fsw_tabelle full="1"]` | Vollständige Ligatabelle |
| `[fsw_spielplan]` | Nächste 5 Spiele aller Vereinsteams als Liste |
| `[fsw_spielplan anzahl="10"]` | Nächste X Spiele (Anzahl anpassbar) |

### Banner-Texte (für Hero-Bereiche)

| Shortcode | Beschreibung |
|-----------|-------------|
| `[fsw_banner_datum]` | Datum + Uhrzeit des nächsten Spiels als formatierter Text |
| `[fsw_banner_liga]` | Liga-Name des nächsten Pflichtspiels |

### Parameter

Alle Shortcodes unterstützen folgende optionale Parameter:

| Parameter | Wert | Beschreibung |
|-----------|------|-------------|
| `team` | Team-ID | Andere Mannschaft anzeigen (statt der ersten in den Einstellungen) |
| `style` | `box` | Box-Stil mit dunkler Kopfzeile erzwingen |

**Beispiele:**
```
[fsw_naechstes_spiel team="011MIA6VDK000000VTVG0001VTR8C1K7"]
[fsw_tabelle style="box" rows="7"]
[fsw_spieltag_tabs jugend_url="/junioren/"]
[fsw_spielplan anzahl="8" filter="junioren"]
```

---

## Anpassung

### Vereinsfarben

Im WordPress-Backend unter **Einstellungen → Spieltag Widget → Darstellung** könnt ihr:

- **Primärfarbe** – Hauptfarbe eures Vereins (Teamname-Hervorhebung, Uhrzeiten, Tabs)
- **Akzentfarbe** – Zweite Farbe (aktive Tabs, Banner-Texte, Labels)

Hell- und Dunkel-Varianten werden automatisch aus der Primärfarbe abgeleitet.

### Eigenes Vereinslogo

Unter **Vereinseinstellungen → Eigenes Vereinslogo (URL)** könnt ihr die URL zu einem eigenen, hochauflösenden Logo eintragen. Dieses wird dann statt des fussball.de-Logos angezeigt.

### Google Fonts

Das Plugin lädt standardmäßig **Oswald** und **Source Sans 3** von Google Fonts. Falls euer Theme diese Schriften bereits lädt oder ihr andere verwenden möchtet, könnt ihr das Laden unter **Darstellung → Google Fonts laden** deaktivieren.

---

## FAQ

**Was ist api-fussball.de?**
Eine inoffizielle API, die Spieltag-Daten von fussball.de bereitstellt. Kein offizielles DFB-Produkt, aber für Amateurvereine gut geeignet.

**Kostet der API-Token etwas?**
Nein, die Registrierung und Nutzung ist kostenlos (Stand: 2025). Bitte die aktuellen Konditionen auf [api-fussball.de](https://api-fussball.de) prüfen.

**Wie oft aktualisieren sich die Daten?**
API-Antworten werden 30 Minuten gecacht. Danach holt das Plugin automatisch neue Daten. Den Cache könnt ihr unter **Einstellungen → Spieltag Widget → Cache leeren** manuell zurücksetzen.

**Funktioniert das Plugin mit meinem Theme?**
Das Plugin bringt sein eigenes CSS mit und funktioniert mit allen Standard-WordPress-Themes. Getestet mit OceanWP + Elementor Free. Bei Theme-Konflikten hilft oft das Deaktivieren der Google Fonts (s. oben).

**Kann ich mehrere Mannschaften anzeigen?**
Ja. Im `[fsw_spieltag_tabs]`-Shortcode werden alle als „Startseite" markierten Mannschaften als Tabs dargestellt. Einzelne Shortcodes können per `team="TEAM_ID"` auf eine bestimmte Mannschaft zeigen.

**Wie finde ich meine Club-ID / Team-ID?**
Auf [fussball.de](https://www.fussball.de) die Vereins- oder Mannschaftsseite aufrufen und den langen alphanumerischen Code am Ende der URL kopieren. Details in der Einrichtungsanleitung oben.

**Mein Verein ist nicht auf fussball.de – funktioniert das Plugin trotzdem?**
Nein. Das Plugin ist ausschließlich für Vereine ausgelegt, die auf fussball.de gelistet sind und dort ihre Spieltage einpflegen.

**Warum sehe ich nur „API-Token fehlt"?**
Ihr habt noch keinen Token unter **Einstellungen → Spieltag Widget** eingetragen. Token kostenlos auf [api-fussball.de/token](https://api-fussball.de/token) registrieren.

---

## Lizenz

GPL-2.0-or-later – Details in der Datei [LICENSE](LICENSE).

---

## Credits

Entwickelt von **joschamoritz** – ursprünglich für den [SV Hochlar 28 e.V.](https://www.hochlar28.de) als vereinsinternes Tool, jetzt als freies Plugin für alle deutschen Fußballvereine.
