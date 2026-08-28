# Piraeus MyCity — City Issue Reporting Application

## Technologies

- HTML5, CSS3, Bootstrap 5.3
- JavaScript (vanilla)
- PHP 8.2 (mysqli, prepared statements)
- MySQL 8
- Leaflet.js + OpenStreetMap
- Nominatim API (geocoding)

## Running with Docker

From the `code/` folder:

    1. docker compose up --build (1st time)
    2. docker compose up (2nd+ time)

- Application: http://localhost:8080
- phpMyAdmin: http://localhost:8081

The database is created automatically on first startup,
from the file `../db/mycity.sql`.

Stopping:

    docker compose down (data is preserved)

Stopping with data deletion:

    docker compose down -v
    docker compose down -v --rmi all (deletes everything, including the build)
    !! Stored videos are never deleted automatically

## Running with XAMPP

1. Copy the folder to `htdocs/mycity`
2. Create the `mycity` database in phpMyAdmin
3. Import `db/mycity.sql`
4. Write permissions on the uploads folder: `chmod 777 uploads` (Linux/macOS only)
5. Enable `extension=curl` in php.ini
6. `upload_max_filesize = 25M` and `post_max_size = 30M` in php.ini
7. Open: http://localhost/mycity/

## Administrator login credentials

- Username: `admin`
- Password: `Admin1234`

### Adding a new administrator

    The following file must be created, and then opened in the browser:

    ---START---

        <?php
        require 'includes/db.php';

        $username  = 'xxx';
        $password  = 'xxx';
        $full_name = 'xxx';
        $email     = 'xxx';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn,
            "INSERT INTO admins (username, password_hash, full_name, email)
            VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'ssss', $username, $hash, $full_name, $email);

        mysqli_stmt_close($stmt);

    ---END---

    WARNING!!!
    1. The file must be deleted after the administrator is created.
    2. If run via docker, the command <docker compose down -v> will delete the administrator that was created

## File structure

    code/
      index.php          Interface 1 — Report submission
      browse.php         Interface 2 — View & search
      detail.php         Interface 3 — Report detail
      login.php          Administrator login
      admin.php          Interface 4 — Dashboard
      logout.php         Logout
      includes/
        db.php           Database connection
        header.php       Shared navbar
        footer.php       Shared footer with map
        functions.php    Geocoding, anonymous ID generation
        auth.php         Admin page protection
      assets/
        css/style.css
        js/
          report.js
          detail-map.js
      uploads/           Video storage

## Database

Name: `mycity`

- `categories` — problem categories with priority weight
- `issues` — the reports
- `admins` — administrator accounts

## External API

Nominatim (OpenStreetMap) for converting address to coordinates.
The call is made server-side via cURL, with a User-Agent header set
as required by the service. Limit: 1 request per second.

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
