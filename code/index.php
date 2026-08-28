<?php

$page_title = 'Αρχική';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'includes/db.php';
require 'includes/functions.php';

$errors=[];



//αν μόλις υποβλήθηκε επιτυχώς, παίρνουμε το ticket από το session
$success = $_SESSION['last_ticket'] ?? null;
unset($_SESSION['last_ticket']);



//κρατάμε τις τιμές ώστε να μη χαθούν αν αποτύχει η υποβολή
$old=[
  'title' => '',
  'category_id' => '',
  'address' => '',
  'description' => '',
  'submission_type' => '',
  'username' => ''
];


if ($_SERVER['REQUEST_METHOD']==='POST') {

  // ανάγνωση δεδομένων, αφαίρεση κενών με trim, ?? '' βάζει κενό string 
  $title= trim($_POST['title'] ?? '');
  $category_id= (int) ($_POST['category_id'] ?? 0); //μόνο int
  $address= trim($_POST['address'] ?? '');
  $description= trim($_POST['description'] ?? '');
  $subType= $_POST['submission_type'] ?? '';
  $username= trim($_POST['username'] ?? '');

  $old['title'] = $title;
  $old['category_id']= $category_id;
  $old['address'] = $address;
  $old['description'] = $description;
  $old['submission_type'] = $subType;
  $old['username'] = $username;

  //έλεγχος τίτλου
  $len = mb_strlen($title);
  if($len < 5 || $len > 100){
    $errors[]= 'Ο τίτλος πρέπει να έχει από 5 έως 100 χαρακτήρες';
  }


  
  // έλεγχος περιγραφής
  $len = mb_strlen($description); //mb_strlen για el
  if($len < 10 || $len > 1000){
    $errors[]= 'Η περιγραφή πρέπει να έχει από 10 έως 1000 χαρακτήρες';
  }

  //έλεγχος διεύθυνσης
  if(mb_strlen($address)<5){
    $errors[]= 'Η διεύθυνση είναι υποχρεωτική και πρέπει να είναι συγκεκριμένη';
  }

  //έλεγχος ότι υπάρχει στη βάση η κατηγορία (με prepared statement)
  $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM categories WHERE category_id = ?");
  mysqli_stmt_bind_param($stmt, 'i', $category_id); //σύνδεση μεταβλ.
  mysqli_stmt_execute($stmt); // τα δεδομένα φεύγουν ξεχωριστά
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res); //μετατροπή σε associative array
  mysqli_stmt_close($stmt);

  if ($row['c'] == 0) {
    $errors[] = 'Επιλέξτε έγκυρη κατηγορία.';
  }

  // τύπος υποβολής
  $isAnonymous = 0;
  $user = '';

  if ($subType === 'named') { //επώνυμη αναφοά

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
      $errors[] = 'Το username πρέπει να έχει από 3 έως 50 χαρακτήρες.';
    } else {

      $stmt = mysqli_prepare($conn,
          "SELECT COUNT(*) AS c FROM issues WHERE user = ? AND is_anonymous = 0"
      );
      mysqli_stmt_bind_param($stmt, 's', $username);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      $row = mysqli_fetch_assoc($res);
      mysqli_stmt_close($stmt);

      if ($row['c'] > 0) {
        $errors[] = 'Το username χρησιμοποιείται ήδη. Επιλέξτε άλλο.';
      } else {
        $user = $username;
        
      }

    }

  } elseif($subType === 'anonymous'){ //ανώνυμη αναφορά
    $isAnonymous = 1;
    $user = generateAnonymousId($conn); //χορήγηση τυχαίου username (functions.php)

  }else{
    $errors[] = 'Επιλέξτε τύπο υποβολής (επώνυμα ή ανώνυμα).';
  }

  //μετατροπή διεύθυνσης σε coordinates
  $lat = null;
  $lon = null;

  if (empty($errors)) {
    $coords = geocodeAddress($address); //από functions.php

    if ($coords === null) {
      $errors[] = 'Δεν βρέθηκαν συντεταγμένες για αυτή τη διεύθυνση. ' . 'Παρακαλώ διορθώστε τη διεύθυνση και δοκιμάστε ξανά';
    }else {
      $lat = $coords['lat'];
      $lon = $coords['lon'];
    }
  }

  //video upload
  $videoPath = null;

  if (empty($errors) && isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {

    $file= $_FILES['video'];
    $ext= strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); //extension
    $mime= mime_content_type($file['tmp_name']); //πραγματικά bytes αρχείου video/mp4 

    if ($file['size'] > 20 * 1024 * 1024) {
        $errors[] = 'Το video δεν πρέπει να ξεπερνά τα 20MB';

    } elseif ($ext !== 'mp4' || $mime !== 'video/mp4') {
        $errors[] = 'Επιτρέπονται μόνο αρχεία .mp4';

    } else { //αποθήκευση του video στον φάκελο uploads
        $newName= 'video_' . date('Ymd_His') . '_' . rand(1000, 9999) . '.mp4'; //αλλαγή ονόματος
        $target= 'uploads/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $target)){
          $videoPath= $target; //μετακινεί το αρχείο από την προσωρινή θέση (tmp name) στην τελική, έπειτα το διαγράφει
        } else{
          $errors[]= 'Σφάλμα κατά την αποθήκευση του video';
        }
    }
  }

  //καταχώρηση στη βάση
  if (empty($errors)) { // αν δεν υπάρχει κανένα error

    //βρίσκει το weight απο το πίνακα categories  
    $stmt = mysqli_prepare($conn, "SELECT weight FROM categories WHERE category_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $catRow = mysqli_fetch_assoc($res); //πρώτη γραμμή αποτελέσματος -> associative array
    mysqli_stmt_close($stmt);

    $weight = $catRow['weight']; //αποθήκευση του weight

    //υπολογισμός priority score
    $score = ($weight * 2) + 5;

    if ($score >= 14) {
        $priority = 'Κρίσιμη';
    } elseif ($score >= 11) {
        $priority = 'Υψηλή';
    } elseif ($score >= 9) {
        $priority = 'Μεσαία';
    } else {
        $priority = 'Χαμηλή';
    }

    // sql insert
    $sql = "INSERT INTO issues
            (title, category_id, description, address, latitude, longitude,
              video_path, user, is_anonymous, priority)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt,
                            'sissddssis',
                            $title, $category_id, $description, $address,
                            $lat, $lon, $videoPath, $user, $isAnonymous, $priority);

    if (mysqli_stmt_execute($stmt)){ //αν η καταχώρηση έγινε επιτυχώς

      //παραγωγή Ticket ID από το AUTO_INCREMENT
      $newId = mysqli_insert_id($conn); //το id σκέτο
      $ticket = 'CR-' . str_pad($newId, 5, '0', STR_PAD_LEFT); //το id σε μορφή CR-XXXXX

      $upd = mysqli_prepare($conn, "UPDATE issues SET ticket_id = ? WHERE issue_id = ?");
      mysqli_stmt_bind_param($upd, 'si', $ticket, $newId);
      mysqli_stmt_execute($upd);
      mysqli_stmt_close($upd);

      //κρατάμε το ticket id (cookie)
      $existing = $_COOKIE['my_tickets'] ?? '';

      //αν υπάρχει ticket, το βάζουμε σε λίστα
      if ($existing === '') {
          $list = [];
      } else {
          $list = explode(',', $existing); // 'CR-00001,CR-00002,CR-00003' -> ['CR-00001', 'CR-00002', 'CR-00003']
      }

      $list[] = $ticket;
      setcookie('my_tickets', implode(',', $list), time() + 60*60*24*365, '/'); //λήξη σε ένα χρόνο
                                                                                //είναι διαθέσιμο σε όλο το site (/)

      // αποθήκευση του ticket στο session και ανακατεύθυνση στο home page
      $_SESSION['last_ticket'] = $ticket;
      header('Location: index.php');
      exit;

    } else {
        $errors[] = 'Προέκυψε σφάλμα κατά την καταχώρηση. Παρακαλούμε δοκιμάστε ξανά αργότερα.';
    }

      mysqli_stmt_close($stmt); //κλείσιμο php
  }
}

//κατηγορίες dropdown
$categories = mysqli_query($conn, "SELECT category_id, name FROM categories");

require 'includes/header.php'; //να περιλαμβάνει το header
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-8">

    <!-- εισαγωγή -->
    <div class="hero-section text-center mb-4">
      <h1 class="h2 mb-3">Δες το - Πες το, με το Piraeus MyCity app</h1>
      <p class="lead mb-0">
      Πόσες κακοτεχνίες συναντάς καθημερινά στον δρόμο σου; 
      Λακκούβες, χαλασμένα φανάρια και σπασμένα πεζοδρόμια είναι 
      μόνο ορισμένα από αυτά που κάνουν τη ζωή μας δύσκολη και 
      ενίοτε επικίνδυνη. Με το Piraeus MyCity app, βλέπεις το 
      πρόβλημα και το αναφέρεις στη στιγμή!
      </p>
    </div>

    <div class="row g-3 mb-5 text-center">
      <div class="col-12 col-md-4">
        <div class="step-box h-100">
          <div class="step-number">1</div>
          <h2 class="h6 mb-1">Περίγραψε</h2>
          <p class="small text-muted mb-0">
            Συμπλήρωσε τη φόρμα με τα στοιχεία του προβλήματος
          </p>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="step-box h-100">
          <div class="step-number">2</div>
          <h2 class="h6 mb-1">Λάβε κωδικό</h2>
          <p class="small text-muted mb-0">
            Θα σου δοθεί μοναδικός κωδικός παρακολούθησης
          </p>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="step-box h-100">
          <div class="step-number">3</div>
          <h2 class="h6 mb-1">Παρακολούθησε</h2>
          <p class="small text-muted mb-0">
            Δες ανά πάσα στιγμή την πορεία του αιτήματός σου
          </p>
        </div>
      </div>
    </div>

    <h2 class="h4 mb-1">Φόρμα Αναφοράς</h2>
    <p class="text-muted small mb-4">
      Τα πεδία με <span class="text-danger">*</span> είναι υποχρεωτικά.
      Δεν απαιτείται εγγραφή. Μπορείτε να υποβάλετε επώνυμα ή ανώνυμα.
    </p>

    <?php if ($success): ?> <!-- επιτυχής υποβολή φόρμας -->
      <div class="alert alert-success">
        <h5 class="alert-heading">Η αναφορά υποβλήθηκε με επιτυχία!</h5>
        <p class="mb-2">Ο κωδικός παρακολούθησης της αναφοράς σας:</p>
        <p class="fs-4 fw-bold mb-2"><?php echo htmlspecialchars($success); ?></p>
        <p class="mb-0 small">
          Κρατήστε τον για να παρακολουθείτε την πορεία του αιτήματός σας.
          <a href="browse.php" class="alert-link">Προβολή όλων των αναφορών</a>
        </p>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <strong>Παρουσιάστηκαν τα εξής προβλήματα:</strong>
        <ul class="mb-0 mt-2">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- φόρμα αναφοράς -->
    <form action="index.php" method="POST" enctype="multipart/form-data" id="reportForm" novalidate>

      <!-- τίτλος -->
      <div class="mb-3">
        <label for="title" class="form-label">
          Τίτλος Προβλήματος <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="title" name="title"
               minlength="5" maxlength="100" required
               value="<?php echo htmlspecialchars($old['title']); ?>"
               placeholder="λ.χ. Λακκούβα στην είσοδο του Πανεπιστημίου Πειραιώς">
        <div class="invalid-feedback" id="titleError"></div>
        <div class="form-text"><span id="titleCount">0</span>/100 χαρακτήρες</div>
      </div>

      <!-- κατηγορία -->
      <div class="mb-3">
        <label for="category_id" class="form-label">
          Κατηγορία <span class="text-danger">*</span>
        </label>
        <select class="form-select" id="category_id" name="category_id" required>
          <option value="">-- Επιλέξτε κατηγορία --</option>
          <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <option value="<?php echo $cat['category_id']; ?>"
              <?php if ((string)$old['category_id'] === (string)$cat['category_id']) echo 'selected'; ?>>
              <?php echo htmlspecialchars($cat['name']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        <div class="invalid-feedback" id="categoryError"></div>
      </div>

      <!-- διεύθυνση -->
      <div class="mb-3">
        <label for="address" class="form-label">
          Πλήρης Διεύθυνση <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="address" name="address" required
               value="<?php echo htmlspecialchars($old['address']); ?>"
               placeholder="λ.χ. Καραολή και Δημητρίου 80, Πειραιάς">
        <div class="invalid-feedback" id="addressError"></div>
      </div>

      <!-- περιγραφή -->
      <div class="mb-3">
        <label for="description" class="form-label">
          Περιγραφή <span class="text-danger">*</span>
        </label>
        <textarea class="form-control" id="description" name="description"
                  rows="5" minlength="10" maxlength="1000" required
                  placeholder="Περιγράψτε αναλυτικά το πρόβλημα..."><?php echo htmlspecialchars($old['description']); ?></textarea>
        <div class="invalid-feedback" id="descriptionError"></div>
        <div class="form-text"><span id="descCount">0</span>/1000 χαρακτήρες</div>
      </div>

      <!-- τύπος υποβολής -->
      <div class="mb-3">
        <label class="form-label">Τύπος Υποβολής <span class="text-danger">*</span></label>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="submission_type"
                 id="typeNamed" value="named"
                 <?php if ($old['submission_type'] === 'named') echo 'checked'; ?>>
          <label class="form-check-label" for="typeNamed">
            Επώνυμα: θα εμφανίζεται το username μου
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="submission_type"
                 id="typeAnon" value="anonymous"
                 <?php if ($old['submission_type'] === 'anonymous') echo 'checked'; ?>>
          <label class="form-check-label" for="typeAnon">
            Ανώνυμα: θα μου ανατεθεί τυχαίο αναγνωριστικό
          </label>
        </div>

        <div id="typeError" class="text-danger small mt-1 d-none">
          Επιλέξτε τύπο υποβολής
        </div>

        <!-- εμφάνιση μόνο για επώνυμη υποβολή -->
        <div class="mt-2 <?php if ($old['submission_type'] !== 'named') echo 'd-none'; ?>"
             id="usernameWrapper">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username"
                 maxlength="50"
                 value="<?php echo htmlspecialchars($old['username']); ?>"
                 placeholder="Επιλέξτε ένα μοναδικό username">
          <div class="invalid-feedback" id="usernameError"></div>
        </div>
      </div>

      <!-- video -->
      <div class="mb-4">
        <label for="video" class="form-label">Video</label>
        <input type="file" class="form-control" id="video" name="video" accept="video/mp4">
        <div class="invalid-feedback" id="videoError"></div>
        <div class="form-text">Μόνο αρχεία .mp4, μέγιστο μέγεθος 20MB.</div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
        Υποβολή Αναφοράς
      </button>

    </form>

  </div>
</div>

<script src="assets/js/report.js"></script>

<?php require 'includes/footer.php'; ?>