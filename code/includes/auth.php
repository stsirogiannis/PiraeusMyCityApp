<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!isset($_SESSION['admin_id'])) { // αν δεν υπάρχει το cookie ανακατεθυνση στο login.php
    header('Location: login.php');
    exit;
}


