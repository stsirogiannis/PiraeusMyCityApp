<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//αν είναι ήδη συνδεδεμένος πάει κατευθείαν στο dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;

}

$page_title = 'Σύνδεση Διαχειριστή';
require 'includes/db.php';


$error = '';
$oldUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldUsername = $username; // αν αποτυχει το login το πεδίο του username μένει συμπληρωμένο


    if ($username === '' || $password === '') {
        $error = 'Συμπληρώστε όνομα χρήστη και κωδικό.';

    } else {

        $stmt = mysqli_prepare($conn,
            "SELECT admin_id, username, password_hash, full_name
            FROM admins WHERE username = ?"
        );

        // prepared statement
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($admin && password_verify($password, $admin['password_hash'])) { //επαλήθευση password με hash


          $_SESSION['admin_id']   = $admin['admin_id'];
          $_SESSION['admin_name'] = $admin['full_name'];

          header('Location: admin.php');
          exit;

        } else {
            $error = 'Λάθος όνομα χρήστη ή κωδικός.';
        }
    }
}

require 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-5">

    <div class="card">
      <div class="card-body">

        <h1 class="h4 mb-3">Σύνδεση Διαχειριστή</h1>

        <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">

          <div class="mb-3">
            <label for="username" class="form-label">Όνομα Χρήστη</label>
            <input type="text" class="form-control" id="username" name="username"
                   required value="<?php echo htmlspecialchars($oldUsername); ?>">
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Κωδικός</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Σύνδεση</button>

        </form>

      </div>
    </div>

  </div>
</div>

<?php require 'includes/footer.php'; ?>