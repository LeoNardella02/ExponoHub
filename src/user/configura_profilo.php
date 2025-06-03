<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('connessione.php');

if (!isset($_SESSION['id_utente'])) {
    echo "Accesso negato. Effettua il login.";
    exit();
}

$errore = "";
$immagine_nome = "immagini/immagini_utente/default.png";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utente = $_SESSION['id_utente'];
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio']);

    if (empty($username)) {
        $errore = "Devi scegliere un username.";
    } else {
        $check = $conn->prepare("SELECT id FROM utenti WHERE username = ? AND id != ?");
        $check->bind_param("si", $username, $id_utente);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errore = "Username già in uso.";
        } else {
            $cartella_utente = "immagini/immagini_utente/" . $id_utente;
            if (!is_dir($cartella_utente)) {
                mkdir($cartella_utente, 0777, true);
            }

            if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
                $query_immagine_vecchia = $conn->prepare("SELECT immagine_profilo FROM utenti WHERE id = ?");
                $query_immagine_vecchia->bind_param("i", $id_utente);
                $query_immagine_vecchia->execute();
                $result_immagine_vecchia = $query_immagine_vecchia->get_result();
                $row_vecchia = $result_immagine_vecchia->fetch_assoc();
                $immagine_vecchia = $row_vecchia['immagine_profilo'];

                $ext = pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION);
                $nome_file = uniqid() . "." . $ext;
                $percorso_destinazione = $cartella_utente . "/" . $nome_file;

                move_uploaded_file($_FILES['immagine']['tmp_name'], $percorso_destinazione);

                if (!empty($immagine_vecchia) && $immagine_vecchia !== 'default.png' && file_exists("immagini/immagini_utente/" . $immagine_vecchia)) {
                    unlink("immagini/immagini_utente/" . $immagine_vecchia);
                }

                $immagine_nome = "immagini/immagini_utente/" . $id_utente . "/" . $nome_file;
            } else {
                $query_immagine_attuale = $conn->prepare("SELECT immagine_profilo FROM utenti WHERE id = ?");
                $query_immagine_attuale->bind_param("i", $id_utente);
                $query_immagine_attuale->execute();
                $result_attuale = $query_immagine_attuale->get_result();
                $row_attuale = $result_attuale->fetch_assoc();
                $immagine_nome = $row_attuale['immagine_profilo'];
            }

            $query = $conn->prepare("UPDATE utenti SET username = ?, biografia = ?, immagine_profilo = ? WHERE id = ?");
            $query->bind_param("sssi", $username, $bio, $immagine_nome, $id_utente);
            if ($query->execute()) {
                header("Location: homepage.php");
                exit();
            } else {
                $errore = "Errore durante l'aggiornamento del profilo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configura Profilo - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="registrazione-container">
        <h2 style="text-align: center; font-size: 1.6rem;">Configura il tuo profilo</h2>

        <?php if (!empty($errore)): ?>
            <div class="messaggio-errore"><?php echo $errore; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group" style="text-align: center; margin-bottom: 20px;">
                <p style="margin-bottom: 8px; font-weight: bold;">Immagine del profilo</p>
                <label for="immagine" style="cursor: pointer; display: inline-block;">
                    <div class="box-profilo-img" style="position: relative;">
                        <?php
                        $id_utente = $_SESSION['id_utente'];
                        $cartella = "immagini/immagini_utente/" . $id_utente;
                        $imgPath = $cartella . "/default.png";

                        $query_immagine = $conn->prepare("SELECT immagine_profilo FROM utenti WHERE id = ?");
                        $query_immagine->bind_param("i", $id_utente);
                        $query_immagine->execute();
                        $result = $query_immagine->get_result();
                        $row = $result->fetch_assoc();

                        if (!empty($row['immagine_profilo']) && file_exists("immagini/immagini_utente/" . $row['immagine_profilo'])) {
                            $imgPath = "immagini/immagini_utente/" . $row['immagine_profilo'];
                        } else {
                            $imgPath = "immagini/immagini_utente/default.png";
                        }
                        ?>
                        <img id="preview-img" src="<?php echo $imgPath; ?>" alt="Anteprima immagine" class="immagine-profilo-preview">
                        <div style="position: absolute; bottom: -10px; right: -10px; background-color: white; border-radius: 50%; padding: 4px; box-shadow: 0 0 5px rgba(0,0,0,0.3);">
                            <i class="bi bi-plus" style="font-size: 18px; color: gray;"></i>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem; color: #555; margin-top: 5px;">Clicca per modificare</p>
                    <input type="file" id="immagine" name="immagine" accept="image/*" style="display: none;">
                </label>
            </div>

            <script>
            document.getElementById('immagine').addEventListener('change', function (event) {
                const file = event.target.files[0];
                const reader = new FileReader();

                if (file) {
                    reader.onload = function (e) {
                        const preview = document.getElementById('preview-img');
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            </script>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <input type="text" id="bio" name="bio">
            </div>

            <div class="form-group">
                <button type="submit" class="btn-registra">Salva Profilo</button>
            </div>
        </form>
    </div>
</body>
</html>
