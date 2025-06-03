<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovopost - ExponoHub</title>
    <link rel="icon" href="immagini/logo.png">
    <link rel="stylesheet"  href="stile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="divpost">
        <form id="formpost" method="POST" action="uploadpost.php" enctype="multipart/form-data">
            <h2 style="text-align: center; margin-top: 20px; font-weight: bold;">NUOVO POST</h2>

            <p class="titolo"> Titolo del post </p>
            <input class="campotitolopost" type="text" id="titolo" name="titolo" required placeholder="Titolo"><br><br>
            
            <p class="titolo">Immagini</p>
            <div class="caricafoto">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="immagine">
                        <label for="immagine<?= $i ?>" id="etichetta-immagine<?= $i ?>">
                            <i class="iconaimmagine bi bi-image"></i>
                            <img id="anteprima<?= $i ?>" class="anteprima" style="display: none;" />
                        </label>
                        <input id="immagine<?= $i ?>" name="immagine<?= $i ?>" type="file" accept="image/*" onchange="previewImage(event, <?= $i ?>)">
                    </div>
                <?php endfor; ?>
            </div>

            <button type="button" id="resetBtn" onclick="resetAllImages()">Reset immagini</button>
            <span id="maxSizeMessage" class="mobile-only">Max image size: 5MB</span>
            
            <div id="editor" class="campodescrizione"></div>
                <input type="hidden" name="descrizione" id="descrizione">
            <br>
            
            <div>
                <label class="caricafile" for="file" id="filelabel"> <i id="scrittalabel"> Carica qui i tuoi file </i>
                    <input type="file" id="file" name="files[]" hidden accept=".3ds, .3mf, .ai, .amf, .bgcode, .blend, .cdr, .csv, .ctb, .dwg, .dxf, .f3d, .f3z, .factory, .fcstd, .gcode, .gif, .heic, .heif, .iges, .ini, .ino, .ipt, .jpeg, .jpg, .lys, .lyt, .obj, .pdf, .ply, .png, .py, .rsdoc, .scad, .shape, .shapr, .skp, .sl1, .sl1s, .sldasm, .sldprt, .slvs, .step, .stl, .stp, .studio3, .svg, .txt, .webp, .zip, .zpr" multiple>
                    <div id="list_file"></div>
                </label>
            </div>

            <button type="button" id="resetFileBtn" onclick="resetAllFile()">Reset file</button>
            <br>

            <button type="submit" id="savebutton">PUBBLICA</button>
            <button type="button" id="exitbutton" onclick="window.location.href='homepage.php'"> ANNULLA </button>

        </form>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="quill.js"></script>
    <script>
        //Estrazioni dati dalla form
        const form = document.getElementById('formpost');
        form.addEventListener('submit', function () {
            document.getElementById('descrizione').value = quill.root.innerHTML;
        });;
        
        //Preview immagini e controllo dimensione
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

            if (input.files && input.files[0]) {
                reader.readAsDataURL(input.files[0]);
            }
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
            if (icon) icon.style.display = "inline-block";
            }
        }

        //Anteprima file
        var fileInput = document.getElementById('file');
        var listFile = document.getElementById('list_file');
        var label = document.getElementById("scrittalabel");
        var resetBtn = document.getElementById("resetFileBtn");

        fileInput.onchange = function () {
            label.style.display = "none";
            var files = Array.from(this.files);
            files = files.map(file => file.name);
            listFile.innerHTML = files.join('<br/>');
        }

        // Reset file
        function resetAllFile() {
            fileInput.value = "";             // Resetta i file
            listFile.innerHTML = "";          // Pulisce la lista
            label.style.display = "block";    // Mostra di nuovo il testo
        };

        //Evita l'invio dei dati al server alla pressione del tasto "Enter"
        document.getElementById("formpost").addEventListener("keydown", function (e) {
            if (e.key === "Enter" && e.target.tagName === "INPUT" && e.target.type === "text") {
            e.preventDefault(); 
            }
        });
        
    </script>
</body>
</html>