window.togglePasswordVisibility = (event, button) => {
  event.preventDefault();
  event.stopPropagation();

  const inputId = button.getAttribute('data-toggle-password');
  const input = inputId ? document.getElementById(inputId) : null;
  if (!input) return false;

  const shouldShow = input.type === 'password';
  input.type = shouldShow ? 'text' : 'password';
  button.setAttribute('aria-label', shouldShow ? 'Ẩn giá trị' : 'Hiện giá trị');
  button.setAttribute('aria-pressed', String(shouldShow));
  const icon = button.querySelector('.material-symbols-outlined');
  if (icon) icon.textContent = shouldShow ? 'visibility_off' : 'visibility';
  return false;
};

const setNavigationDrawerState = (target, button, open) => {
  const scrim = document.querySelector('[data-nav-scrim]');
  const closeButton = target.querySelector('.drawer-close');
  button.setAttribute('aria-expanded', String(open));
  button.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
  target.classList.toggle('is-open', open);
  document.body.classList.toggle('nav-drawer-open', open);
  if (scrim) scrim.hidden = !open;

  if (open) {
    closeButton?.focus();
  } else {
    button.focus();
  }
};

window.toggleNavigationMenu = (event, button) => {
  event.preventDefault();
  event.stopPropagation();
  const target = document.getElementById(button.getAttribute('aria-controls'));
  if (!target) return false;
  const expanded = button.getAttribute('aria-expanded') === 'true';
  if (target.matches('[data-drawer]')) {
    setNavigationDrawerState(target, button, !expanded);
  } else {
    button.setAttribute('aria-expanded', String(!expanded));
    target.classList.toggle('is-open', !expanded);
  }
  return false;
};

document.addEventListener('DOMContentLoaded', () => {
  const bookForm = document.querySelector('[data-book-form]');

  if (bookForm) {
    const editors = bookForm.querySelectorAll('[data-simple-editor]');
    editors.forEach((editor) => {
      const editorElement = editor.querySelector('[data-editor-content]');
      const input = editor.querySelector('[data-editor-input]') || editor.previousElementSibling;
      if (!editorElement || !input) return;

      const oldContent = editorElement.getAttribute('data-editor-content') || '';
      editorElement.innerHTML = oldContent;
      editor.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
          editorElement.focus();
          document.execCommand(button.getAttribute('data-editor-command'), false, button.getAttribute('data-editor-value') || null);
        });
      });
      bookForm.addEventListener('submit', () => {
        const html = editorElement.innerHTML.trim();
        input.value = html === '<br>' ? '' : html;
      });
    });
  }

  document.querySelectorAll('[data-accordion-trigger]').forEach((button) => {
    const panelId = button.getAttribute('aria-controls');
    const panel = panelId ? document.getElementById(panelId) : null;
    if (!panel) return;

    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!open));
      button.closest('.faq-item')?.classList.toggle('is-open', !open);
      panel.inert = open;
    });
  });

  const drawer = document.querySelector('[data-drawer]');
  const drawerButton = drawer ? document.querySelector(`[aria-controls="${drawer.id}"]`) : null;
  const scrim = document.querySelector('[data-nav-scrim]');
  const closeButton = drawer?.querySelector('.drawer-close');

  const closeDrawer = () => {
    if (drawer && drawerButton && drawer.classList.contains('is-open')) {
      setNavigationDrawerState(drawer, drawerButton, false);
    }
  };

  scrim?.addEventListener('click', closeDrawer);
  closeButton?.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeDrawer();
  });

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-toggle-password]');
    if (!button) return;

    event.preventDefault();
    const inputId = button.getAttribute('data-toggle-password');
    const input = inputId ? document.getElementById(inputId) : null;
    if (!input) return;

    const shouldShow = input.type === 'password';
    input.type = shouldShow ? 'text' : 'password';
    button.setAttribute('aria-label', shouldShow ? 'Ẩn giá trị' : 'Hiện giá trị');
    button.setAttribute('aria-pressed', String(shouldShow));
    const icon = button.querySelector('.material-symbols-outlined');
    if (icon) icon.textContent = shouldShow ? 'visibility_off' : 'visibility';
  });

  document.querySelectorAll('[data-show-more-list]').forEach((list) => {
    const listId = list.id;
    const showMoreButton = document.querySelector(`[data-show-more-target="${listId}"]`);
    const items = Array.from(list.children);
    const step = Number(list.getAttribute('data-show-more-step')) || 8;

    if (!showMoreButton || !listId || items.length <= step) {
      return;
    }

    let visibleCount = 0;
    items.forEach((item) => {
      item.hidden = true;
      item.classList.add('lazy-item');
    });

    const showNextItems = () => {
      const nextItems = items.slice(visibleCount, visibleCount + step);
      nextItems.forEach((item) => {
        item.hidden = false;
        item.classList.add('is-visible');
      });
      visibleCount += nextItems.length;

      if (visibleCount >= items.length) {
        showMoreButton.hidden = true;
      }
    };

    showMoreButton.addEventListener('click', showNextItems);
    showMoreButton.hidden = false;
    showNextItems();
  });

  document.querySelectorAll('.admin-table select[name="status"]').forEach((select) => {
    if (select.value !== 'completed') return;
    select.disabled = true;
    const submit = select.form?.querySelector('button[type="submit"]');
    if (submit) {
      submit.disabled = true;
      submit.textContent = 'Đã hoàn tất';
      submit.setAttribute('aria-label', 'Đơn hàng đã hoàn tất, không thể thay đổi');
    }
  });
});
