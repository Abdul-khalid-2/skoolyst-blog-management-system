// Tag picker: type to search existing tags (click a suggestion to add it), or type a name
// nothing matches and press Enter/comma to add it as a new tag. Renders selected tags as
// removable chips and mirrors the selection into hidden tags[]/new_tags inputs the post
// controller already understands, so no backend changes are needed for this widget.
(function () {
  function initOne(root) {
    if (root.dataset.tagInputReady) return;
    root.dataset.tagInputReady = '1';

    var allTags = [];
    var selectedIds = [];
    try { allTags = JSON.parse(root.getAttribute('data-all-tags') || '[]'); } catch (e) {}
    try { selectedIds = JSON.parse(root.getAttribute('data-selected-tags') || '[]'); } catch (e) {}

    var chipsEl = root.querySelector('[data-tag-chips]');
    var searchEl = root.querySelector('[data-tag-search]');
    var suggestEl = root.querySelector('[data-tag-suggestions]');
    var hiddenHost = root.querySelector('[data-tag-hidden]');

    var selected = allTags.filter(function (t) { return selectedIds.indexOf(t.id) !== -1; });
    var newNames = [];

    function isSelected(id) {
      return selected.some(function (t) { return t.id === id; });
    }

    function makeChip(label, isNew, onRemove) {
      var chip = document.createElement('span');
      chip.className = 'tag-chip' + (isNew ? ' tag-chip-new' : '');
      chip.textContent = label;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'tag-chip-remove';
      btn.setAttribute('aria-label', 'Remove ' + label);
      btn.textContent = '×';
      btn.addEventListener('click', onRemove);
      chip.appendChild(btn);
      return chip;
    }

    function syncHidden() {
      hiddenHost.innerHTML = '';
      selected.forEach(function (t) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tags[]';
        input.value = t.id;
        hiddenHost.appendChild(input);
      });
      var newInput = document.createElement('input');
      newInput.type = 'hidden';
      newInput.name = 'new_tags';
      newInput.value = newNames.join(',');
      hiddenHost.appendChild(newInput);
    }

    function render() {
      chipsEl.innerHTML = '';
      selected.forEach(function (t) {
        chipsEl.appendChild(makeChip(t.name, false, function () {
          selected = selected.filter(function (x) { return x.id !== t.id; });
          render();
        }));
      });
      newNames.forEach(function (name) {
        chipsEl.appendChild(makeChip(name, true, function () {
          newNames = newNames.filter(function (n) { return n !== name; });
          render();
        }));
      });
      syncHidden();
    }

    function showSuggestions(query) {
      var q = query.trim().toLowerCase();
      if (!q) { suggestEl.hidden = true; suggestEl.innerHTML = ''; return; }

      var matches = allTags.filter(function (t) {
        return !isSelected(t.id) && t.name.toLowerCase().indexOf(q) !== -1;
      }).slice(0, 8);

      suggestEl.innerHTML = '';
      if (!matches.length) { suggestEl.hidden = true; return; }

      matches.forEach(function (t) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'tag-suggestion';
        item.textContent = t.name;
        item.addEventListener('mousedown', function (e) {
          e.preventDefault();
          selected.push(t);
          searchEl.value = '';
          suggestEl.hidden = true;
          render();
        });
        suggestEl.appendChild(item);
      });
      suggestEl.hidden = false;
    }

    function addFromInput() {
      var name = searchEl.value.trim();
      if (!name) return;

      var existing = allTags.find(function (t) { return t.name.toLowerCase() === name.toLowerCase(); });
      if (existing) {
        if (!isSelected(existing.id)) selected.push(existing);
      } else if (!newNames.some(function (n) { return n.toLowerCase() === name.toLowerCase(); })) {
        newNames.push(name);
      }

      searchEl.value = '';
      suggestEl.hidden = true;
      render();
    }

    searchEl.addEventListener('input', function () { showSuggestions(searchEl.value); });

    searchEl.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addFromInput();
      } else if (e.key === 'Backspace' && searchEl.value === '') {
        if (newNames.length) {
          newNames.pop();
          render();
        } else if (selected.length) {
          selected.pop();
          render();
        }
      }
    });

    searchEl.addEventListener('blur', function () {
      setTimeout(function () { suggestEl.hidden = true; }, 150);
    });

    render();
  }

  function initAll(root) {
    (root || document).querySelectorAll('[data-tag-input]').forEach(initOne);
  }

  document.addEventListener('DOMContentLoaded', function () { initAll(document); });
  window.initTagInputs = initAll;
})();
