// Rich-text editor for the post Body field. Purely a client-side editing aid —
// the actual XSS defense is server-side HTML sanitization (see sanitize_html()
// in app/Helpers/sanitize.php), since anyone can bypass this editor entirely by
// posting straight to the controller. This script only has to keep the real
// <textarea> in sync so form submission (including the SPA's AJAX submit,
// which reads the DOM via FormData) always sees the current content.
(function () {
  var currentEditor = null;

  function destroyCurrent() {
    if (!currentEditor) return;
    var editor = currentEditor;
    currentEditor = null;
    editor.destroy().catch(function () {});
  }

  function init(root) {
    destroyCurrent();

    var textarea = (root || document).querySelector('#field-body');
    if (!textarea || typeof ClassicEditor === 'undefined') return;

    ClassicEditor.create(textarea, {
      toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
    }).then(function (editor) {
      currentEditor = editor;
      editor.model.document.on('change:data', function () {
        textarea.value = editor.getData();
      });
    }).catch(function (err) {
      console.error('CKEditor failed to load:', err);
    });
  }

  document.addEventListener('DOMContentLoaded', function () { init(document); });
  window.initPostEditor = init;
})();
