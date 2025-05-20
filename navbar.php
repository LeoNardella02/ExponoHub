<nav class="custom-navbar">
    <div class="navbar-container">

        <!-- Prima riga: Logo, link e pulsanti -->
        <div class="navbar-top">
            
            <!-- Nome sito -->
            <div class="nome">ExponoHub</div>

            <!--Elementi al centro-->
            <div class="nav-center">
                <a class="nav-link" href="homepage.php">Homepage</a>
                <a class="nav-link" href="#">Popolari</a>
                <a class="nav-link" href="#">Profilo</a>  
                <a class="nav-link" href="#">Preferiti</a>
            </div>
            
            <!--Elementi a destra-->
            <div class="nav-right">
                <a class="nav-link bi bi-plus-circle" href="nuovopost.php"></a>
                <div class="dropdown">
                    <button class="btn btn-link nav-link dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item bi bi-person" href="utente.html"> Utente</a></li>
                        <li><a class="dropdown-item bi bi-gear" href="impostazioni.html"> Impostazioni</a></li>
                        <li><a class="dropdown-item bi bi-person-lock" href="privacy.html"> Privacy</a></li>
                        <li><a class="dropdown-item bi bi-box-arrow-right" href="logout.php"> Logout</a></li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- Seconda riga: Barra di ricerca  -->
        <div class="search-bar">  
            <form>
                <input  
                    type="search" 
                    class="form-control" 
                    placeholder="Cerca" 
                    aria-label="Search"
                > 
            </form>
        </div>
    
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
