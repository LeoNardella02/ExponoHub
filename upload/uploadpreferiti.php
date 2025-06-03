<?php
session_start();
require 'connessione.php';

if (!isset($_SESSION['id_utente'])) {
    echo json_encode(['success' => false, 'message' => 'Non sei loggato.']);
    exit;
}

$user_id = $_SESSION['id_utente'];
$post_id = (int) ($_POST['post_id'] ?? 0);

// Controlla se il post è già tra i preferiti
$checkStmt = $conn->prepare("SELECT id FROM preferiti WHERE user_id = ? AND post_id = ?");
$checkStmt->bind_param("ii", $user_id, $post_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Rimuovi dai preferiti
    $deleteStmt = $conn->prepare("DELETE FROM preferiti WHERE user_id = ? AND post_id = ?");
    $deleteStmt->bind_param("ii", $user_id, $post_id);
    $deleteStmt->execute();
    echo json_encode(['success' => true, 'favorited' => false]);
} else {
    // Aggiungi ai preferiti
    $insertStmt = $conn->prepare("INSERT INTO preferiti (user_id, post_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $user_id, $post_id);
    $insertStmt->execute();
    echo json_encode(['success' => true, 'favorited' => true]);
}
?>
