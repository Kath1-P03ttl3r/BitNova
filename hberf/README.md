# CookingBit Recipe Website

Dieses Projekt ist eine einfache Rezept-Website mit Benutzer-Authentifizierung, Datenbankanbindung und PDF-Downloadfunktion.

## Features
- Login / Registrierung
- Rezepte suchen, filtern und anzeigen
- Eigene Rezepte hinzufügen
- Rezepte als PDF herunterladen
- SQLite-Datenbank mit Benutzer- und Rezept-Tabellen

## Ordnerstruktur
- `index.php` - Landing Page mit Login/Register-Option
- `login.php` / `register.php` / `logout.php` - Authentifizierung
- `dashboard.php` - Rezeptübersicht mit Such- und Filteroptionen
- `add_recipe.php` - Neues Rezept anlegen
- `detail.php` - Rezeptdetailseite mit PDF-Download
- `db.php` - Datenbankverbindung und Initialisierung
- `styles.css` - Design und Layout
- `scripts.js` - PDF-Generierung

## Starten
1. Stelle sicher, dass PHP auf deinem System installiert ist.
2. Öffne ein Terminal im Ordner `hberf`.
3. Starte den PHP-Server:

```bash
php -S localhost:8000
```

4. Öffne dann im Browser:

```
http://localhost:8000/index.php
```

## Datenbank
Die SQLite-Datenbank wird automatisch unter `data/cookingbit.db` erstellt, wenn du die Seite öffnest.


