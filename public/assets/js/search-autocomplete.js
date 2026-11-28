// Lucian UX/UI Prompt #429 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#429
(function () {
  function init(form) {
    var input = form.querySelector('.u429-in');
    var commandButton = form.querySelector('[data-command-k]');
    var commandMod = commandButton?.querySelector('[data-command-mod]');
    var listbox = form.querySelector('[data-search-results]');
    if (!input || !listbox) return;

    var live = document.createElement('span');
    live.className = 'sr-only';
    live.setAttribute('role', 'status');
    live.setAttribute('aria-live', 'polite');
    form.appendChild(live);

    var uid = 'search' + Math.random().toString(36).slice(2, 7);
    var suggestions = [];
    var active = -1;
    var timer = null;
    var request = null;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var mac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent || '');
    if (commandMod) commandMod.textContent = mac ? '⌘' : 'Ctrl';
    commandButton?.setAttribute('aria-keyshortcuts', mac ? 'Meta+K' : 'Control+K');
    input.setAttribute('aria-controls', uid + '-results');
    listbox.id = uid + '-results';

    function escapeHtml(value) {
      return String(value).replace(/[&<>"']/g, function (character) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
      });
    }

    function highlight(value, query) {
      var safe = escapeHtml(value);
      var start = safe.toLowerCase().indexOf(escapeHtml(query).toLowerCase());
      if (start < 0) return safe;
      return safe.slice(0, start) + '<strong class="font-bold text-[#172033]">' + safe.slice(start, start + query.length) + '</strong>' + safe.slice(start + query.length);
    }

    function setOpen(open) {
      form.dataset.open = String(open);
      listbox.dataset.open = String(open);
      input.setAttribute('aria-expanded', String(open));
      if (!open) input.removeAttribute('aria-activedescendant');
    }

    function commandFeedback() {
      if (!commandButton) return;
      var keys = commandButton.querySelectorAll('.u471-key');
      commandButton.classList.remove('ring-2', 'ring-[#dbeafe]', 'scale-[.975]');
      void commandButton.offsetWidth;
      commandButton.classList.add('ring-2', 'ring-[#dbeafe]', 'scale-[.975]');
      keys.forEach(function (key, index) {
        key.classList.remove('translate-y-[3px]');
        if (!reducedMotion) window.setTimeout(function () { key.classList.add('translate-y-[3px]'); }, index * 50);
      });
      window.setTimeout(function () {
        commandButton.classList.remove('ring-2', 'ring-[#dbeafe]', 'scale-[.975]');
        keys.forEach(function (key) { key.classList.remove('translate-y-[3px]'); });
      }, reducedMotion ? 1 : 180);
    }

    function paint() {
      Array.prototype.forEach.call(listbox.querySelectorAll('[data-search-option]'), function (option, index) {
        var selected = index === active;
        option.setAttribute('aria-selected', String(selected));
        option.classList.toggle('bg-[#f1f5f9]', selected);
        option.querySelector('[data-search-enter]').classList.toggle('scale-100', selected);
        option.querySelector('[data-search-enter]').classList.toggle('opacity-100', selected);
        option.querySelector('[data-search-enter]').classList.toggle('scale-75', !selected);
        option.querySelector('[data-search-enter]').classList.toggle('opacity-0', !selected);
      });
      if (active >= 0 && suggestions[active]) input.setAttribute('aria-activedescendant', uid + '-' + active);
    }

    function render(query) {
      if (!suggestions.length) {
        listbox.innerHTML = '<div class="px-3 py-3 text-sm text-[#64748b]">Không tìm thấy sách phù hợp</div>';
        active = -1;
        setOpen(true);
        live.textContent = 'Không tìm thấy sách phù hợp';
        return;
      }

      listbox.innerHTML = suggestions.map(function (item, index) {
        return '<a id="' + uid + '-' + index + '" class="u429-o relative flex min-h-11 items-center gap-3 rounded-lg px-2.5 text-[#475569] no-underline transition duration-200 [transition-timing-function:cubic-bezier(.3,1.4,.5,1)] hover:bg-[#f8fafc]" href="' + escapeHtml(item.href) + '" role="option" aria-selected="false" data-search-option>' +
          '<span class="grid size-8 shrink-0 place-items-center rounded-lg bg-[#f1f5f9] text-[#64748b]"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span></span>' +
          '<span class="min-w-0 flex-1 truncate text-sm">' + highlight(item.title, query) + '<small class="mt-0.5 block truncate text-xs text-[#64748b]">' + escapeHtml(item.author || 'Sách Mộc Bản') + '</small></span>' +
          '<span class="u429-en material-symbols-outlined grid size-5 shrink-0 scale-75 place-items-center text-[16px] text-[#64748b] leading-none opacity-0 transition duration-300 [transition-timing-function:cubic-bezier(.34,1.7,.5,1)]" data-search-enter aria-hidden="true">keyboard_return</span>' +
          '</a>';
      }).join('');
      active = 0;
      setOpen(true);
      paint();
      live.textContent = suggestions.length + ' gợi ý tìm kiếm';
    }

    function loadSuggestions() {
      var query = input.value.trim();
      if (!query) {
        suggestions = [];
        listbox.innerHTML = '';
        setOpen(false);
        return;
      }
      if (request) request.abort();
      request = new AbortController();
      fetch('/search?q=' + encodeURIComponent(query), {headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: request.signal})
        .then(function (response) { return response.text(); })
        .then(function (html) {
          var parsed = new DOMParser().parseFromString(html, 'text/html');
          suggestions = Array.prototype.slice.call(parsed.querySelectorAll('.book-card')).slice(0, 4).map(function (card) {
            var link = card.querySelector('a[href*="/books/"]');
            var title = card.querySelector('[title]')?.getAttribute('title') || card.querySelector('h3')?.textContent.trim() || '';
            var author = card.querySelector('.book-author')?.textContent.trim() || '';
            return {title: title, author: author, href: link ? link.getAttribute('href') : '/search?q=' + encodeURIComponent(query)};
          }).filter(function (item) { return item.title; });
          render(query);
        })
        .catch(function (error) { if (error.name !== 'AbortError') { suggestions = []; render(query); } });
    }

    input.addEventListener('input', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(loadSuggestions, reducedMotion ? 0 : 160);
    });
    input.addEventListener('click', function () { if (input.value.trim() && !form.dataset.open) loadSuggestions(); });
    commandButton?.addEventListener('click', function () {
      commandFeedback();
      input.focus({preventScroll: true});
      input.select();
    });
    input.addEventListener('keydown', function (event) {
      if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && suggestions.length) {
        event.preventDefault();
        if (!form.dataset.open) setOpen(true);
        active = (active + (event.key === 'ArrowDown' ? 1 : -1) + suggestions.length) % suggestions.length;
        paint();
      } else if (event.key === 'Enter' && form.dataset.open === 'true' && active >= 0 && suggestions[active]) {
        event.preventDefault();
        input.value = suggestions[active].title;
        setOpen(false);
      } else if (event.key === 'Escape' && form.dataset.open === 'true') {
        event.preventDefault();
        setOpen(false);
      }
    });
    document.addEventListener('keydown', function (event) {
      var target = event.target;
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k' && !event.altKey && !event.shiftKey) {
        event.preventDefault();
        commandFeedback();
        input.focus({preventScroll: true});
        input.select();
      }
    });
    listbox.addEventListener('pointermove', function (event) {
      var option = event.target.closest('[data-search-option]');
      if (!option) return;
      active = Array.prototype.indexOf.call(listbox.querySelectorAll('[data-search-option]'), option);
      paint();
    });
    document.addEventListener('pointerdown', function (event) { if (!form.contains(event.target)) setOpen(false); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-search-autocomplete]').forEach(init);
  });
}());
