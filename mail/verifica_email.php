<?php
    session_start();
    require_once('connessione.php');

    if (isset($_GET['email']) && isset($_GET['token'])) {
        $email = $_GET['email'];
        $token = $_GET['token'];

        $query = $conn->prepare("SELECT id FROM utenti WHERE email = ? AND token_verifica = ?");
        $query->bind_param("ss", $email, $token);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            $update = $conn->prepare("UPDATE utenti SET email_verificata = 1, token_verifica = NULL WHERE email = ?");
            $update->bind_param("s", $email);
            $update->execute();

            // Recupera ID utente e crea sessione
            $query = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
            $query->bind_param("s", $email);
            $query->execute();
            $result = $query->get_result();
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
