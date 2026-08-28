<?php

//μετατροπή διεύθυνσης σε συντεταγμένες
function geocodeAddress($address) {

    $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&limit=1';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //επιστροφή αποτελέσματος ως κείμενο
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyCity-DS-Project');
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true); //από JSON σε πίνακα PHP (associative array)

    if (empty($data)) {
        return null;
    }

    //αποτελέσματα από το nominatim, παίρνουμε το το πρώτο στοιχείο από τον πίνακα
    $result = [];
    $result['lat'] = (float) $data[0]['lat'];
    $result['lon'] = (float) $data[0]['lon'];

    return $result;
}

//παραγωγή τυχαίου id για ανώνυμες αναφορές
function generateAnonymousId($conn) {
    $found = true;
    while ($found) {
        $candidate = 'anon_' . rand(10000, 99999);

        //prepared statement για username
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM issues WHERE user = ?");
        mysqli_stmt_bind_param($stmt, 's', $candidate);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res); //αποθήκευση του πλήθος των εγγραφών που βρέθηκαν
        mysqli_stmt_close($stmt);

        if ($row['c'] == 0) { //δεν υπάρχει κανένας άλος χρήστης με το ίδιο username, έξοδος από βρόγχο
            $found = false;
        }
    }
    return $candidate;
}