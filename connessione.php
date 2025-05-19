
<?php
$host = "localhost";
$user = "root";
$password = ""; // di default è vuota su XAMPP
$database = "Exponohub"; // Assicurati che il nome sia corretto

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
