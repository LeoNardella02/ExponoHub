<?php
session_start();
require 'connessione.php';

// Verifica se l'utente è loggato
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id_utente'];

$titolo = isset($_POST['titolo']) ? $conn->real_escape_string($_POST['titolo']) : '';
$descrizione = isset($_POST['descrizione']) ? $conn->real_escape_string($_POST['descrizione']) : '';

$sqlPost = "INSERT INTO post (user_id, titolo, descrizione, data_creazione)
            VALUES ($user_id, '$titolo', '$descrizione', NOW())";

if ($conn->query($sqlPost) === TRUE) {
    $post_id = $conn->insert_id; // ID del post appena inserito
} else {
    die("Errore nel salvataggio del post: " . $conn->error);
}

// Crea le cartelle
$imgDir = "immagini/immagini_post/$post_id/";
$fileDir = "file/$post_id/";
mkdir($imgDir, 0777, true);
mkdir($fileDir, 0777, true);

// IMMAGINI
for ($i = 0; $i < 5; $i++) {
    $inputName = "immagine$i";

    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === 0) {
        $tmpName = $_FILES[$inputName]['tmp_name'];
        $originalName = basename($_FILES[$inputName]['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        $uniqueName = uniqid("img_", true) . '.' . $extension;
        $targetPath = $imgDir . $uniqueName;
        $relativePath = "immagini/immagini_post/$post_id/$uniqueName";

        if (move_uploaded_file($tmpName, $targetPath)) {
            $sqlImg = "INSERT INTO immagini (post_id, path, data_creazione)
                       VALUES ('$post_id', '$relativePath', NOW())";
            $conn->query($sqlImg);
        }
    }
}

// FILE
if (isset($_FILES['files'])) {
    $fileCount = count($_FILES['files']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $tmpName = $_FILES['files']['tmp_name'][$i];
        $originalName = basename($_FILES['files']['name'][$i]);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        $uniqueName = uniqid("file_", true) . '.' . $extension;
        $targetPath = $fileDir . $uniqueName;
        $relativePath = "file/$post_id/$uniqueName";

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
