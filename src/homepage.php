<?php 
    session_start();
    require 'connessione.php'; 

    // Controlla se l'utente è loggato, altrimenti reindirizza
    if (!isset($_SESSION['id_utente'])) {
        header("Location: login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet"  href="stile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Menù di filtraggio post-->
    <div class="filtra-container">
        <form method="GET" class="filtra-form">
            <?php if (isset($_GET['filter']) && $_GET['filter'] === 'favorites'): ?>
                <input type="hidden" name="filter" value="favorites">
            <?php endif; ?>
            <select name="order" id="order" class="filtra-select" onchange="this.form.submit()">
                <option disabled <?= !isset($_GET['order']) ? 'selected' : '' ?>>Filtra per</option>
                <option value="desc" <?= (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'selected' : '' ?>>Più recenti</option>
                <option value="asc" <?= (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'selected' : '' ?>>Meno recenti</option>
                <option value="like_desc" <?= ($_GET['order'] ?? '') === 'like_desc' ? 'selected' : '' ?>>Più apprezzati</option>
                <option value="like_asc" <?= ($_GET['order'] ?? '') === 'like_asc' ? 'selected' : '' ?>>Meno apprezzati</option>
            </select>
        </form>
    </div>

    <!-- Visualizzazione post -->
    <div class="homepage">
        <?php
        $orderParam = $_GET['order'] ?? 'desc';

        switch ($orderParam) {
            case 'asc':
                $orderBy = "p.data_creazione ASC";
                break;
            case 'like_desc':
                $orderBy = "like_count DESC, p.data_creazione DESC";

                break;
            case 'like_asc':
                $orderBy = "like_count ASC, p.data_creazione DESC";
                break;
            case 'desc':
            default:
                $orderBy = "p.data_creazione DESC";
                break;
        }

        // Query per i post con join alla tabella dei like
        $user_id = $_SESSION['id_utente'];
        $filterFavorites = isset($_GET['filter']) && $_GET['filter'] === 'favorites';

        if ($filterFavorites) {
            // Mostra solo i post preferiti
            $query = "
                SELECT p.*, u.username, COUNT(m.id) AS like_count
                FROM preferiti f
                JOIN post p ON f.post_id = p.id
                JOIN utenti u ON p.user_id = u.id
                LEFT JOIN mipiace m ON p.id = m.post_id
                WHERE f.user_id = ?
                GROUP BY p.id
                ORDER BY $orderBy
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Mostra tutti i post
            $query = "
                SELECT p.*, u.username, COUNT(m.id) AS like_count
                FROM post p
                JOIN utenti u ON p.user_id = u.id
                LEFT JOIN mipiace m ON p.id = m.post_id
                GROUP BY p.id
                ORDER BY $orderBy
            ";
            $result = $conn->query($query);
        }

        if ($result && $result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
                // Immagini associate al post
                $imgQuery = $conn->prepare("SELECT path FROM immagini WHERE post_id = ?");
                $imgQuery->bind_param("i", $post['id']);
                $imgQuery->execute();
                $imgResult = $imgQuery->get_result();
                $images = $imgResult->fetch_all(MYSQLI_ASSOC);
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
                        <i class="bi bi-heart-fill" style="text-color:black;"></i> <?= $post['like_count'] ?? 0 ?>
                    </a>
                </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="alert alert-info">Nessun post trovato</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</body>
</html>