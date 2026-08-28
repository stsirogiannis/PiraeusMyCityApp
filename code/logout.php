<?php
session_start();
session_unset(); //άδειασμα πίνακα $_SESSION
session_destroy(); //καταστροφή αρχείου session

header('Location: index.php'); //ανακατεύθυνση χρήστη στο homepage
exit;