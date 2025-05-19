<?php
require 'connessione.php';


//Verifica accesso
session_start();
if (!isset($_SESSION['id_utente'])) {
    die("Accesso non autorizzato. Effettua il login.");
}
    
$user_id = $_SESSION['id_utente'];


if (!isset($_GET['id'])) {
    die("Post non trovato.");
}

$id = (int) $_GET['id'];

// post e autore
$stmt = $conn->prepare("SELECT p.*, u.username FROM post p JOIN utenti u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post inesistente.");
}

// Immagini
$imgStmt = $conn->prepare("SELECT path FROM immagini WHERE post_id = ?");
$imgStmt->bind_param("i", $post['id']);
$imgStmt->execute();
$imgResult = $imgStmt->get_result();
$images = $imgResult->fetch_all(MYSQLI_ASSOC);

// File
$fileStmt = $conn->prepare("SELECT path FROM file WHERE post_id = ?");
$fileStmt->bind_param("i", $post['id']);
$fileStmt->execute();
$fileResult = $fileStmt->get_result();
$files = $fileResult->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post - ExponoHub</title>
    <link rel="stylesheet"  href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>



<body>
    <?php include 'navbar.php'; ?>

    <div class="postview">
        <form action="homepage.php" method="get" class="mt-4">
            <button type="submit" class="btnritornohomepage">← Homepage</button>
        </form>
        <div class="titolo_post"><?= htmlspecialchars($post['titolo']) ?></div>
        <div class="meta_post">
            Pubblicato da <strong><?= htmlspecialchars($post['username']) ?></strong> il <?= date('d/m/Y H:i', strtotime($post['data_creazione'])) ?>
        </div>
        
        <?php if ($images): ?>
            <div class="immagine_post">
                <?php foreach ($images as $img): ?>
                    <?php
                        $dir = dirname($img['path']);
                        $file = basename($img['path']);
                        $encodedFile = rawurlencode($file);
                        $src = "http://localhost/ExponoHub/$dir/$encodedFile";
                    ?>
                    <img src="<?= $src ?>" alt="Immagine del post">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="descrizione_post"><?= $post['descrizione'] ?></div>

        <?php if ($files): ?>
            <h5>File allegati:</h5>
            <ul class="lista_filet">
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank">
                            <?= basename($file['path']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>


    </div>

</body>
</html>
