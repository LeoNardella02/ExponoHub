<?php
require 'connessione.php';
session_start();

if (!isset($_SESSION['id_utente'])) {
    die("Accesso non autorizzato.");
}

$user_id = $_SESSION['id_utente'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$contenuto = trim($_POST['contenuto']);

if (empty($contenuto)) {
    die("Il commento non può essere vuoto.");
}

$stmt = $conn->prepare("INSERT INTO commenti (user_id, post_id, contenuto) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $post_id, $contenuto);
$stmt->execute();

header("Location: postview.php?id=" . $post_id);
exit;
?>