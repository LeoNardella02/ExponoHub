<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">
    <div class="container flex-column">

        <!-- Prima riga: Logo, link e pulsanti -->
        <div class="div">
            
            <!-- Nome sito -->
            <div class="nome">ExponoHub</div>

            <!--Elementi al centro-->
            <div class="nav-center">
                <a class="nav-link" href="homepage.php">Homepage</a>
                <a class="nav-link" href="#">Popolari</a>
                <a class="nav-link" href="#">Categorie</a>  
                <a class="nav-link" href="#">Preferiti</a>
            </div>
            
            <!--Elementi a destra-->
            <div class="nav-right">
                <a class="nav-link bi bi-plus-circle" href="nuovopost.php"></a>
                <a class="nav-link bi bi-bell-fill" href="index.user.notiche.html"></a>
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