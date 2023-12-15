# AMS
 Aufgaben Management System

# Intro
Das Aufgaben Management System ist eine Test-Aufgabe mit dem Ziel, eine simple Laravel REST-API zu bauen mit den folgenden Anforderungen:
* Datenbank Migrationen
* Aufgaben Model und Controller
* Authentifizierung (Middleware)
* Testing
* Dokumentation

# Installation
## Voraussetzungen
* PHP 8.1+
* Composer
* Git
* MySQL Datenbank

## Aufsetzen des Projekts
1. Repository klonen
Als erster Schritt sollte der git clone Befehl in einem leeren Ordner ausgeführt werden. 
Der vollständige Befehl hierfür lautet: `git clone https://github.com/TempJannik/AMS.git`
Jetzt sollte der Projektinhalt heruntergeladen sein.

2. Datenbank konfigurieren
In der .env Datei wird definiert, wie auf die Datenbank zugegriffen wird. Um eine .env Datei einzurichten kann die `.env.example` kopiert und in `.env` umbennant werden. Aktuell wird über 127.0.0.1:3306 mit dem Benutzernamen `root` ohne Passwort zugegriffen. Wenn die Datenbank noch nicht existiert kann sie mit folgendem SQL Befehl erstellt werden `CREATE DATABASE smake;` anschließend muss der Name der Datenbank (DB_DATABASE) entsprechend angepasst werden.

3. Packages installieren
Mit dem Befehl `composer install` werden alle nötigen Pakete installiert.

4. Migrationen ausführen
Um die Tabellen für das Projekt zu erstellen, muss eine Migrationsbefehl ausgeführt werden.
`php artisan migrate`
Wenn hier ein Fehler kommt, ist vermutlich die Konfiguration der Datenbankverbindung fehlerhaft.

5. Datenbank mit Beispieldaten füllen
Der Seeder ist mit dem Befehl `php artisan db:seed` auszuführen. Alternativ kann bei Schritt 4. das --seed Argument mitgegeben werden. Mit dem Seeder wird eine Admin Rolle, Benutzer, Projekte und Aufgaben automatisch erstellt.

6. APP_KEY setzen
Damit der APP_KEY in der .env für die Umgebung neu gesetzt wird kann der Befehl `php artisan key:generate` benutzt werden.

7. Applikation starten
Um in der lokalen Umgebung ein Webserver zu starten über welchen die API läuft wird der Befehl `php artisan serve` benutzt. Dieser startet ein Webserver welcher standardmäßig über http://127.0.0.1:8000 die Applikation bereitstellt.

8. Tests ausführen
Die Tests sind mit der PHPUnit Library umgesetzt. Ausgeführt werden können diese mit `php artisan test`
Mit dem filter Argument ist es auch möglich einzelne Test Klassen auszuführen. Beispiel: `php artisan test --filter=TaskApiTest`

9. Deployment
`php artisan serve` ist nur ein lokaler Webserver. Für eine produktive Umgebung wäre ein umfangreicher Webserver wie Apache oder Nginx geeignet.
Zusätzlich müssen in der .env Datei die `APP_ENV=local` auf `APP_ENV=production` und `APP_DEBUG=true` auf `APP_DEBUG=false` geändert werden.
Für das Caching wird empfohlen den Standard File-Driver mit einem für die Produktion geeigneten Driver wie Redis zu ersetzen.
Ein Mail Server muss auch konfiguriert werden, ansonsten werden Email Notifications nur in die Log Datei geschrieben.

## Authentication
Authentifizierung wird über [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum)
Aktuell laufen die erstellten Tokens nie aus. Alte tokens werden aber bei jedem Login gelöscht, somit hat jeder Benutzer jeweils ein Token.

## API Reference

Alle task-Endpoints brauchen einen Bearer API-Token als Authentifizierungsmethode. Wenn dieser nicht im Header gegeben wird, werden alle Requests zurückgewiesen.

### Insomnia

Lokales Testing wurde mit [Insomnia](https://insomnia.rest) durchgeführt. Um schneller an das Testing zu kommen kann die `Insomnia_2023-12-13.json` oder `Insomnia_2023-12-13.har` aus dem Projekt-Root Verzeichnis importiert werden. Dort liegen Beispiel Requests für alle Endpoints.

### Status Codes

Die REST-API wird folgende Status Codes ausgeben:
| Status Code | Description | Reason
| :--- | :--- | :--- |
| 200 | `OK` | Request was successful |
| 201 | `CREATED` | A resource was created successfully |
| 204 | `NO CONTENT` | Request was successful, there is not further content to show |
| 401 | `UNAUTHORIZED` | There was no or an incorrect Auth Token provided |
| 403 | `FORBIDDEN` | An Auth token was provided but you are not allowed to perform the action |
| 404 | `NOT FOUND` | The route was not found |
| 422 | `UNPROCESSABLE ENTITY` | The provided input data has errors or does not match validation requirements |
| 500 | `INTERNAL SERVER ERROR` | An internal error occured |

#### Login with Email/Password

```http
  GET /api/login
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Required**. Your Email |
| `password` | `string` | **Required**. Your plaintext Password |

#### Register an account

```http
  GET /api/register
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Required**. Your Email |
| `password` | `string` | **Required**. Your plaintext Password |
| `name` | `string` | **Required**. Your account Name |

#### Get specific tasks

```http
  GET /api/tasks/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |

#### Get list of tasks

```http
  GET /api/tasks/
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |

#### Get list of overdue tasks

```http
  GET /api/tasks/past-deadline
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |


#### Create task

```http
  POST /api/tasks/
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `title` | `string` | **Required**. Title of the task |
| `description` | `string` | **Required**. Title of the task |
| `title` | `enum` | **Required**. Status of the Task, can be todo, in_progress or done|
| `deadline` | `datetime` | The deadline of a task in the Y-m-d H:m:s format|

#### Update task

```http
  PUT /api/tasks/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `title` | `string` | **Required**. Title of the task |
| `description` | `string` | **Required**. Title of the task |
| `title` | `enum` | **Required**. Status of the Task, can be todo, in_progress or done|
| `deadline` | `datetime` | The deadline of a task in the Y-m-d H:m:s format|

#### Delete task

```http
  DELETE /api/tasks/${id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |

#### Get list of a users tasks

```http
  GET /api/users/{user_id}/tasks
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |

#### Get list of a projects tasks

```http
  GET /api/projects/{project_id}/tasks
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
