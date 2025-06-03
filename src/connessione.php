<?php
    // Connessione al database 
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "ExponoHub";

    $conn = new mysqli($host, $user, $password, $dbname);

    // Controlla la connessione
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
?>