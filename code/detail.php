<?php
require 'includes/db.php';

//ποια αναφορά ζητήθηκε
$issue_id = (int) ($_GET['id'] ?? 0); //όπου id στο url, αν δεν υπάρχει βάζει 0 για error
                                      // με το int μετατρέπει τα πάντα σε ακέραιο

if ($issue_id <= 0) {
    header('Location: browse.php'); // ανακατέυθυνση στο browse.php αν δεν δωθεί αριθμός ή 0
    exit; // σταματάει το script
}

//sql query για άντληση δεδομένων από τη βάση
$sql = "SELECT i.*, c.name AS category_name
        FROM issues i
        JOIN categories c ON i.category_id = c.category_id /* το όνομα της κατηγορίας από το category id */
        WHERE i.issue_id = ?";

//prepared statement 
$stmt = mysqli_prepare($conn, $sql); //αποστολή δομή ερωτήματος
mysqli_stmt_bind_param($stmt, 'i', $issue_id); //συνδέει το issue id στο ?
mysqli_stmt_execute($stmt); // αποστολή δεδομένων ξεχωριστά
$res = mysqli_stmt_get_result($stmt); //παίρνει τα αποτελ
$issue = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// αν δεν βρεθει
// πλήρης σελίδα με το warning
if (!$issue) {
    $page_title = 'Η αναφορά δεν βρέθηκε';
    require 'includes/header.php';
    echo '<div class="alert alert-danger">
            <h5>Η αναφορά δεν βρέθηκε</h5>
            <p class="mb-0">Ελέγξτε το Ticket ID και δοκιμάστε ξανά
            <a href="browse.php" class="alert-link">Επιστροφή στη λίστα</a></p>
          </div>';
    require 'includes/footer.php';
    exit;
}

$page_title = $issue['title']; // ο τίτλος της αναφοράς είναι και ο τίτλος της καρτέλας του browser
require 'includes/header.php';

//χρώμα badge ανάλογα με την κατάσταση
$badgeClass = 'bg-primary'; //μπλε
if ($issue['status'] === 'Σε Εξέλιξη') $badgeClass = 'bg-warning text-dark'; //κίτρινο
if ($issue['status'] === 'Επιλύθηκε') $badgeClass = 'bg-success'; //πράσινο
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-9">

    <a href="browse.php" class="btn btn-sm btn-outline-secondary mb-3">
      &larr; Επιστροφή στη λίστα 
    </a>

    <div class="card mb-4">
      <div class="card-body">

        <!-- κατηγορία αριστερά & κατάσταση δεξιά --> 
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <span class="badge bg-secondary">
              <?php echo htmlspecialchars($issue['category_name']); ?>
            </span>
          </div>
          <span class="badge <?php echo $badgeClass; ?> fs-6">
            <?php echo htmlspecialchars($issue['status']); ?>
          </span>
        </div>

        <h1 class="h4 mb-3"><?php echo htmlspecialchars($issue['title']); ?></h1>

        <!-- πίνακας αναφοράς -->
        <table class="table table-sm">
          <tbody>
            <tr>
              <th scope="row" style="width: 180px;">Ticket ID</th>
              <td class="fw-bold"><?php echo htmlspecialchars($issue['ticket_id']); ?></td>
            </tr>
            <tr>
              <th scope="row">Ημερομηνία Υποβολής</th>
              <td><?php echo date('d/m/Y H:i', strtotime($issue['created_at'])); ?></td>
            </tr>
            <tr>
              <th scope="row">Διεύθυνση</th>
              <td><?php echo htmlspecialchars($issue['address']); ?></td>
            </tr>
            <tr>
              <th scope="row">Υποβλήθηκε από</th>
              <td>
                <?php if ($issue['is_anonymous'] == 1): ?>
                  <span class="text-muted">
                    Ανώνυμη αναφορά (<?php echo htmlspecialchars($issue['user']); ?>)
                  </span>
                <?php else: ?>
                  <strong><?php echo htmlspecialchars($issue['user']); ?></strong>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>

        <h2 class="h6 mt-4">Περιγραφή</h2>
        <p><?php echo nl2br(htmlspecialchars($issue['description'])); ?></p> <!-- μετατροπή /n σε <br> -->

      </div>
    </div>

    <!-- προβολή video αν υπάρχει-->
    <?php if (!empty($issue['video_path'])): ?> 
      <div class="card mb-4">
        <div class="card-body">
          <h2 class="h6 mb-3">Επισυναπτόμενο Video</h2>
          <video controls class="w-100" style="max-height: 400px;">
            <source src="<?php echo htmlspecialchars($issue['video_path']); ?>" type="video/mp4">
            Ο browser σας δεν υποστηρίζει την αναπαραγωγή video
          </video>
        </div>
      </div>
    <?php endif; ?>



    <!-- xάρτης -->
    <!-- αν έχει επιτύχει το geocoding -->
    <?php if ($issue['latitude'] !== null && $issue['longitude'] !== null): ?>
      <div class="card mb-4">
        <div class="card-body">
          <h2 class="h6 mb-3">Τοποθεσία Προβλήματος</h2>
          <div id="issueMap" style="height: 350px; border-radius: 6px;"></div>
        </div>
      </div>

      <!-- δημιουργία χάρτη -->
      <script>
        var issueLat= <?php echo $issue['latitude']; ?>;
        var issueLon= <?php echo $issue['longitude']; ?>;
        var issueTitle= "<?php echo htmlspecialchars($issue['title'], ENT_QUOTES); ?>"; // ent quotes γιατι βάζω εισαγωγικά js
        var issueAddr= "<?php echo htmlspecialchars($issue['address'], ENT_QUOTES); ?>";
      </script>
      <script src="assets/js/detail-map.js"></script> 
      <!-- detail-map.js για leaflet -->


    <?php endif; ?>

  </div>
</div>



<?php require 'includes/footer.php'; ?>