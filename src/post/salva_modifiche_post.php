<?php
require 'connessione.php';
session_start();

if (!isset($_SESSION['id_utente'])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION['id_utente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Eliminazione post
    if (isset($_POST['delete_post']) && $_POST['delete_post'] == '1') {
        $post_id = (int) $_POST['post_id'];

        // Rimozione immagini e file fisici
        function deleteDirectory($dir) {
            if (!file_exists($dir)) return;

            if (!is_dir($dir)) {
                unlink($dir);
                return;
            }

            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                is_dir($path) ? deleteDirectory($path) : unlink($path);
            }

            rmdir($dir);
        }

        // Elimina la cartella immagini relative al post
        $imgDir = "immagini/immagini_post/$post_id";
        if (is_dir($imgDir)) {
            deleteDirectory($imgDir);
        }

        // Elimina la cartella file relative al post
        $fileDir = "file/$post_id";
        if (is_dir($fileDir)) {
            deleteDirectory($fileDir);
        }

        // Elimina dal DB
        $conn->query("DELETE FROM immagini WHERE post_id = $post_id");
        $conn->query("DELETE FROM file WHERE post_id = $post_id");
        $conn->query("DELETE FROM post WHERE id = $post_id AND user_id = $user_id");

        header("Location: homepage.php?deleted=1");
        exit;
    }

    $post_id = (int) $_POST['post_id'];
    $titolo = trim($_POST['titolo']);
    $descrizione = $_POST['descrizione'] ?? '';

    if (empty($titolo)) {
        die("Il titolo non può essere vuoto.");
    }

    // Verifica proprietà post
    $stmt = $conn->prepare("SELECT * FROM post WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Post inesistente");
    }

    // Aggiorna post
    $updateStmt = $conn->prepare("UPDATE post SET titolo = ?, descrizione = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $titolo, $descrizione, $post_id);
    $updateStmt->execute();

    // Elimina immagini esistenti dal filesystem e dal database
    if (isset($_POST['reset_immagini']) && $_POST['reset_immagini'] === '1'){
        $imgDelStmt = $conn->prepare("SELECT path FROM immagini WHERE post_id = ?");
        $imgDelStmt->bind_param("i", $post_id);
        $imgDelStmt->execute();
        $imgRes = $imgDelStmt->get_result();

        while ($img = $imgRes->fetch_assoc()) {
            if(file_exists($img['path'])) {
                unlink($img['path']); // elimina il file fisico
            }
        }

        $delStmt = $conn->prepare("DELETE FROM immagini WHERE post_id = ?");
        $delStmt->bind_param("i", $post_id);
        $delStmt->execute();
    }

    // Carica nuove immagini
    for ($i = 0; $i < 5; $i++) {
        $inputName = 'immagine' . $i;
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {

            // Elimina immagine precedente (se presente)
            $imgSelStmt = $conn->prepare("SELECT id, path FROM immagini WHERE post_id = ? LIMIT 1 OFFSET ?");
            $imgSelStmt->bind_param("ii", $post_id, $i);
            $imgSelStmt->execute();
            $imgResult = $imgSelStmt->get_result();
            if ($img = $imgResult->fetch_assoc()) {
                if (file_exists($img['path'])) {
                    unlink($img['path']); // elimina dal filesystem
                }
                $delImgStmt = $conn->prepare("DELETE FROM immagini WHERE id = ?");
                $delImgStmt->bind_param("i", $img['id']);
                $delImgStmt->execute(); // elimina dal db
            }

            // Carica nuova immagine
            $fileTmp = $_FILES[$inputName]['tmp_name'];
            $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
            $uniqueName = "img_" . uniqid() . "." . $ext;
            $targetDir = "immagini/immagini_post/$post_id/";
            $targetPath = $targetDir . $uniqueName;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $imgStmt = $conn->prepare("INSERT INTO immagini (post_id, path) VALUES (?, ?)");
                $imgStmt->bind_param("is", $post_id, $targetPath);
                $imgStmt->execute();
            }
        }
    }


    // Elimina i file esistenti se richiesto
    if (isset($_POST['reset_file_esistenti']) && $_POST['reset_file_esistenti'] === '1') {
        // Elimina file fisici dal disco
        $filePathStmt = $conn->prepare("SELECT path FROM file WHERE post_id = ?");
        $filePathStmt->bind_param("i", $post_id);
        $filePathStmt->execute();
        $filePaths = $filePathStmt->get_result();

        while ($file = $filePaths->fetch_assoc()) {
            if (file_exists($file['path'])) {
                unlink($file['path']); // rimuovi dal disco
            }
        }

        // Elimina dal DB
        $deleteFileStmt = $conn->prepare("DELETE FROM file WHERE post_id = ?");
        $deleteFileStmt->bind_param("i", $post_id);
        $deleteFileStmt->execute();
    }

    // Carica nuovi file
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['files']['name'][$index], PATHINFO_EXTENSION);
                $uniqueName = "file_" . uniqid() . "." . $ext;
                $targetDir = "file/$post_id/";
                $targetPath = $targetDir . $uniqueName;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true); // Crea directory ricorsivamente
                }

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $fileStmt = $conn->prepare("INSERT INTO file (post_id, path) VALUES (?, ?)");
                    $fileStmt->bind_param("is", $post_id, $targetPath);
                    $fileStmt->execute();
                } else {
                    echo "Errore nel salvataggio del file: $targetPath";
                }
            }
        }
    }

    header("Location: postview.php?id=" . $post_id);
    exit;
}
?>