<?php
require_once('connessione.php');
session_start();

if (!isset($_GET['id'])) {
    echo "Profilo non trovato.";
    exit();
}

$id_utente = intval($_GET['id']);

// Recupero dati utente
$query = $conn->prepare("SELECT username, bio, immagine_profilo FROM utenti WHERE id = ?");
$query->bind_param("i", $id_utente);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "Utente non trovato.";
    exit();
}

$utente = $result->fetch_assoc();
$immagine = 'immagini/immagini_utente/' . $utente['immagine_profilo'];

if (empty($utente['immagine_profilo']) || !file_exists($immagine)) {
    $immagine = 'immagini/immagini_utente/default.png';
}

// Recupero i post dell'utente
$query_post = $conn->prepare("SELECT id, titolo, descrizione FROM post WHERE id_utente = ? ORDER BY id DESC");
$query_post->bind_param("i", $id_utente);
$query_post->execute();
$risultato_post = $query_post->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo di <?php echo htmlspecialchars($utente['username']); ?></title>

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
        <h2>@<?php echo htmlspecialchars($utente['username']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($utente['bio'])); ?></p>
    </div>

    <div class="progetti">
        <h3>Progetti pubblicati</h3>

        <?php if ($risultato_post->num_rows > 0): ?>
            <?php while ($post = $risultato_post->fetch_assoc()): ?>
                <?php
                $id_post = $post['id'];

                // Copertina: prima immagine
                $img_query = $conn->prepare("SELECT percorso FROM immagini WHERE id_post = ? ORDER BY id ASC LIMIT 1");
                $img_query->bind_param("i", $id_post);
                $img_query->execute();
                $img_result = $img_query->get_result();

                if ($img_result->num_rows > 0) {
                    $img_row = $img_result->fetch_assoc();
                    $immagine_post = $img_row['percorso'];
                } else {
                    $immagine_post = "immagini/immagini_post/default_post.png";
                }

                // Conta commenti
                $commenti_query = $conn->prepare("SELECT COUNT(*) as totale FROM commenti WHERE id_post = ?");
                $commenti_query->bind_param("i", $id_post);
                $commenti_query->execute();
                $commenti_result = $commenti_query->get_result();
                $n_commenti = $commenti_result->fetch_assoc()['totale'];

                // Troncatura descrizione
                $descrizione = strip_tags($post['descrizione']);
                $anteprima_descrizione = mb_strimwidth($descrizione, 0, 80, '...');
                ?>

                <div class="card-progetto">
                    <img src="<?php echo htmlspecialchars($immagine_post); ?>" alt="Copertina post">
                    <div class="card-contenuto">
                        <h4>
                            <a href="postview.php?id=<?php echo $id_post; ?>" style="color: black; text-decoration: none;">
                                <?php echo htmlspecialchars($post['titolo']); ?>
                            </a>
                        </h4>
                        <p><?php echo htmlspecialchars($anteprima_descrizione); ?></p>
                        <div class="card-stats">
                            <span><i class="bi bi-chat-dots"></i> <?php echo $n_commenti; ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>L'utente non ha ancora pubblicato progetti.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
