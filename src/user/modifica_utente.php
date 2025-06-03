<?php
session_start();
require_once('connessione.php');

$messaggio = "";
$errore = "";

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}

$id_utente = $_SESSION['id_utente'];

// Recupera i dati attuali dell'utente
$query = $conn->prepare("SELECT username, biografia, immagine_profilo FROM utenti WHERE id = ?");
$query->bind_param("i", $id_utente);
$query->execute();
$result = $query->get_result();
$utente = $result->fetch_assoc();
$query->close();

$username = $utente['username'];
$bio = $utente['biografia'];
$path_immagine = $utente['immagine_profilo'] ?: 'default.png';
$percorso_immagine = file_exists($path_immagine) ? $path_immagine : "immagini/immagini_utente/default.png";

// Gestione aggiornamenti
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuovo_username = trim($_POST['username']);
    $nuova_bio = trim($_POST['biografia']);

    // Controllo immagine
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        $estensione = pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION);
        $nome_file = uniqid() . "." . $estensione;
        $cartella_utente = "immagini/immagini_utente/" . $id_utente;
        if (!is_dir($cartella_utente)) mkdir($cartella_utente, 0777, true);
        $percorso_destinazione = $cartella_utente . "/" . $nome_file;

        if (move_uploaded_file($_FILES['immagine']['tmp_name'], $percorso_destinazione)) {
            $immagine_nome = "immagini/immagini_utente/" . $id_utente . "/" . $nome_file;
            $update_img = $conn->prepare("UPDATE utenti SET immagine_profilo = ? WHERE id = ?");
            $update_img->bind_param("si", $immagine_nome, $id_utente);
            $update_img->execute();
            $update_img->close();
        }
    }

    // Cambio username e bio
    $update = $conn->prepare("UPDATE utenti SET username = ?, biografia = ? WHERE id = ?");
    $update->bind_param("ssi", $nuovo_username, $nuova_bio, $id_utente);
    $update->execute();
    $update->close();

    // Cambio password se fornito
    if (!empty($_POST['vecchia_password']) && !empty($_POST['nuova_password']) && !empty($_POST['conferma_password'])) {
        $vecchia_password = $_POST['vecchia_password'];
        $nuova_password = $_POST['nuova_password'];
        $conferma_password = $_POST['conferma_password'];

        $query_pw = $conn->prepare("SELECT password FROM utenti WHERE id = ?");
        $query_pw->bind_param("i", $id_utente);
        $query_pw->execute();
        $query_pw->bind_result($password_hash);
        $query_pw->fetch();
        $query_pw->close();

        if (password_verify($vecchia_password, $password_hash)) {
            if ($nuova_password !== $conferma_password) {
                $errore .= "Le nuove password non coincidono.<br>";
            } elseif (strlen($nuova_password) < 8 || strlen($nuova_password) > 20) {
                $errore .= "La nuova password deve avere tra 8 e 20 caratteri.<br>";
            } else {
                $hash_nuova = password_hash($nuova_password, PASSWORD_DEFAULT);
                $update_pw = $conn->prepare("UPDATE utenti SET password = ? WHERE id = ?");
                $update_pw->bind_param("si", $hash_nuova, $id_utente);
                $update_pw->execute();
                $update_pw->close();
                $messaggio .= "Password aggiornata con successo.<br>";
            }
        } else {
            $errore .= "La vecchia password non è corretta.<br>";
        }
    }

    // Redirect solo se non ci sono errori
    if (empty($errore)) {
        header("Location: profilo_utente.php?id=$id_utente");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni Profilo - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="registrazione-container">
    <form action="homepage.php" method="get">
        <button type="submit" class="btnritornohomepage">← Homepage</button>
    </form>

    <h2 style="text-align: center; font-size: 1.6rem;">Impostazioni Profilo</h2>

    <?php if (!empty($messaggio)): ?>
        <div class="messaggio-successo"><?php echo $messaggio; ?></div>
    <?php endif; ?>

    <?php if (!empty($errore)): ?>
        <div class="messaggio-errore"><?php echo $errore; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group" style="text-align: center; margin-bottom: 20px;">
            <label for="immagine" style="cursor: pointer; display: inline-block;">
                <div class="box-profilo-img" style="position: relative;">
                    <img id="preview-img" src="<?php echo $percorso_immagine; ?>" alt="Anteprima immagine" class="immagine-profilo-preview">
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
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <input type="text" id="biografia" name="biografia" value="<?php echo htmlspecialchars($bio); ?>">
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <h4 style="margin: 0;">Cambio password</h4>
        </div>

        <div class="form-group">
            <label for="vecchia_password">Vecchia password</label>
            <input type="password" id="vecchia_password" name="vecchia_password">
        </div>

        <div class="form-group">
            <label for="nuova_password">Nuova password</label>
            <input type="password" id="nuova_password" name="nuova_password">
        </div>

        <div class="form-group">
            <label for="conferma_password">Conferma nuova password</label>
            <input type="password" id="conferma_password" name="conferma_password">
        </div>

        <div class="form-group">
            <button type="submit" class="btn-registra">Salva modifiche</button>
        </div>
    </form>
</div>
</body>
</html>