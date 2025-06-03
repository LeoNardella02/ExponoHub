<?php
session_start();

// Distrugge tutte le variabili di sessione
$_SESSION = array();

// Se c'è un cookie di sessione, lo elimina
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distrugge la sessione
session_destroy();

// Reindirizza alla pagina di login
header("Location: login.php");
exit();
?>
