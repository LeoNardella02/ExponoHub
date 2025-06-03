<?php
require 'connessione.php';
session_start();

if (!isset($_SESSION['id_utente'])) {
    die("Accesso negato.");
}

$user_id = $_SESSION['id_utente'];
$commento_id = isset($_POST['commento_id']) ? (int)$_POST['commento_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$commento_id || !$post_id) {
    die("Dati mancanti.");
}

// Verifica che il commento appartenga all'utente
$stmt = $conn->prepare("SELECT user_id FROM commenti WHERE id = ?");
$stmt->bind_param("i", $commento_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row['user_id'] != $user_id) {
    die("Permesso negato.");
}

// Elimina il commento
$delStmt = $conn->prepare("DELETE FROM commenti WHERE id = ?");
$delStmt->bind_param("i", $commento_id);
$delStmt->execute();

// Reindirizza al post
header("Location: postview.php?id=" . $post_id);
exit;
?>
