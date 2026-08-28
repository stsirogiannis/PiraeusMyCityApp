<?php

if (session_status() === PHP_SESSION_NONE) { //αν δεν έχει ήδη ενεργό session
  session_start();
}

$is_admin = isset($_SESSION['admin_id']); //έλεγχος cookie admin

?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- προσαρμογή για mobile -->
  <title><?php echo htmlspecialchars($page_title); ?> | Piraeus MyCity</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100"> <!-- sticky footer -->

<!-- μενού -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary"> 
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Piraeus MyCity</a>

    <!--hamburger -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav" 
            aria-controls="mainNav" aria-expanded="false"
            aria-label="Μενού πλοήγησης">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- κουμπιά για μενού -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Αναφορά Προβλήματος</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="browse.php">Προβολή Αναφορών</a>
        </li>

        <?php if ($is_admin): ?>  <!-- μόνο αν είναι συνδεδεμένος ο admin -->
          <li class="nav-item">
            <a class="nav-link" href="admin.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Αποσύνδεση</a>
          </li>
        <?php else: ?> <!-- αν δεν είναι συνδεδεμένος ο admin -->
          <li class="nav-item">
            <a class="nav-link" href="login.php">Σύνδεση Διαχειριστή</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4 flex-grow-1"> <!-- κρύψιμο του dashboard -->