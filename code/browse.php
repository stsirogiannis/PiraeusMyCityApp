<?php
$page_title = 'Προβολή Αναφορών';
require 'includes/db.php';

//ανάγνωση φίλτρων από url 
$fCategory= $_GET['category'] ?? '';
$fStatus= $_GET['status'] ?? '';
$fUser= trim($_GET['user'] ?? '');
$fSort= $_GET['sort'] ?? 'newest';
$fTicket= trim($_GET['ticket'] ?? '');

//αναζήτηση με ticket id
if ($fTicket !== '') {

  //prepared statement για εισαγωγή του ticket id
  $stmt= mysqli_prepare($conn, "SELECT issue_id FROM issues WHERE ticket_id = ?");
  mysqli_stmt_bind_param($stmt, 's', $fTicket);
  mysqli_stmt_execute($stmt);
  $res= mysqli_stmt_get_result($stmt); //άντληση δεδομ
  $row= mysqli_fetch_assoc($res);// αποθήκευση δεδομ
  mysqli_stmt_close($stmt);

  if ($row) { //αν υπάρχει σχ. αναφορά
    header('Location: detail.php?id=' . $row['issue_id']); //απευεθείας μετάβαση στις λεπτομέρειες της αναφοράς του user
    exit;
  } else {
    $ticketError = 'Δεν βρέθηκε αναφορά με Ticket ID: ' . $fTicket;
  }
}

//με χρήση φίλτρων, γίνεται ερώτηση στη βάση
$sql = "SELECT i.issue_id, i.ticket_id, i.title, i.status, i.user,
               i.is_anonymous, i.created_at, c.name AS category_name
        FROM issues i /* από τον πίνακα issues */
        JOIN categories c ON i.category_id = c.category_id /* ταιριάζει το category id με την ονομασία του από τον πίνακα categories*/
        WHERE 1=1";    //  το 1=1 είναι πάντα αληθές οπότε δεν αλλάζει τα αποτελέσματα
                      // υπάρχει για να έχω ήδη ένα WHERE με μια συνθήκη μέσα, 
                      // ώστε κάθε φίλτρο να μπορεί να προστεθεί απλώς με AND

$params = [];
$types  = '';

if($fCategory !== ''){
    $sql .= " AND i.category_id = ?"; //προσθέτει στο query 
    $params[] = (int) $fCategory; //προσθήκη κατηγορίας
    $types .= 'i'; //τύπος μεταβλ
}

if($fStatus !== ''){
    $sql .= " AND i.status = ?";
    $params[] = $fStatus; //προσθήκη του status στον πινακα params
    $types .= 's';
}

if($fUser !== ''){
    $sql .= " AND i.user LIKE ? AND i.is_anonymous = 0"; //μερική ταύτιση με LIKE και %
    $params[] = '%' . $fUser . '%';
    $types .= 's';
}

//ταξινόμηση 
if($fSort === 'oldest'){
    $sql .= " ORDER BY i.created_at ASC";
} else {
    $sql .= " ORDER BY i.created_at DESC";
}

$stmt = mysqli_prepare($conn, $sql); //σύνδεση με βάση

if(count($params) === 1){ //αν έχει 1 όρισμα κ.ο.κ. (οι τιμές ξεχωριστά)
    mysqli_stmt_bind_param($stmt, $types, $params[0]);
} elseif (count($params) === 2){
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1]);
}elseif (count($params) === 3){
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1], $params[2]);
}

//έπειτα κάνει κλήση π.χ. mysqli_stmt_bind_param($stmt, 'is', 3, 'Επιλύθηκε')
mysqli_stmt_execute($stmt);
$issues = mysqli_stmt_get_result($stmt);

// Οι κατηγορίες για το dropdown φίλτρου
$categories = mysqli_query($conn, "SELECT category_id, name FROM categories");

require 'includes/header.php';
?> 

<h1 class="h3 mb-4">Προβολή Αναφορών</h1>

<!-- αναζήτηση με ticket id -->
<div class="card mb-4"> <!-- bootstrap card-->
  <div class="card-body">
    <form action="browse.php" method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-9">
        <label for="ticket" class="form-label mb-1">Αναζήτηση με Ticket ID</label>
        <input type="text" class="form-control" id="ticket" name="ticket"
               placeholder="π.χ. CR-00001"
               value="<?php echo htmlspecialchars($fTicket); ?>"> <!-- αν η αναζήτηση αποτύχει ο χρήστης εξακολουθεί να βλέπει τι έγραψε-->
      </div>
      <div class="col-12 col-md-3">
        <button type="submit" class="btn btn-secondary w-100">Αναζήτηση</button>
      </div>
    </form>

    <?php if (isset($ticketError)): ?> <!--αν υπάρχει η μεταβλ $ticketError από PHP-->
      <div class="alert alert-warning mt-3 mb-0"> <!-- boostrap warning -->
        <?php echo htmlspecialchars($ticketError); ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- φίλτρα-->
<div class="card mb-4">
  <div class="card-body">
    <form action="browse.php" method="GET" class="row g-3">

      <div class="col-12 col-md-3">
        <label for="category" class="form-label">Κατηγορία</label>
        <select class="form-select" id="category" name="category"> <!-- dropdown menu -->
          <option value="">Όλες</option>

          <!-- loop, παίρνει τις κατηγορίες από τη βάση και φτιάχνει τα options-->
          <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <option value="<?php echo $cat['category_id']; ?>"
              <?php if ((string)$fCategory === (string)$cat['category_id']) echo 'selected'; ?>> <!-- έλεγχος τύπου και τιμήσ-->
              <?php echo htmlspecialchars($cat['name']); ?>
            </option>
          <?php endwhile; ?>

        </select>
      </div>

      <div class="col-12 col-md-3">
        <label for="status" class="form-label">Κατάσταση</label>
        <select class="form-select" id="status" name="status">
          <option value="">Όλες</option>
          <option value="Υποβλήθηκε" <?php if ($fStatus === 'Υποβλήθηκε') echo 'selected'; ?>>Υποβλήθηκε</option>
          <option value="Σε Εξέλιξη"  <?php if ($fStatus === 'Σε Εξέλιξη')  echo 'selected'; ?>>Σε Εξέλιξη</option>
          <option value="Επιλύθηκε"   <?php if ($fStatus === 'Επιλύθηκε')   echo 'selected'; ?>>Επιλύθηκε</option>
        </select>
      </div>


      <div class="col-12 col-md-3">
        <label for="sort" class="form-label">Ημερομηνία</label>
        <select class="form-select" id="sort" name="sort">
          <option value="newest" <?php if ($fSort === 'newest') echo 'selected'; ?>>Πιο πρόσφατα πρώτα</option>
          <option value="oldest" <?php if ($fSort === 'oldest') echo 'selected'; ?>>Πιο παλιά πρώτα</option>
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label for="user" class="form-label">Username</label>
        <input type="text" class="form-control" id="user" name="user"
               placeholder="Αναζήτηση χρήστη"
               value="<?php echo htmlspecialchars($fUser); ?>">
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Εφαρμογή Φίλτρων</button>
        <a href="browse.php" class="btn btn-outline-secondary">Καθαρισμός</a>
      </div>

    </form>
  </div>

</div>

<!--λίστα αναφορών-->
<?php if (mysqli_num_rows($issues) === 0): ?> <!-- πόσες γραμμές επέστρεψε το ερώτημα -->

  <div class="alert alert-info">Δεν βρέθηκαν αναφορές με τα κριτήρια που επιλέξατε </div>

<?php else: ?>

  <p class="text-muted"><?php echo mysqli_num_rows($issues); ?> αναφορές</p> <!-- μετρητής αποτελεσμάτων -->

  <div class="row g-3">
    <?php while ($issue = mysqli_fetch_assoc($issues)): ?>
      <div class="col-12 col-md-6">
        <!-- όλη η κάρτα είναι σύνδεσμος -->
        <a href="detail.php?id=<?php echo $issue['issue_id']; ?>"
           class="text-decoration-none text-dark">
          <div class="card h-100 issue-card"> <!-- issue-card από style.css για hover-->
            <div class="card-body">

              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-secondary"><?php echo htmlspecialchars($issue['category_name']); ?></span>
                <?php
                  $badgeClass = 'bg-primary';
                  if ($issue['status'] === 'Σε Εξέλιξη') $badgeClass = 'bg-warning text-dark';
                  if ($issue['status'] === 'Επιλύθηκε') $badgeClass = 'bg-success';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"> <?php echo htmlspecialchars($issue['status']); ?> </span>
              </div>

              <h5 class="card-title h6"><?php echo htmlspecialchars($issue['title']); ?></h5>

              <p class="card-text small text-muted mb-1">
                <?php echo htmlspecialchars($issue['ticket_id']); ?>
                &middot;
                <?php echo date('d/m/Y H:i', strtotime($issue['created_at'])); ?>
              </p>

              <p class="card-text small mb-0">
                <?php if ($issue['is_anonymous'] == 1): ?>
                  <span class="text-muted">Ανώνυμη αναφορά</span>
                <?php else: ?>
                  Από: <strong><?php echo htmlspecialchars($issue['user']); ?></strong>
                <?php endif; ?>
              </p>

            </div>
          </div>
        </a>
      </div>
    <?php endwhile; ?>
  </div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>