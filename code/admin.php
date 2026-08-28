<?php


require 'includes/auth.php'; // εφόσον είναι συνδεδεμένος ο admin
require 'includes/db.php' ;

$page_title = 'Dashboard' ;
$message = '';



if($_SERVER['REQUEST_METHOD']==='POST'){


  $action= $_POST['action'] ?? ''; //τι ενέργεια θα κάνει
  $issue_id= (int) ($_POST['issue_id'] ?? 0); // σε ποια αναφορά

  //αλλαγή κατάστασης
  if ($action==='update_status' && $issue_id>0){

      $newStatus = $_POST['new_status'] ?? '';

      if ($newStatus === 'Υποβλήθηκε' || $newStatus === 'Σε Εξέλιξη' || $newStatus === 'Επιλύθηκε') {
          $stmt = mysqli_prepare($conn, "UPDATE issues SET status = ? WHERE issue_id = ?");
          mysqli_stmt_bind_param($stmt, 'si', $newStatus, $issue_id);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_close($stmt);

          $_SESSION['admin_message']= 'Η κατάσταση ενημερώθηκε';
      }

  //διαγραφή αναφοράς
  } elseif ($action === 'delete' && $issue_id>0) {

      //εντοπισμός διαδρομής για το βιντεο
      $stmt = mysqli_prepare($conn, "SELECT video_path FROM issues WHERE issue_id = ?");
      mysqli_stmt_bind_param($stmt, 'i', $issue_id);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      $row = mysqli_fetch_assoc($res);
      mysqli_stmt_close($stmt);

      // διαγραφή βίνεο, αν υπάρχει
      if ($row && $row['video_path'] !== null && file_exists($row['video_path'])){
          unlink($row['video_path']);
      } 

      //σβήσιμο γραμμής από τη βάση
      $stmt = mysqli_prepare($conn, "DELETE FROM issues WHERE issue_id = ?");
      mysqli_stmt_bind_param($stmt, 'i', $issue_id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);


      $_SESSION['admin_message'] = 'Η αναφορά διαγράφηκε';
  }

  header('Location: admin.php');
  exit;



}

// μήνυμα μετα το redirect
if (isset($_SESSION['admin_message'])){
    $message = $_SESSION['admin_message']; // μήνυμα για την αλλαγή
    unset($_SESSION['admin_message']); // για να μην ξαναεμφανιστεί
}

// φίλτρα
$fCategory= $_GET['category'] ?? '';
$fStatus= $_GET['status'] ?? '';
$fPriority= $_GET['priority'] ?? '' ;
$fUser= trim($_GET['user'] ?? '');
$fSort= $_GET['sort'] ?? 'newest';

//όπως στο browse.php, γρ. 32
$sql = "SELECT i.issue_id, i.ticket_id, i.title, i.status, i.priority,
               i.user, i.is_anonymous, i.created_at, c.name AS category_name
        FROM issues i
        JOIN categories c ON i.category_id = c.category_id
        WHERE 1=1" ;

$params= [] ;

$types= '';


if($fCategory!==''){
    $sql .= " AND i.category_id = ?";
    $params[] = (int) $fCategory;
    $types .= 'i';
}

if ($fStatus !== '') {
    $sql .= " AND i.status = ?";
    $params[] = $fStatus;
    $types .= 's';
}



if ($fPriority!== '') {
    $sql .= " AND i.priority = ?" ;
    $params[] = $fPriority;
    $types .= 's';
}

if($fUser!== '') {
    $sql .= " AND i.user LIKE ?";
    $params[] = '%' . $fUser . '%';
    $types .= 's';

}

if($fSort==='oldest'){
    $sql .= " ORDER BY i.created_at ASC";
}else{
    $sql .= " ORDER BY i.created_at DESC" ;
}

$stmt= mysqli_prepare($conn, $sql);


if(count($params)===1){
    mysqli_stmt_bind_param($stmt, $types, $params[0]) ;
}elseif(count($params)===2) {
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1]);
}elseif(count($params)===3){
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1], $params[2]) ;
}elseif(count($params) === 4){
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1], $params[2], $params[3]);
}

//κλήση στη βάση
mysqli_stmt_execute($stmt);
$issues = mysqli_stmt_get_result($stmt) ;


$categories = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Dashboard</h1>

</div>

<?php if ($message !== ''): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($message) ; ?></div>
<?php endif; ?>

<!-- Φίλτρα -->
<div class="card mb-4">
  <div class="card-body">
    <form action="admin.php" method="GET" class="row g-3">

      <div class="col-12 col-md-3">
        <label for="category" class="form-label">Κατηγορία</label>
        <select class="form-select" id="category" name="category">
          <option value="">Όλες</option>
          <?php while ($cat= mysqli_fetch_assoc($categories)): ?>
            <option value="<?php echo $cat['category_id'] ; ?>"
              <?php if ((string)$fCategory===(string)$cat['category_id']) echo 'selected'; ?>>
              <?php echo htmlspecialchars($cat['name']) ; ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-12 col-md-2">
        <label for="status" class="form-label">Κατάσταση</label>
        <select class="form-select" id="status" name="status">
          <option value="">Όλες</option>
          <option value="Υποβλήθηκε" <?php if ($fStatus==='Υποβλήθηκε') echo 'selected'; ?>>Υποβλήθηκε</option>
          <option value="Σε Εξέλιξη" <?php if ($fStatus==='Σε Εξέλιξη') echo 'selected'; ?>>Σε Εξέλιξη</option>
          <option value="Επιλύθηκε" <?php if ($fStatus==='Επιλύθηκε') echo 'selected' ; ?>>Επιλύθηκε</option>
        </select>
      </div>



      <div class="col-12 col-md-2">
        <label for="priority" class="form-label">Προτεραιότητα</label>
        <select class="form-select" id="priority" name="priority">
          <option value="">Όλες</option>
          <option value="Χαμηλή" <?php if ($fPriority==='Χαμηλή') echo 'selected' ; ?>>Χαμηλή</option>
          <option value="Μεσαία" <?php if ($fPriority==='Μεσαία') echo 'selected'; ?>>Μεσαία</option>
          <option value="Υψηλή" <?php if ($fPriority==='Υψηλή') echo 'selected'; ?>>Υψηλή</option>
          <option value="Κρίσιμη" <?php if ($fPriority==='Κρίσιμη') echo 'selected'; ?>>Κρίσιμη</option>
        </select>
      </div>




      <div class="col-12 col-md-2">
        <label for="sort" class="form-label">Ημερομηνία</label>
        <select class="form-select" id="sort" name="sort">
          <option value="newest" <?php if ($fSort==='newest') echo 'selected'; ?>>Πρόσφατα</option>
          <option value="oldest" <?php if ($fSort==='oldest') echo 'selected'; ?>>Παλιά</option>
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label for="user" class="form-label">Χρήστης</label>
        <input type="text" class="form-control" id="user" name="user"
               value="<?php echo htmlspecialchars($fUser) ; ?>">
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Εφαρμογή</button>
        <a href="admin.php" class="btn btn-outline-secondary">Καθαρισμός</a>
      </div>

    </form>

  </div>

</div>

<!--πίνακας αναφορών-->
<?php if (mysqli_num_rows($issues)===0): ?>
  <div class="alert alert-info">Δεν βρέθηκαν αναφορές</div>
<?php else: ?>

  <p class="text-muted"><?php echo mysqli_num_rows($issues); ?> αναφορές</p>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Ticket ID</th>
          <th>Τίτλος</th>
          <th>Κατηγορία</th>
          <th>Προτεραιότητα</th>
          <th>Ημερομηνία</th>
          <th>Κατάσταση</th>
          <th>Ενέργειες</th>
        </tr>
      </thead>


      <tbody>
        <?php while($issue = mysqli_fetch_assoc($issues)): ?>
          <tr>
            <td class="small fw-bold"><?php echo htmlspecialchars($issue['ticket_id']); ?></td>
            <td class="small"><?php echo htmlspecialchars($issue['title']); ?></td>
            <td class="small"><?php echo htmlspecialchars($issue['category_name']); ?></td>
            <td class="small">
              <?php
                $pClass = 'bg-secondary';
                if ($issue['priority']=== 'Μεσαία') $pClass= 'bg-info text-dark';
                if ($issue['priority']=== 'Υψηλή') $pClass= 'bg-warning text-dark';
                if ($issue['priority']=== 'Κρίσιμη') $pClass= 'bg-danger';
              ?>
              <span class="badge <?php echo $pClass; ?>">
                <?php echo htmlspecialchars($issue['priority']); ?>
              </span>
            </td>
            <td class="small"><?php echo date('d/m/Y', strtotime($issue['created_at'])) ; ?></td>




            <td>
              <form action="admin.php" method="POST" class="d-flex gap-1">
                <!--hidden fields για να σταλθεί το action και το issue id -->
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                <select name="new_status" class="form-select form-select-sm">
                  <option value="Υποβλήθηκε" <?php if ($issue['status'] === 'Υποβλήθηκε') echo 'selected'; ?>>Υποβλήθηκε</option>
                  <option value="Σε Εξέλιξη" <?php if ($issue['status'] === 'Σε Εξέλιξη')  echo 'selected'; ?>>Σε Εξέλιξη</option>
                  <option value="Επιλύθηκε" <?php if ($issue['status'] === 'Επιλύθηκε')   echo 'selected'; ?>>Επιλύθηκε</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">OK</button>
              </form>
            </td>



            <td>
              <div class="d-flex gap-1">
                <a href="detail.php?id=<?php echo $issue['issue_id']; ?>"
                   class="btn btn-sm btn-outline-secondary">Προβολή</a>

                <form action="admin.php" method="POST"
                      onsubmit="return confirm('Διαγραφή της αναφοράς <?php echo htmlspecialchars($issue['ticket_id']) ; ?> ;') ;">
                    <!--hidden fields για να σταλθεί το action και το issue id-->
                    <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Διαγραφή</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>


  </div>




<?php endif; ?>

<?php require 'includes/footer.php'; ?>