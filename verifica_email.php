<?php
session_start();
require_once('connessione.php');

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Verifica token e email
    $query = $conn->prepare("SELECT id FROM utenti WHERE email = ? AND token_verifica = ?");
    $query->bind_param("ss", $email, $token);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        // Aggiorna stato verifica
        $update = $conn->prepare("UPDATE utenti SET email_verificata = 1, token_verifica = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        // Recupera ID utente
        $recupera = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
        $recupera->bind_param("s", $email);
        $recupera->execute();
        $result = $recupera->get_result();
        $utente = $result->fetch_assoc();

        $_SESSION['id_utente'] = $utente['id'];
        $_SESSION['email'] = $email;

        header("Location: configura_profilo.php");
        exit();
    } else {
        echo "Token non valido o email già verificata.";
    }
} else {
    echo "Parametri mancanti.";
}
?>
