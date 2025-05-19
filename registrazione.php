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
                $successo = "Registrazione completata! Controlla la tua email per verificare l'account.";
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
    <title>Registrazione - ExpoonHub</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <div class="registrazione-container">
        <h2>Registrati su ExpoonHub</h2>

        <?php if (isset($_GET['verificato']) && $_GET['verificato'] == 1): ?>
            <div class="messaggio-successo">Email verificata con successo! Ora puoi accedere.</div>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div class="messaggio-errore"><?php echo $errore; ?></div>
        <?php elseif (!empty($successo)): ?>
            <div class="messaggio-successo"><?php echo $successo; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
                <label for="cognome">Cognome</label>
                <input type="text" id="cognome" name="cognome" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input type="password" id="conferma_password" name="conferma_password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-registra">Registrati</button>
            </div>

            <div class="form-group">
                <button type="button" class="btn-registra btn-spazio" onclick="window.location.href='login.php'">
                    Accedi
                </button>
            </div>
        </form>
    </div>
</body>
</html>
