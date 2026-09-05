// Rich-text editor for the post Body field. Purely a client-side editing aid —
// the actual XSS defense is server-side HTML sanitization (see sanitize_html()
// in app/Helpers/sanitize.php), since anyone can bypass this editor entirely by
// posting straight to the controller. This script only has to keep the real
// <textarea> in sync so form submission (including the SPA's AJAX submit,
// which reads the DOM via FormData) always sees the current content.
(function () {
  var currentEditor = null;
  var currentTextarea = null;

  function destroyCurrent() {
    if (!currentEditor) return;
    var editor = currentEditor;
    currentEditor = null;
    editor.destroy().catch(function () {});
  }

  function init(root) {
    destroyCurrent();

    var textarea = (root || document).querySelector('#field-body');
    currentTextarea = textarea;
    if (!textarea || typeof ClassicEditor === 'undefined') return;

    ClassicEditor.create(textarea, {
      toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
    }).then(function (editor) {
      currentEditor = editor;
      editor.model.document.on('change:data', function () {
        textarea.value = editor.getData();
        clearFieldError(textarea);
      });
    }).catch(function (err) {
      console.error('CKEditor failed to load:', err);
    });
  }

  // --- Front-end validation for the create/edit post form ---
  // This is a UX convenience only — catching obvious mistakes before a round trip
  // to the server. It is not a security boundary: PostController re-validates and
  // sanitize_html() re-purifies everything server-side regardless of what happens here.

  function fieldGroup(el) {
    return el ? el.closest('.form-group') : null;
  }

  function clearFieldError(el) {
    var group = fieldGroup(el);
    if (!group) return;
    var msg = group.querySelector('[data-client-error]');
    if (msg) msg.remove();
    if (!group.querySelector('.form-error')) group.classList.remove('has-error');
  }

  function showFieldError(el, message) {
    var group = fieldGroup(el);
    if (!group) return;
    group.classList.add('has-error');
    var msg = document.createElement('p');
    msg.className = 'form-error';
    msg.setAttribute('data-client-error', '');
    msg.textContent = message;
    group.appendChild(msg);
  }

  function bodyPlainText() {
    var html = currentEditor ? currentEditor.getData() : (currentTextarea ? currentTextarea.value : '');
    return html.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/gi, ' ').replace(/\s+/g, ' ').trim();
  }

  function formatMegabytes(bytes) {
    return Math.round((bytes / 1048576) * 10) / 10;
  }

  function validateSubmit(e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('post-editor')) return;

    var firstInvalid = null;
    function fail(el, message) {
      showFieldError(el, message);
      if (!firstInvalid) firstInvalid = el;
    }

    var titleEl = form.querySelector('[name="title"]');
    if (titleEl) {
      clearFieldError(titleEl);
      if (!titleEl.value.trim()) fail(titleEl, 'Title is required.');
    }

    if (currentTextarea && form.contains(currentTextarea)) {
      clearFieldError(currentTextarea);
      if (!bodyPlainText()) {
        showFieldError(currentTextarea, 'Body is required.');
        // The real <textarea> is hidden once CKEditor replaces it, so focus/scroll
        // its visible editing surface instead — a hidden element can't take focus.
        var group = fieldGroup(currentTextarea);
        var editable = group ? group.querySelector('.ck-editor__editable') : null;
        if (!firstInvalid) firstInvalid = editable || currentTextarea;
      }
    }

    var activeTab = form.querySelector('.cover-tab-buttons [data-tab-target].is-active');
    var fileInput = form.querySelector('input[name="cover_image_file"]');
    if (fileInput) {
      clearFieldError(fileInput);
      var isUploadTabActive = !activeTab || activeTab.getAttribute('data-tab-target') === 'upload';
      var maxSize = parseInt(fileInput.getAttribute('data-max-size') || '0', 10);
      var file = fileInput.files && fileInput.files[0];
      if (isUploadTabActive && file && maxSize && file.size > maxSize) {
        fail(fileInput, 'File is too large (max ' + formatMegabytes(maxSize) + ' MB).');
      }
    }

    if (firstInvalid) {
      e.preventDefault();
      e.stopImmediatePropagation();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (typeof firstInvalid.focus === 'function') firstInvalid.focus();
    }
  }

  // Capture phase so this runs — and can veto the submit — before admin-spa.js's
  // own (bubble-phase) submit handler starts an AJAX request with invalid data.
  document.addEventListener('submit', validateSubmit, true);

  document.addEventListener('input', function (e) {
    if (e.target.matches('.post-editor [name="title"], .post-editor input[name="cover_image_file"]')) {
      clearFieldError(e.target);
    }
  });
  document.addEventListener('change', function (e) {
    if (e.target.matches('.post-editor input[name="cover_image_file"]')) clearFieldError(e.target);
  });

  document.addEventListener('DOMContentLoaded', function () { init(document); });
  window.initPostEditor = init;
})();
