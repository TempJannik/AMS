# AMS - Aufgaben Management System

## Intro
Das Aufgaben Management System ist eine Testaufgabe mit dem Ziel, eine simple Laravel REST-API zu bauen, mit den folgenden Anforderungen:
* Datenbank-Migrationen
* Aufgaben-Model und Controller
* Authentifizierung (Middleware)
* Testing
* Dokumentation

## Installation
### Voraussetzungen
* PHP 8.1+
* Composer
* Git
* MySQL-Datenbank

## Aufsetzen des Projekts
1. Repository klonen
Als erster Schritt sollte der git clone Befehl in einem leeren Ordner ausgeführt werden. 
Der vollständige Befehl hierfür lautet: `git clone https://github.com/TempJannik/AMS.git`
Jetzt sollte der Projektinhalt heruntergeladen sein.

2. Datenbank konfigurieren
In der `.env` Datei wird definiert, wie auf die Datenbank zugegriffen wird. Um eine `.env` Datei einzurichten, kann die `.env.example` kopiert und in `.env` umbenannt werden. Aktuell wird über 127.0.0.1:3306 mit dem Benutzernamen `root` ohne Passwort zugegriffen. Wenn die Datenbank noch nicht existiert kann sie mit folgendem SQL Befehl erstellt werden `CREATE DATABASE smake;`. Anschließend muss der Name der Datenbank (DB_DATABASE) entsprechend angepasst werden.
Für das Testing wird eine andere Datenbank `testing` benötigt. Entsprechend muss hierfür auch der Befehl `CREATE DATABASE testing;` ausgeführt werden.

3. Packages installieren
Mit dem Befehl `composer install` werden alle nötigen Pakete installiert.

4. Migrationen ausführen
Um die Tabellen für das Projekt zu erstellen, muss eine Migrationsbefehl ausgeführt werden.
`php artisan migrate`
Wenn hier ein Fehler auftritt, ist vermutlich die Konfiguration der Datenbankverbindung fehlerhaft.

5. Datenbank mit Beispieldaten füllen
Der Seeder ist mit dem Befehl `php artisan db:seed` auszuführen. Alternativ kann bei Schritt 4. das --seed Argument mitgegeben werden. Mit dem Seeder wird eine Admin Rolle, Benutzer, Projekte und Aufgaben automatisch erstellt.

6. APP_KEY setzen
Damit der APP_KEY in der .env für die Umgebung neu gesetzt wird kann der Befehl `php artisan key:generate` benutzt werden.

7. Applikation starten
Um in der lokalen Umgebung ein Webserver zu starten über welchen die API läuft wird der Befehl `php artisan serve` benutzt. Dieser startet ein Webserver welcher standardmäßig über http://127.0.0.1:8000 die Applikation bereitstellt.

8. Tests ausführen
Die Tests sind mit der PHPUnit Library umgesetzt. Ausgeführt werden können diese mit `php artisan test`
Mit dem filter Argument ist es auch möglich einzelne Test Klassen auszuführen. Beispiel: `php artisan test --filter=TaskApiTest`
Tests werden auf einer separaten Datenbankverbindung ausgeführt. Diese Verbindung ist in der .env unter TESTING_DB_ konfigurierbar. Die Daten die zuvor durch bspw. ein `php artisan db:seed` eingespielt wurden bleiben entsprechend erhalten.

9. Deployment
`php artisan serve` ist nur ein lokaler Webserver. Für eine produktive Umgebung wäre ein umfangreicher Webserver wie Apache oder Nginx geeignet.
Zusätzlich müssen in der .env Datei die `APP_ENV=local` auf `APP_ENV=production` und `APP_DEBUG=true` auf `APP_DEBUG=false` geändert werden.
Für das Caching wird empfohlen den Standard File-Driver mit einem für die Produktion geeigneten Driver wie Redis zu ersetzen.
Ein Mail Server muss auch konfiguriert werden, ansonsten werden Email Notifications nur in die Log Datei geschrieben.

## Authentifizierung
Authentifizierung wird über [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum)
Aktuell laufen die erstellten Tokens nie aus. Alte tokens werden aber bei jedem Login gelöscht, somit hat jeder Benutzer jeweils ein Token.

## API Reference
Alle task-Endpoints brauchen einen Bearer API-Token als Authentifizierungsmethode. Wenn dieser nicht im Header gegeben wird, werden alle Requests zurückgewiesen.

### Insomnia
Lokales Testing wurde mit [Insomnia](https://insomnia.rest) durchgeführt. Um schneller an das Testing zu kommen kann die `Insomnia_2023-12-21.json` oder `Insomnia_2023-12-21.har` aus dem Projekt-Root Verzeichnis importiert werden. Dort gibt es Anfragen für alle Endpoints.

### Status Codes

Die REST-API wird folgende Status Codes ausgeben:
| Status Code | Beschreibung | Begründung
| :--- | :--- | :--- |
| 200 | `OK` | Anfrage war erfolgreich |
| 201 | `CREATED` | Eine Ressource wurde erfolgreich erstellt |
| 204 | `NO CONTENT` | Anfrage war erfolgreich, es gibt keinen weiteren Inhalt zum anzeigen |
| 401 | `UNAUTHORIZED` | Es wurde kein oder ein falscher Bearer Token angegeben |
| 403 | `FORBIDDEN` | Es wurde ein Bearer Token angegeben aber du darfst die Aktion nicht ausführen |
| 404 | `NOT FOUND` | Route wurde nicht gefunden |
| 422 | `UNPROCESSABLE ENTITY` | Die angegebenen Input Daten haben Fehler bzw. entsprechen nicht den Validierungs-Richtlinien |
| 500 | `INTERNAL SERVER ERROR` | Ein interner Fehler ist aufgetreten |

#### Login mit Email/Password

```http
  GET /api/login
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Verpflichtend**. Deine Email |
| `password` | `string` | **Verpflichtend**. Dein Passwort |

#### Account registrieren

```http
  GET /api/register
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Verpflichtend**. Deine Email |
| `password` | `string` | **Verpflichtend**. Dein Passwort |
| `name` | `string` | **Verpflichtend**. Dein Account Name |

#### Spezifische Aufgabe abrufen

```http
  GET /api/tasks/${id}
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |

#### Liste an Aufgaben abrufen

```http
  GET /api/tasks/
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |

#### Liste an überfälligen Aufgaben aufrufen

```http
  GET /api/tasks/past-deadline
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |


#### Aufgabe erstellen

```http
  POST /api/tasks/
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |
| `title` | `string` | **Verpflichtend**. Titel der Aufgabe |
| `description` | `string` | **Verpflichtend**. Beschreibung der Aufgabe |
| `title` | `enum` | **Verpflichtend**. Status der Aufgabe, kann todo, in_progress oder done sein |
| `deadline` | `datetime` | Das Fälligkeitsdatum einer Aufgabe im Y-m-d H:m:s Format |

#### Aufgabe aktualisieren

```http
  PUT /api/tasks/${id}
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |
| `title` | `string` | **Verpflichtend**. Titel der Aufgabe |
| `description` | `string` | **Verpflichtend**. Beschreibung der Aufgabe |
| `title` | `enum` | **Verpflichtend**. Status der Aufgabe, kann todo, in_progress oder done sein |
| `deadline` | `datetime` | Das Fälligkeitsdatum einer Aufgabe im Y-m-d H:m:s Format |

#### Aufgabe löschen

```http
  DELETE /api/tasks/${id}
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |

#### Liste an Aufgaben eines Benutzers abrufen

```http
  GET /api/users/{user_id}/tasks
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |

#### Liste an Aufgaben eines Projekts abrufen

```http
  GET /api/projects/{project_id}/tasks
```

| Parameter | Typ     | Beschreibung                |
| :-------- | :------- | :-------------------------------- |
