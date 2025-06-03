//Campo descrizione
const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],       
    ['code-block'],
    ['link', 'video'],

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