<?php 
    session_start();
    require 'connessione.php'; 

    // Controlla se l'utente è loggato, altrimenti reindirizza
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - ExponoHub</title>
    <link rel="stylesheet"  href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Ordinamento post -->
    <div class="d-flex justify-content-center align-items-center w-100 mt-3 pe-2">
        <form method="GET" class="d-flex align-items-center">
            <select name="order" id="order" class="form-select form-select-sm" onchange="this.form.submit()">
                <option disabled <?= !isset($_GET['order']) ? 'selected' : '' ?>>Filtra per</option>
                <option value="desc" <?= (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'selected' : '' ?>>Più recenti</option>
                <option value="asc" <?= (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'selected' : '' ?>>Meno recenti</option>
            </select>
        </form>
    </div>

    
    <!-- Visualizzazione post -->
    <div class="homepage">
        <?php
        // Ordine dei post
        $order = 'DESC'; // Valore di default
        if (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') {
            $order = 'ASC';
        }

        // Query per i post
        $query = "SELECT p.*, u.username FROM post p JOIN utenti u ON p.user_id = u.id ORDER BY p.data_creazione $order";
        $result = $conn->query($query);

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
                                $src = "http://localhost/ExponoHub/$dir/$encodedFile";
                            ?>
                            <img src="<?= $src ?>" alt="Immagine del post">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </a>
        </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="alert alert-info">Nessun post trovato</div>
        <?php endif; ?>
    </div>

</body>
</html>