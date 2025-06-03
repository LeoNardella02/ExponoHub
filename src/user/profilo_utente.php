<?php
require_once('connessione.php');
session_start();

if (!isset($_SESSION['id_utente'])) {
    die("Accesso non autorizzato. Effettua il login.");
} 

if (!isset($_GET['id'])) {
    die("Profilo non trovato.");
}

$id_utente = (int) $_GET['id'];

// Recupero dati utente
$query = $conn->prepare("SELECT username, biografia, immagine_profilo FROM utenti WHERE id = ?");
$query->bind_param("i", $id_utente);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "Utente non trovato.";
    exit();
}

$utente = $result->fetch_assoc();

if (empty($utente['immagine_profilo'])) {
    $immagine = 'immagini/immagini_utente/default.png';
}else{
    $immagine = "http://localhost/ExponoHub/".$utente['immagine_profilo'];
}


// Recupero i post dell'utente
$query_post = $conn->prepare("
    SELECT 
        p.id, 
        p.titolo, 
        p.descrizione, 
        (SELECT COUNT(*) FROM mipiace WHERE post_id = p.id) AS like_count
    FROM post p
    WHERE p.user_id = ?
    ORDER BY p.id DESC
");

$query_post->bind_param("i", $id_utente);
$query_post->execute();
$risultato_post = $query_post->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo di <?php echo htmlspecialchars($utente['username']); ?></title>
    <link rel="icon" href="immagini/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="profilo-container">
    <div class="profilo-card">
        <div class="box-profilo-img">
            <img src="<?php echo htmlspecialchars($immagine); ?>" alt="Foto profilo">
        </div>
        <h2><?php echo htmlspecialchars($utente['username']);?></h2>
        <p><?php echo nl2br(htmlspecialchars($utente['biografia'])); ?></p>
    </div>

    <div class="progetti">

        <div style="text-align: center; margin-bottom: 20px;">
            <h3>Progetti pubblicati</h3>
        </div>
        
        <?php if ($risultato_post->num_rows > 0):
            while ($post = $risultato_post->fetch_assoc()):
                // Immagini associate al post
                $imgQuery = $conn->prepare("SELECT path FROM immagini WHERE post_id = ? ORDER BY id");
                $imgQuery->bind_param("i", $post['id']);
                $imgQuery->execute();
                $imgResult = $imgQuery->get_result();
                $images = $imgResult->fetch_all(MYSQLI_ASSOC);
        ?>

        <div class="post">
            <a href="postview.php?id=<?= $post['id'] ?>" class="post-link">
                <h2 class="titolopost"><?= htmlspecialchars($post['titolo']) ?></h2>
                <?php if ($images): ?>
                    <div class="grid_immagini">
                        <?php foreach ($images as $img): ?>
                            <?php
                                $dir = dirname($img['path']);
                                $file = basename($img['path']);
                                $encodedFile = rawurlencode($file);
                                $src = "http://localhost/ExponoHub/$dir/$encodedFile";
                            ?>
                            <img src="<?= $src ?>" alt="Immagine del post" style="width:100%; height:auto;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <br>
                <i class="bi bi-heart-fill"></i> <?= $post['like_count'] ?? 0 ?>
            </a>
        </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="alert alert-info">Nessun post trovato</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
