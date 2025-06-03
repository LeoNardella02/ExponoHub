<?php
session_start();
require_once('connessione.php');
$errore = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $conferma_password = $_POST['conferma_password'];

    if (empty($nome) || empty($cognome) || empty($email) || empty($password) || empty($conferma_password)) {
        $errore = "Tutti i campi sono obbligatori.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "L'email non è valida.";
    } elseif ($password !== $conferma_password) {
        $errore = "Le password non coincidono.";
    }elseif (strlen($password) < 8 || strlen($password) > 20) {
                $errore .= "La password deve avere tra 8 e 20 caratteri.<br>";
    } else {
        $query = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            $errore = "Email già registrata.";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, email, password, token_verifica, email_verificata) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $nome, $cognome, $email, $hash_password, $token);

            if ($stmt->execute()) {
                include_once('invia_verifica.php');
                inviaEmailVerifica($email, $token);

                header("Location: login.php?registrazione=successo");
                exit;
            } else {
                $errore = "Errore durante la registrazione.";
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
    <title>Registrazione - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
   
    <div class="registrazione-container">
        <div style="display: flex; justify-content: center; align-items: center;">
            <img src="immagini/logo.png" class="logo">
        </div>
        
        <h2>Registrati</h2>

        <?php if (isset($_GET['email_verificata']) && $_GET['email_verificata'] == 1): ?>
            <div class="messaggio-successo">Email verificata con successo! Ora puoi accedere.</div>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div class="messaggio-errore"><?php echo $errore; ?></div>
        <?php elseif (!empty($successo)): ?>
            <div class="messaggio-successo"><?php echo $successo; ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="cognome">Cognome</label>
                <input type="text" id="cognome" name="cognome" required value="<?php echo htmlspecialchars($_POST['cognome'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="password"> Password</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input type="password" id="conferma_password" name="conferma_password">
            </div>

            <div class="form-group">
                <button type="submit" class="btn-registra" style="margin-bottom:15px;">
                    Registrati
                </button>
            </div>

            <div class="form-group">
                <p style="margin-bottom: 10px;">Sei già registrato? </p>                
                <button type="button" style="font-size: 10px"class="btn-registra btn-spazio" onclick="window.location.href='login.php'">
                    Accedi
                </button>
            </div>

        </form>
    </div>
</body>
</html>
