<?php
session_start();
require_once('connessione.php');

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati ricerca - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="contenuto-centrale">
        <h2>Risultati per: "<?php echo htmlspecialchars($query); ?>"</h2>

            <!-- Profili trovati -->
            <h3>Profili trovati</h3>

            <?php
            $stmtUtenti = $conn->prepare("SELECT id, username, immagine_profilo FROM utenti WHERE username LIKE CONCAT('%', ?, '%')");
            $stmtUtenti->bind_param("s", $query);
            $stmtUtenti->execute();
            $utenti = $stmtUtenti->get_result();

            if ($utenti->num_rows > 0) {
                while ($utente = $utenti->fetch_assoc()) {
                    $idUtente = $utente['id'];
                    $nomeImmagine = $utente['immagine_profilo'];

                    // Costruisco il percorso dell'immagine
                    $pathRelativo = $nomeImmagine;
                    $pathAssoluto = __DIR__ . '/' . $pathRelativo;

                    if (empty($nomeImmagine) || !file_exists($pathAssoluto)) {
                        $pathRelativo = 'immagini/immagini_utente/default.png';
                    }

                    echo '
                    <div class="card-utente">
                        <img src="' . htmlspecialchars($pathRelativo) . '" alt="Profilo utente" >
                        <a class="username" href="profilo_utente.php?id=' . $idUtente . '" style="text-decoration: none; color: inherit;">' . htmlspecialchars($utente['username']) . '</a>
                    </div>';
                }
            } else {
                echo '<p>Nessun profilo trovato</p>';
            }
            ?>

            <!-- Post trovati -->
            <h3 class="mt-4">Post trovati</h3>

            <?php
            $stmtPost = $conn->prepare("
                SELECT 
                    p.id, 
                    p.titolo, 
                    p.descrizione, 
                    p.user_id, 
                    p.data_creazione, 
                    u.username
                FROM post p
                JOIN utenti u ON p.user_id = u.id
                WHERE p.titolo LIKE CONCAT('%', ?, '%') OR p.descrizione LIKE CONCAT('%', ?, '%')
            ");
            $stmtPost->bind_param("ss", $query, $query);
            $stmtPost->execute();
            $postRisultati = $stmtPost->get_result();

            $stmtLikes = $conn->prepare("SELECT COUNT(*) as like_count FROM mipiace WHERE post_id = ?");

            if ($postRisultati->num_rows > 0): 
                while ($post = $postRisultati->fetch_assoc()):
                    // Immagini associate al post
                    $imgQuery = $conn->prepare("SELECT path FROM immagini WHERE post_id = ?");
                    $imgQuery->bind_param("i", $post['id']);
                    $imgQuery->execute();
                    $imgResult = $imgQuery->get_result();
                    $images = $imgResult->fetch_all(MYSQLI_ASSOC);

                    $stmtLikes->bind_param("i", $post['id']);
                    $stmtLikes->execute();
                    $resultLikes = $stmtLikes->get_result();
                    $likeRow = $resultLikes->fetch_assoc();
                    $likeCount = $likeRow['like_count'] ?? 0;

            ?>
                <div class="post">
                    <a href="postview.php?id=<?= $post['id'] ?>" class="post-link">
                        <h2 class="titolopost"><?= htmlspecialchars($post['titolo']) ?></h2>
                        <div class="testo">
                            Pubblicato da <strong><?= htmlspecialchars($post['username']) ?></strong> il <?= date('d/m/Y H:i', strtotime($post['data_creazione'])) ?>
                        </div>

                        <?php if ($images): ?>
                            <div class="grid_immagini">
                                <?php foreach ($images as $img): ?>
                                    <?php
                                        $dir = dirname($img['path']);
                                        $file = basename($img['path']);
                                        $encodedFile = rawurlencode($file);
                                        $src = "$dir/$encodedFile";
                                    ?>
                                    <img src="<?= $src ?>" alt="Immagine del post" style="width:100%; height:auto;">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <br>
                        <i class="bi bi-heart-fill"></i> <?= $likeCount ?>

                    </a>
                </div>
                <?php endwhile;?>
            <?php else: ?>
                <div class="alert alert-info">Nessun post trovato</div>
            <?php endif; ?>
    </div>
</body>
</html>