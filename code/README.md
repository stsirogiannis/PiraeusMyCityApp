# Piraeus MyCity — Εφαρμογή Αναφοράς Προβλημάτων Πόλης

Διαδικτυακή εφαρμογή για την υποβολή και διαχείριση αναφορών
προβλημάτων πόλης, στο πλαίσιο του μαθήματος «Διαδικτυακά και
Φορητά Πληροφοριακά Συστήματα».

## Τεχνολογίες

- HTML5, CSS3, Bootstrap 5.3
- JavaScript (vanilla)
- PHP 8.2 (mysqli, prepared statements)
- MySQL 8
- Leaflet.js + OpenStreetMap
- Nominatim API (geocoding)

## Εκτέλεση με Docker

Από τον φάκελο `code/`:

    1. docker compose up --build (1η φορά)
    2. docker compose up (2+ φορές)

- Εφαρμογή: http://localhost:8080
- phpMyAdmin: http://localhost:8081

Η βάση δεδομένων δημιουργείται αυτόματα κατά την πρώτη εκκίνηση,
από το αρχείο `../db/mycity.sql`.

Σταμάτημα:

    docker compose down (τα δεδομένα σώζονται)

Σταμάτημα με διαγραφή δεδομένων:

    docker compose down -v
    docker compose down -v --rmi all (διαγραφή όλων, συμπεριλαμβανομένου και του build)
    !! Τα αποθηκευμένα βίντεο δεν διαγράφονται ποτέ αυτόματα

## Εκτέλεση με XAMPP

1. Αντιγραφή του φακέλου στο `htdocs/mycity`
2. Δημιουργία βάσης `mycity` στο phpMyAdmin
3. Εισαγωγή του `db/mycity.sql`
4. Δικαιώματα εγγραφής στον φάκελο uploads: `chmod 777 uploads` (μόνο για Linux/macOS)
5. Ενεργοποίηση `extension=curl` στο php.ini
6. `upload_max_filesize = 25M` και `post_max_size = 30M` στο php.ini
7. Άνοιγμα: http://localhost/mycity/

## Στοιχεία σύνδεσης διαχειριστή

- Username: `admin`
- Password: `Admin1234`

### Προσθήκη νέου διαχειριστή

    Πρέπει να δημιουργηθεί το παρακάτω αρχείο, και έπειτα να ανοιχτεί στον browser:

    ---ΑΡΧΗ---

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

    ---ΤΕΛΟΣ---

    ΠΡΟΣΟΧΗ!!!
    1. Το αρχείο θα πρέπει να διαγραφεί αφού δημιουργηθέι ο διαχειριστής.
    2. Σε περίπτωση που εκτελεστεί από το docker η εντολή <docker compose down -v> θα διαγραφεί ο διαχειριστής που δημιουργήθηκε

## Δομή αρχείων

    code/
      index.php          Interface 1 — Υποβολή αναφοράς
      browse.php         Interface 2 — Προβολή & αναζήτηση
      detail.php         Interface 3 — Λεπτομέρεια αναφοράς
      login.php          Σύνδεση διαχειριστή
      admin.php          Interface 4 — Dashboard
      logout.php         Αποσύνδεση
      includes/
        db.php           Σύνδεση με τη βάση
        header.php       Κοινό navbar
        footer.php       Κοινό footer με χάρτη
        functions.php    Geocoding, παραγωγή anon ID
        auth.php         Προστασία σελίδων διαχειριστή
      assets/
        css/style.css
        js/
          report.js
          detail-map.js
      uploads/           Αποθήκευση video

## Βάση δεδομένων

Όνομα: `mycity`

- `categories` — κατηγορίες προβλημάτων με βάρος προτεραιότητας
- `issues` — οι αναφορές
- `admins` — λογαριασμοί διαχειριστών

## Εξωτερικό API

Nominatim (OpenStreetMap) για μετατροπή διεύθυνσης σε συντεταγμένες.
Η κλήση γίνεται server-side μέσω cURL, με ορισμό User-Agent header
όπως απαιτεί η υπηρεσία. Όριο: 1 αίτημα ανά δευτερόλεπτο.
