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

let navigationReturnFocus = null;

const setNavigationDrawerState = (target, button, open) => {
  const scrim = document.querySelector('[data-nav-scrim]');
  const mobileTabs = document.querySelector('[data-mobile-tabs]');
  const closeButton = target.querySelector('.drawer-close');
  const isMobile = window.matchMedia('(max-width: 800px)').matches;
  button.setAttribute('aria-expanded', String(open));
  button.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
  target.classList.toggle('is-open', open);
  if (isMobile) target.classList.toggle('hidden', !open);
  document.body.classList.toggle('nav-drawer-open', open);
  document.body.classList.toggle('overflow-hidden', open && isMobile);
  if (mobileTabs) {
    mobileTabs.classList.toggle('pointer-events-none', open && isMobile);
    mobileTabs.classList.toggle('opacity-0', open && isMobile);
    mobileTabs.setAttribute('aria-hidden', String(open && isMobile));
    mobileTabs.inert = open && isMobile;
  }
  target.setAttribute('aria-hidden', String(!open && isMobile));
  target.inert = !open && isMobile;
  if (scrim) {
    scrim.hidden = !open;
    scrim.classList.toggle('hidden', !open);
    scrim.dataset.open = String(open);
  }

  if (open) {
    navigationReturnFocus = document.activeElement;
    closeButton?.focus();
  } else {
    if (navigationReturnFocus instanceof HTMLElement && navigationReturnFocus.isConnected) {
      navigationReturnFocus.focus();
    } else {
      button.focus();
    }
    navigationReturnFocus = null;
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

// Lucian UX/UI Prompt #439 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#439
const makeBookSkeleton = (sourceCard) => {
  const skeleton = sourceCard.cloneNode(false);
  skeleton.removeAttribute('id');
  skeleton.setAttribute('data-loading-indicator', 'skeleton');
  skeleton.setAttribute('aria-hidden', 'true');
  skeleton.classList.add('pointer-events-none', 'animate-pulse', 'motion-reduce:animate-none');
  skeleton.innerHTML = `
    <div class="book-cover mb-3 block aspect-[2/3] overflow-hidden rounded-xl bg-[#eef1f4]"></div>
    <div class="book-info grid gap-2 p-3"><span class="block h-3 w-[52px] rounded-full bg-[#eef1f4]"></span><span class="block h-3 w-[92%] rounded-full bg-[#eef1f4]"></span><span class="block h-3 w-[58%] rounded-full bg-[#eef1f4]"></span><div class="mt-0.5 flex items-center gap-2"><i class="size-5 rounded-full bg-[#eef1f4]"></i><i class="h-2.5 w-[86px] rounded-full bg-[#eef1f4]"></i></div></div>`;
  return skeleton;
};

const setLoadingState = (region, loading, kind = 'spinner') => {
  if (!region) return;

  const cards = Array.from(region.children).filter((child) => child.matches('.book-card'));
  region.setAttribute('aria-busy', String(loading));

  if (!loading) {
    region.querySelectorAll('[data-loading-indicator]').forEach((indicator) => indicator.remove());
    cards.forEach((card, index) => {
      card.hidden = false;
      card.classList.add('opacity-0', 'transition', 'duration-500', 'ease-out', 'motion-reduce:transition-none');
      if (index === 0) card.classList.add('delay-0');
      if (index === 1) card.classList.add('delay-100');
      if (index === 2) card.classList.add('delay-200');
      if (index >= 3) card.classList.add('delay-300');
      requestAnimationFrame(() => card.classList.replace('opacity-0', 'opacity-100'));
    });
    region.removeAttribute('data-loading-kind');
    return;
  }

  region.querySelectorAll('[data-loading-indicator]').forEach((indicator) => indicator.remove());
  cards.forEach((card) => { card.hidden = true; });
  region.setAttribute('data-loading-kind', kind);

  if (kind === 'skeleton' && cards[0]) {
    const skeletonRegion = document.createDocumentFragment();
    const count = Math.max(cards.length, 5);
    for (let index = 0; index < count; index += 1) {
      skeletonRegion.appendChild(makeBookSkeleton(cards[0]));
    }
    region.appendChild(skeletonRegion);
    return;
  }

  const spinner = document.createElement('div');
  spinner.setAttribute('data-loading-indicator', 'spinner');
  spinner.setAttribute('role', 'status');
  spinner.className = 'col-span-full flex min-h-40 items-center justify-center gap-2.5 text-[#64748b]';
  spinner.innerHTML = '<span class="size-5 animate-spin rounded-full border-2 border-[#cbd5e1] border-t-[#1e3a5f] motion-reduce:animate-none" aria-hidden="true"></span><span class="sr-only">Đang tải...</span>';
  region.appendChild(spinner);
};

window.setLoadingState = setLoadingState;

document.addEventListener('DOMContentLoaded', () => {
  const adminSidebar = document.querySelector('[data-admin-sidebar]');
  const adminSidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
  const adminShell = document.querySelector('[data-admin-shell]');

  if (adminSidebar && adminSidebarToggle && adminShell) {
    const setAdminSidebarCollapsed = (collapsed) => {
      adminSidebar.dataset.collapsed = String(collapsed);
      adminShell.classList.toggle('lg:grid-cols-[64px_minmax(0,1fr)]', collapsed);
      adminShell.classList.toggle('lg:grid-cols-[248px_minmax(0,1fr)]', !collapsed);
      adminSidebar.querySelectorAll('.admin-sidebar-label').forEach((label) => {
        label.classList.toggle('lg:pointer-events-none', collapsed);
        label.classList.toggle('lg:w-0', collapsed);
        label.classList.toggle('lg:translate-x-[-8px]', collapsed);
        label.classList.toggle('lg:opacity-0', collapsed);
      });
      adminSidebar.querySelectorAll('.admin-sidebar-link, .admin-sidebar-footer').forEach((link) => {
        link.classList.toggle('lg:justify-center', collapsed);
        link.classList.toggle('lg:px-0', collapsed);
        link.classList.toggle('lg:gap-0', collapsed);
      });
      adminSidebarToggle.setAttribute('aria-expanded', String(!collapsed));
      adminSidebarToggle.setAttribute('aria-label', collapsed ? 'Mở rộng thanh bên' : 'Thu gọn thanh bên');
      adminSidebarToggle.classList.toggle('lg:fixed', collapsed);
      adminSidebarToggle.classList.toggle('lg:left-14', collapsed);
      adminSidebarToggle.classList.toggle('lg:right-auto', collapsed);
      adminSidebarToggle.querySelector('.material-symbols-outlined')?.classList.toggle('rotate-180', collapsed);
    };

    adminSidebarToggle.addEventListener('click', () => {
      setAdminSidebarCollapsed(adminSidebar.dataset.collapsed !== 'true');
    });
  }

  document.querySelectorAll('.book-grid').forEach((region) => {
    region.setAttribute('aria-busy', 'false');
  });

  const cartForm = document.querySelector('form[action="/cart/update"]');
  if (cartForm) {
    cartForm.querySelectorAll('input[name^="quantity["]').forEach((input) => {
      input.addEventListener('change', () => {
        if (input.value === '' || input.form?.dataset.updating === 'true') return;
        input.form.dataset.updating = 'true';
        input.form.setAttribute('aria-busy', 'true');
        input.form.requestSubmit();
      });
    });

    cartForm.querySelector('aside button[type="submit"]:not([name])')?.remove();
  }

  document.querySelectorAll('form[action="/books"], form[action="/search"]').forEach((form) => {
    form.addEventListener('submit', () => {
      const bookGrid = document.querySelector('.book-grid');
      if (bookGrid) setLoadingState(bookGrid, true, 'skeleton');
    });
  });

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
      panel.classList.toggle('hidden', open);
    });
  });

  const drawer = document.querySelector('[data-drawer]');
  const drawerButton = drawer ? document.querySelector(`[aria-controls="${drawer.id}"]`) : null;
  const scrim = document.querySelector('[data-nav-scrim]');
  const closeButton = drawer?.querySelector('.drawer-close');

  if (drawer && drawerButton) {
    const isMobile = window.matchMedia('(max-width: 800px)').matches;
    drawer.setAttribute('aria-hidden', String(isMobile));
    drawer.inert = isMobile;
  }

  const closeDrawer = () => {
    if (drawer && drawerButton && drawer.classList.contains('is-open')) {
      setNavigationDrawerState(drawer, drawerButton, false);
    }
  };

  scrim?.addEventListener('click', closeDrawer);
  closeButton?.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer?.classList.contains('is-open')) closeDrawer();
  });

  window.addEventListener('resize', () => {
    if (!drawer || !drawerButton) return;
    const isMobile = window.matchMedia('(max-width: 800px)').matches;
    if (!isMobile) {
      drawer.classList.remove('is-open');
      drawer.classList.remove('hidden');
      drawerButton.setAttribute('aria-expanded', 'false');
      drawer.setAttribute('aria-hidden', 'false');
      drawer.inert = false;
      document.body.classList.remove('nav-drawer-open', 'overflow-hidden');
      if (scrim) {
        scrim.hidden = true;
        scrim.classList.add('hidden');
        scrim.dataset.open = 'false';
      }
    } else if (!drawer.classList.contains('is-open')) {
      drawer.classList.add('hidden');
      drawer.setAttribute('aria-hidden', 'true');
      drawer.inert = true;
    }
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
      item.classList.add('lazy-item', 'opacity-35', 'translate-y-1.5', 'transition-[opacity,transform]', 'duration-200', 'ease-out');
    });

    const showNextItems = () => {
      const nextItems = items.slice(visibleCount, visibleCount + step);
      nextItems.forEach((item) => {
        item.hidden = false;
        item.classList.add('is-visible', 'opacity-100', 'translate-y-0');
        item.classList.remove('opacity-35', 'translate-y-1.5');
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
