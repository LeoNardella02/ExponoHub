<?php
require 'connessione.php';
session_start();

if (!isset($_SESSION['id_utente'])) {
    die("Accesso non autorizzato. Effettua il login.");
}

$user_id = $_SESSION['id_utente'];

if (!isset($_POST['id'])) {
    die("Post non trovato.");
}

$post_id = (int) $_POST['id'];

// Recupera il post
$stmt = $conn->prepare("SELECT * FROM post WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post inesistente o non autorizzato.");
}

// Recupera immagini
$imgStmt = $conn->prepare("SELECT id, path FROM immagini WHERE post_id = ?");
$imgStmt->bind_param("i", $post_id);
$imgStmt->execute();
$imgResult = $imgStmt->get_result();
$images = $imgResult->fetch_all(MYSQLI_ASSOC);

// Recupera file
$fileStmt = $conn->prepare("SELECT id, path FROM file WHERE post_id = ?");
$fileStmt->bind_param("i", $post_id);
$fileStmt->execute();
$fileResult = $fileStmt->get_result();
$files = $fileResult->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Post - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet" href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="divpost">
        <form id="formpost" method="POST" action="salva_modifiche_post.php" enctype="multipart/form-data">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">
            <h2 style="text-align: center; margin-top: 20px; font-weight: bold;">MODIFICA POST</h2>

            <!-- Titolo -->
            <p class="titolo"> Titolo del post </p>
            <input class="campotitolopost" type="text" id="titolo" name="titolo" required value="<?= htmlspecialchars($post['titolo']) ?>" placeholder="Titolo"><br><br>


            <!-- Immagini -->
            <p class="titolo">Immagini</p>
            <div class="caricafoto">
                <?php
                for ($i = 0; $i < 5; $i++):
                    $img = $images[$i] ?? null;
                    $src = $img ? ltrim(htmlspecialchars($img['path']), '/') : '';
                    $id_input = "immagine$i";
                    $id_label = "etichetta-immagine$i";
                    $id_preview = "anteprima$i";
                ?>
                <div class="immagine">
                    <label for="<?= $id_input ?>" id="<?= $id_label ?>">
                        <i class="iconaimmagine bi bi-image" style="<?= $img ? 'display:none;' : '' ?>"></i>
                    </label>
                    <label for="<?= $id_input ?>" id="<?= $id_label ?>">
                        <img id="<?= $id_preview ?>" src="<?= $src ?>" class="anteprima" style="<?= $src ? 'display:block;' : '' ?>"/>
                    </label>
                    <input id="<?= $id_input ?>" name="immagine<?= $i ?>" type="file" accept="image/*" onchange="previewImage(event, <?= $i ?>)">
                </div>
                <?php endfor; ?>
            </div>
            <button type="button" id="resetBtn" onclick="resetAllImages()">Reset immagini</button>
            <input type="hidden" name="reset_immagini" id="reset_immagini" value="0">
            <span id="maxSizeMessage" class="mobile-only">Max image size: 5MB</span>


            <!-- Campo descrizione -->
            <div id="editor" class="campodescrizione"><?= $post['descrizione'] ?></div>
            <input type="hidden" name="descrizione" id="descrizione">


            <!-- File -->
            <div>
                <div>
                    <p class="titolo" style="margin-top:20px;">File presenti nel post</p>
                    <?php if (!empty($files)): ?>
                    <ul id="fileListFromDB" >
                        <?php foreach ($files as $file): ?>
                            <li>
                                <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank">
                                    <?= basename($file['path']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                <?php endif; ?>
                </div>

                <label class="caricafile" for="file" id="filelabel"> <i id="scrittalabel"> Carica qui file aggiuntivi</i>
                    <input type="file" id="file" name="files[]" hidden multiple
                        accept=".3ds, .3mf, .ai, .amf, .bgcode, .blend, .cdr, .csv, .ctb, .dwg, .dxf, .f3d, .f3z, .factory, .fcstd, .gcode, .gif, .heic, .heif, .iges, .ini, .ino, .ipt, .jpeg, .jpg, .lys, .lyt, .obj, .pdf, .ply, .png, .py, .rsdoc, .scad, .shape, .shapr, .skp, .sl1, .sl1s, .sldasm, .sldprt, .slvs, .step, .stl, .stp, .studio3, .svg, .txt, .webp, .zip, .zpr"
                        onchange="showFileList(this)">
                    <div id="list_file" class="lista_filet"></div>
                </label>
            </div>
            <button type="button" id="resetBtn" onclick="resetExistingFiles()">Reset file già presenti</button>
            <input type="hidden" name="reset_file_esistenti" id="reset_file_esistenti" value="0">
            <button type="button" id="resetBtn" onclick="resetNewFiles()">Reset file aggiuntivi</button>

            <br>

            <button type="submit" id="savebutton">SALVA MODIFICHE</button>
            <button type="button" id="exitbutton" onclick="window.location.href='homepage.php'"> ANNULLA </button>

            <button type="submit" id="deletebutton" name="delete_post" value="1" style="float: right; margin-left: 10px;" onclick="return confirm('Sei sicuro di voler eliminare questo post? L\'operazione è irreversibile.')">
                ELIMINA POST
            </button>

        </form>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="quill.js"></script>
    <script>
        const form = document.getElementById('formpost');
        form.addEventListener('submit', function () {
            document.getElementById('descrizione').value = quill.root.innerHTML;
        });

        // Preview immagini e controllo dimensione
        function previewImage(event, index) {
            const input = event.target;
            const file = input.files[0];
            
            if (!file) return;

            const maxSize = 5 * 1024 * 1024; // 5MB in byte

            if (file.size > maxSize) {
                alert("La dimensione dell'immagine non deve superare i 5MB.");
                input.value = ""; // resetta il file input
                return;
            }

            const reader = new FileReader();

            reader.onload = function() {
                const img = document.getElementById(`anteprima${index}`);
                const icon = document.querySelector(`#etichetta-immagine${index} .iconaimmagine`);

                img.src = reader.result;
                img.style.display = 'block';
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        //Reset immagini
        function resetAllImages() {
            for (let i = 0; i < 5; i++) {
            const input = document.getElementById(`immagine${i}`);
            const img = document.getElementById(`anteprima${i}`);
            const icon = document.querySelector(`#etichetta-immagine${i} .iconaimmagine`);

            input.value = "";
            img.src = "";
            img.style.display = "none";
            if (icon) icon.style.display = "block";
            }
            
            // Imposta il flag per segnalare al PHP di eliminare le immagini
            document.getElementById("reset_immagini").value = "1";
        }

        // File
        const fileInput = document.getElementById("file");
        const listFile = document.getElementById("list_file");
        const label = document.getElementById("scrittalabel");

        fileInput.onchange = function () {
            label.style.display = "none";

            var files = Array.from(this.files);
            files = files.map(file => file.name);

            listFile.innerHTML = files.join('<br/>');
            const baseHeight = 200;
            const extraHeightPerFile = 25;
            const newHeight = baseHeight + (files.length * extraHeightPerFile);
        }

        // Reset file già presenti 
        function resetExistingFiles() {
            const fileListFromDB = document.getElementById("fileListFromDB");
            if (fileListFromDB) {
                fileListFromDB.innerHTML = "";
            }

            // Imposta il flag per segnalare al PHP di eliminare i file esistenti
            document.getElementById("reset_file_esistenti").value = "1";
        }

        // Reset file aggiuntivi 
        function resetNewFiles() {
            fileInput.value = ""; // resetta l'input
            listFile.innerHTML = ""; // svuota l'anteprima dei nuovi file
            label.style.display = "block"; // riappare la scritta "Carica qui file aggiuntivi"
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
