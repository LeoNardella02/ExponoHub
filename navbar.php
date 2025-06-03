<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <nav class="navbar">
        <div class="navbar-layout">

            <!-- Prima riga -->
            <div class="div">
                
                <!-- Nome del sito -->
                <img src="immagini/scritta.png" class="scritta">
                
                <!-- Burger button -->
                <button class="burger" onclick="toggleMenu()">☰</button>

                <!-- Link centrali -->
                <div class="nav-center" id="navTop">
                    <a class="nav-link" href="homepage.php">Homepage</a>
                    <a class="nav-link" href="homepage.php?filter=favorites">Preferiti</a>
                    <a class="nav-link" href="profilo_utente.php?id=<?php echo $_SESSION['id_utente']; ?>">Profilo</a>  
                    <a class="nav-link bi bi-plus-circle" href="nuovopost.php" title="Nuovo post"></a>
                </div>
            
                <!-- Dropdown -->
                <div class="nav-right">
                    <div class="dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item bi bi-gear" href="modifica_utente.php"> Modifica utente</a></li>
                                <li><a class="dropdown-item bi bi-box-arrow-right" href="logout.php"> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Link centrali mobile (inizialmente nascosti) -->
            <div class="nav-center1" id="navBottom" style="display: none;">
                <a class="nav-link" href="homepage.php">Homepage</a>
                <a class="nav-link" href="homepage.php?filter=favorites">Preferiti</a>
                <a class="nav-link" href="profilo_utente.php?id=<?= $_SESSION['id_utente']; ?>">Profilo</a>  
                <a class="nav-link bi bi-plus-circle" href="nuovopost.php" title="Nuovo post"></a>
            </div>

            <!-- Seconda riga: Search bar -->
            <div class="search-bar">  
                <form action="risultati_ricerca.php" method="GET">
                    <input  
                        type="search" 
                        name="query"
                        class="form-control" 
                        placeholder="Cerca" 
                        aria-label="Search"
                        required
                    > 
                </form>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const bottomMenu = document.getElementById('navBottom');

            if (bottomMenu.style.display === "none" || bottomMenu.style.display === "") {
                bottomMenu.style.display = "flex"; // mostra il menu mobile
            } else {
                bottomMenu.style.display = "none"; // nasconde il menu mobile
            }
        }
    </script>
</body>
</html>