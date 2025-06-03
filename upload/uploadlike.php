<?php
session_start();
require 'connessione.php';

$user_id = $_SESSION['id_utente'];
$post_id = (int) ($_POST['post_id'] ?? 0);

// Controlla se esiste già un like
$checkStmt = $conn->prepare("SELECT id FROM mipiace WHERE user_id = ? AND post_id = ?");
$checkStmt->bind_param("ii", $user_id, $post_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Rimuove il like
    $deleteStmt = $conn->prepare("DELETE FROM mipiace WHERE user_id = ? AND post_id = ?");
    $deleteStmt->bind_param("ii", $user_id, $post_id);
    $deleteStmt->execute();
    echo json_encode(['success' => true, 'liked' => false]);
} else {
    // Aggiunge il like
    $insertStmt = $conn->prepare("INSERT INTO mipiace (user_id, post_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $user_id, $post_id);
    $insertStmt->execute();
    echo json_encode(['success' => true, 'liked' => true]);
}
?>