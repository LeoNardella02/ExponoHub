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

// Preferiti
$favStmt = $conn->prepare("SELECT 1 FROM preferiti WHERE user_id = ? AND post_id = ?");
$favStmt->bind_param("ii", $user_id, $id);
$favStmt->execute();
$hasFavorited = $favStmt->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet"  href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>
<script>
    /* Script per like */
    function toggleLike(postId) {
        const btn = document.getElementById("likeButton");
        const countSpan = document.getElementById("likeCount");

        fetch("uploadlike.php", {
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

                // Aggiorna visivamente il conteggio
                let current = parseInt(countSpan.textContent);
                countSpan.textContent = data.liked ? current + 1 : current - 1;
            } else {
                alert("Errore inserimento mi piace.");
            }
        });
    }

    /* Script per i preferiti */
    function toggleFavorite(postId) {
        const btn = document.getElementById("bottonepreferiti");
        const icon = document.getElementById("bookmarkIcon");

        fetch("uploadpreferiti.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "post_id=" + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle("active", data.favorited);
                icon.classList.toggle("bi-bookmark-fill", data.favorited);
                icon.classList.toggle("bi-bookmark", !data.favorited);
            } else {
                alert("Errore nel salvataggio tra i preferiti.");
            }
        })
        .catch(error => {
            console.error("Errore AJAX:", error);
        });
    }
</script>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="postview">
        <div class="barra-superiore">
            <form action="homepage.php" method="get">
                <button type="submit" class="btnritornohomepage">← Homepage</button>
            </form>

            <?php if ($post['user_id'] == $user_id): ?>
                <form method="POST" action="modifica_post.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" id="modificaPost" style="float: right;">Modifica</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="titolo_post"><?= htmlspecialchars($post['titolo']) ?></div>
        <div class="meta_post">
            Pubblicato da 
            <a href="profilo_utente.php?id=<?= $post['user_id'] ?>" style="text-decoration: none; color: inherit;">
                <strong><?= htmlspecialchars($post['username']) ?></strong>
            </a> 
            il <?= date('d/m/Y H:i', strtotime($post['data_creazione'])) ?>
        </div>
        
        <!-- Immagini -->
        <?php if ($images): ?>
            <div class="immagine_post">
                <?php foreach ($images as $img): ?>
                    <?php
                        $dir = dirname($img['path']);
                        $file = basename($img['path']);
                        $encodedFile = rawurlencode($file);
                        $src = "http://localhost/ExponoHub/$dir/$encodedFile";
                    ?>
                    <img src="<?= $src ?>" alt="Immagine del post" onclick="openImage('<?= $src ?>')" style="cursor: zoom-in; width:100%; height:auto;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Descrizione -->
        <div class="descrizione_post ql-editor"><?php echo $post['descrizione']; ?></div>

        <!-- File allegati-->
        <?php if ($files): ?>
            <h5 style="margin-bottom: 10px;">File allegati:</h5>
            <ul class="lista_file">
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="/ExponoHub/<?= htmlspecialchars($file['path']) ?>" download style="text-decoration: none; color: black;">
                            <?= basename($file['path']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div>
            <!-- Bottone like -->
            <button id="likeButton" class="<?= $hasLiked ? 'active' : '' ?>" onclick="toggleLike(<?= $id ?>)">
                <i class="bi bi-heart-fill"></i> <span id="likeCount"><?= $likeCount ?></span>
            </button>
            <!-- Bottone commenti -->
            <button id="attivacommenti" onclick="toggleCommentSection()">Commenta</button>
            <!-- Bottone preferiti -->
            <button id="bottonepreferiti" class="<?= $hasFavorited ? 'active' : '' ?>" onclick="toggleFavorite(<?= $id ?>)">
                <i id="bookmarkIcon" class="bi <?= $hasFavorited ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i> 
            </button>
        </div>

        <!-- Textarea commento -->
        <div id="commentSection" class="commenti-container" style="display: none;">
            <!-- Form nuovo commento -->
            <form action="uploadcommento.php" method="POST">
                <textarea name="contenuto" class="form-control" rows="3" placeholder="Scrivi un commento..."></textarea>
                <input type="hidden" name="post_id" value="<?= $id ?>">
                <button type="submit" id="inviacommento">Invia</button>
            </form>
        </div>

        <!-- Lista dei commenti -->
        <div id="listaCommenti">
            <?php
            $commentStmt = $conn->prepare("
                SELECT c.id, c.user_id, c.contenuto, c.data_creazione, u.username  
                FROM commenti c 
                JOIN utenti u ON c.user_id = u.id 
                WHERE c.post_id = ? 
                ORDER BY c.data_creazione DESC
            ");
            $commentStmt->bind_param("i", $id);
            $commentStmt->execute();
            $commentResult = $commentStmt->get_result();

            while ($comment = $commentResult->fetch_assoc()): ?>
                <div class="card"  data-id="<?= $comment['id'] ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?= htmlspecialchars($comment['username']) ?></strong>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['data_creazione'])) ?></small>
                        </div>
                        <?php if ($comment['user_id'] == $user_id): ?>
                            <form action="eliminacommento.php" method="POST" style="display:inline;" onsubmit="return confirm('Sei sicuro di voler eliminare questo commento?');">
                                <input type="hidden" name="commento_id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="post_id" value="<?= $id ?>">
                                <button type="submit" style="border:none; background:none; padding:0;" title="Elimina commento">
                                    <i class="bi bi-trash" ></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="commento-testo"><?= nl2br(htmlspecialchars($comment['contenuto'])) ?></div>
                </div>
            <?php endwhile; ?>
        </div>

    <div id="sovrimpressione" class="sovrimpressione" onclick="closeImage()">
        <img id="sovrimpressioneImage" class="sovrimpressioneimg" src="" alt="Immagine ingrandita">
    </div>

    <script>
        // Anteprima immagini presenti
        function openImage(src) {
            const lightbox = document.getElementById('sovrimpressione');
            const image = document.getElementById('sovrimpressioneImage');
            image.src = src;
            lightbox.classList.add('active');
        }

        // Chiudi immagine in sovrimpressione
        function closeImage() {
            document.getElementById('sovrimpressione').classList.remove('active');
        }

        // attiva textarea commenti
        function toggleCommentSection() {
            const section = document.getElementById("commentSection");
            section.style.display = (section.style.display === "none" || section.style.display === "") ? "block" : "none";
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>