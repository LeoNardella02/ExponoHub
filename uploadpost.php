<?php
session_start();
require 'connessione.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$query = $conn->prepare("SELECT id, username FROM utenti WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

// Recupera ID utente
if ($result->num_rows === 1) {
    $utente = $result->fetch_assoc();
    $user_id = $utente['id'];
} else {
    die("Utente non trovato.");
}

// Pulisci e ricevi i dati
$titolo = isset($_POST['titolo']) ? $conn->real_escape_string($_POST['titolo']) : '';
$descrizione = isset($_POST['descrizione']) ? $conn->real_escape_string($_POST['descrizione']) : '';

if ($descrizione === '') {
    die("ERRORE: descrizione è vuota!");
}

$sqlPost = "INSERT INTO post (user_id, titolo, descrizione, data_creazione)
            VALUES ($user_id, '$titolo', '$descrizione', NOW())";

if ($conn->query($sqlPost) === TRUE) {
    $post_id = $conn->insert_id; // ID del post appena inserito
} else {
    die("Errore nel salvataggio del post: " . $conn->error);
}

// Crea le cartelle
$imgDir = "C:/xampp/htdocs/ExponoHub/immagini/immagini_post/$post_id/";
$fileDir = "C:/xampp/htdocs/ExponoHub/file/$post_id/";
mkdir($imgDir, 0777, true);
mkdir($fileDir, 0777, true);

// Loop sulle 5 immagini
for ($i = 0; $i < 5; $i++) {
    $inputName = "immagine$i";

    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === 0) {
        $tmpName = $_FILES[$inputName]['tmp_name'];
        $Name = basename($_FILES[$inputName]['name']);

        $targetPath = $imgDir . $Name;
        $relativePath = "immagini/immagini_post/$post_id/$Name";

        if (move_uploaded_file($tmpName, $targetPath)) {
            $sqlImg = "INSERT INTO immagini (post_id, path, data_creazione)
                       VALUES ('$post_id', '$relativePath', NOW())";
            $conn->query($sqlImg);
        }
    }
}


//Directory di destinazione file
if (isset($_FILES['files'])) {
    $fileCount = count($_FILES['files']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $tmpName = $_FILES['files']['tmp_name'][$i];
        $Name = basename($_FILES['files']['name'][$i]);

        $targetPath = $fileDir . $Name;
        $relativePath = "file/$post_id/$Name";

        if (move_uploaded_file($tmpName, $targetPath)) {
            $sqlFile = "INSERT INTO file (post_id, path, data_creazione)
                        VALUES ('$post_id', '$relativePath', NOW())";
            $conn->query($sqlFile);
        }
    }
}


$conn->close();
header("Location: homepage.php");
exit;
?>
