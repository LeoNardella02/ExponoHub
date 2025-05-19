<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovopost - ExponoHub</title>
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
            
            <p class="titolo"> Carica foto </p>

            <div class="caricafoto">
                <div class="immagine">
                    <label for="immagine0" id="etichetta-immagine0">
                        <i class="iconaimmagine bi bi-image"></i>
                        <img id="anteprima0" class="anteprima" />
                    </label>
                    <input id="immagine0" name="immagine0" type="file" accept="image/*" onchange="previewImage(event, 0)">
                </div>
                <div class="immagine">
                    <label for="immagine1" id="etichetta-immagine1">
                        <i class="iconaimmagine bi bi-image"></i>
                        <img id="anteprima1" class="anteprima" />
                    </label>
                    <input id="immagine1" name="immagine1" type="file" accept="image/*" onchange="previewImage(event, 1)">
                </div>
                <div class="immagine">
                    <label for="immagine2" id="etichetta-immagine2">
                        <i class="iconaimmagine bi bi-image"></i>
                        <img id="anteprima2" class="anteprima" />
                    </label>
                    <input id="immagine2" name="immagine2" type="file" accept="image/*" onchange="previewImage(event, 2)">
                </div>
                <div class="immagine">
                    <label for="immagine3" id="etichetta-immagine3">
                        <i class="iconaimmagine bi bi-image"></i>
                        <img id="anteprima3" class="anteprima" />
                    </label>
                    <input id="immagine3" name="immagine3" type="file" accept="image/*" onchange="previewImage(event, 3)">
                </div>
                <div class="immagine">
                    <label for="immagine4" id="etichetta-immagine4">
                        <i class="iconaimmagine bi bi-image"></i>
                        <img id="anteprima4" class="anteprima" />
                    </label>
                    <input id="immagine4" name="immagine4" type="file" accept="image/*" onchange="previewImage(event, 4)">
                </div>

            </div>

            <button type="button" id="resetBtn" onclick="resetAllImages()">Reset immagini</button>
            <a style="font-size: 12px"> N.B. Puoi caricare immagini con una dimensione massima di 5 MByte. </a>

            <div id="editor" class="campodescrizione"></div>
            <input type="hidden" name="descrizione" id="descrizione">
            <br>

            <div>
                <label class="caricafile" for="file" id="filelabel"> <i id="scrittalabel"> Carica qui i tuoi file </i>
                    <input type="file" id="file" name="files[]" hidden accept=".3ds, .3mf, .ai, .amf, .bgcode, .blend, .cdr, .csv, .ctb, .dwg, .dxf, .f3d, .f3z, .factory, .fcstd, .gcode, .gif, .heic, .heif, .iges, .ini, .ino, .ipt, .jpeg, .jpg, .lys, .lyt, .obj, .pdf, .ply, .png, .py, .rsdoc, .scad, .shape, .shapr, .skp, .sl1, .sl1s, .sldasm, .sldprt, .slvs, .step, .stl, .stp, .studio3, .svg, .txt, .webp, .zip, .zpr" multiple>
                    <div id="list_file"></div>
                </label>
            </div>

            <button type="button" id="resetFileBtn" onclick="resetFiles()">Reset file</button>
            <br>

            <button type="submit" id="savebutton">PUBBLICA</button>
            <button type="button" id="exitbutton" onclick="salvabozza();"> ANNULLA </button>

        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        
        //Campo descrizione
        const toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
            ['code-block'],
            ['link', 'image', 'video'],

            [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
            [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
            [{ 'align': [] }],

            [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            [{ 'color': [] }],          // dropdown with defaults from theme
            
            ['clean']                                         // remove formatting button
        ];

        const quill = new Quill('#editor', {
            placeholder: 'Descrizione',
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });
        

        //Estrazioni dati dalla form
        const form = document.getElementById('formpost');
        form.addEventListener('submit', function () {
            document.getElementById('descrizione').value = quill.root.innerHTML;
        });;
        

        function salvabozza(){
            alert("Vuoi salvare la bozza?"); 
        }

        
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
            const baseHeight = 200;
            const extraHeightPerFile = 25;
            const newHeight = baseHeight + (files.length * extraHeightPerFile);

            //uploadBox.style.height = `${newHeight}px`; ///////////da controllare
        }

        resetBtn.onclick = function () {
            fileInput.value = "";             // Resetta i file
            listFile.innerHTML = "";          // Pulisce la lista
            label.style.display = "block";    // Mostra di nuovo il testo
        };


        //Evita l'invio dei dati al server alla pressione del tasto "enter"
        document.getElementById("formpost").addEventListener("keydown", function (e) {
            if (e.key === "Enter" && e.target.tagName === "INPUT" && e.target.type === "text") {
            e.preventDefault(); // blocca l'invio
            }
        });
        
    </script>
</body>
</html>