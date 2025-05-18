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

// Mi piace
$likeStmt = $conn->prepare("SELECT 1 FROM mipiace WHERE user_id = ? AND post_id = ?");
$likeStmt->bind_param("ii", $user_id, $id);
$likeStmt->execute();
$hasLiked = $likeStmt->get_result()->num_rows > 0;

// Conteggio totale dei like
$countStmt = $conn->prepare("SELECT COUNT(*) as tot FROM mipiace WHERE post_id = ?");
$countStmt->bind_param("i", $id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$likeCount = $countResult->fetch_assoc()['tot'];
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

<!-- Script per like -->
<script>
    function toggleLike(postId) {
        const btn = document.getElementById("likeButton");

        fetch("like.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "post_id=" + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle("active");
            } else {
                alert(data.message);
            }
        });
    }
</script>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">
        <div class="container flex-column">

            <!-- Prima riga: Logo, link e pulsanti -->
            <div class="d-flex w-100 align-items-center"> 
                
                <a class="navbar-brand fw-bold" href="#">ExponoHub</a>

                <!--Elementi al centro-->
                <div class="nav-center">
                    <a class="nav-link" href="homepage.php">Homepage</a>
                    <a class="nav-link" href="#">Popolari</a>
                    <a class="nav-link" href="#">Categorie</a>  
                    <a class="nav-link" href="#">Preferiti</a>
                </div>
                
                <!--Elementi a destra-->
                <div class="nav-right">
                    <a class="nav-link bi bi-plus-circle" href="nuovopost.php"></a>
                    <a class="nav-link bi bi-bell-fill" href="index.user.notiche.html"></a>
                    <div class="dropdown">
                    <button class="btn btn-link nav-link dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item bi bi-person" href="utente.html"> Utente</a></li>
                        <li><a class="dropdown-item bi bi-gear" href="impostazioni.html"> Impostazioni</a></li>
                        <li><a class="dropdown-item bi bi-person-lock" href="privacy.html"> Privacy</a></li>
                        <li><a class="dropdown-item bi bi-box-arrow-right" href="logout.html"> Logout</a></li>
                    </ul>
                    </div>
                </div>

            </div>

            <!-- Seconda riga: Barra di ricerca  -->
            <div class="search-bar w-100 mt-2">  
                <form class="d-flex justify-content-center">
                    <input  
                        type="search" 
                        class="form-control" 
                        placeholder="Cerca" 
                        aria-label="Search"
                    > 
                </form>
            </div>
        
        </div>
    </nav>

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

        <div class="like-section mb-4">
            <button 
                id="likeButton" 
                class="btn btn-outline-danger <?= $hasLiked ? 'active' : '' ?>" 
                onclick="toggleLike(<?= $id ?>)"
            >
                <i class="bi bi-heart-fill"></i> Mi Piace
            </button>
        </div>

    </div>

</body>
</html>
