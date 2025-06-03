<?php
session_start();
require_once('connessione.php');

$errore = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errore = "Inserisci email e password.";
    } else {
        $query = $conn->prepare("SELECT id, password, email_verificata FROM utenti WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows == 1) {
            $utente = $result->fetch_assoc();
            if ($utente['email_verificata'] == 0) {
                $errore = "Email non ancora verificata.";
            } elseif (password_verify($password, $utente['password'])) {
                $_SESSION['email'] = $email;
                $_SESSION['id_utente'] = $utente['id'];
                header("Location: homepage.php");
                exit();
            } else {
                $errore = "Password errata.";
            }
        } else {
            $errore = "Utente non trovato.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="registrazione-container">
        <div style="display: flex; justify-content: center; align-items: center;">
            <img src="immagini/logo.png" class="logo">
        </div>

        <h2>Accedi</h2>

        <?php if (isset($_GET['registrazione']) && $_GET['registrazione'] === 'successo'): ?>
            <div class="messaggio-successo">Registrazione completata! Verifica l'email prima di accedere.</div>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div class="messaggio-errore"><?php echo $errore; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-registra" style="margin-bottom:15px;">
                    Accedi
                </button>
            </div>

            <div class="form-group">
                <p style="margin-bottom: 10px;">Non sei registrato?</p>                
                <button type="button" style="font-size: 10px"class="btn-registra btn-spazio" onclick="window.location.href='registrazione.php'">
                    Registrati
                </button>
            </div>

        </form>
    </div>
</body>
</html>
