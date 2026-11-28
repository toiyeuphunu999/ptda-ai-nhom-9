document.addEventListener('DOMContentLoaded', () => {
  const tabs = [...document.querySelectorAll('[data-mobile-tabs] [data-tab-index]')];
  if (!tabs.length) return;
  const select = (index, focus = false) => {
    tabs.forEach((tab, tabIndex) => {
      const active = tabIndex === index;
      tab.setAttribute('aria-selected', String(active));
      tab.setAttribute('aria-current', active ? 'page' : 'false');
      tab.tabIndex = active ? 0 : -1;
    });
    if (focus) tabs[index]?.focus();
  };
  tabs.forEach((tab, index) => tab.addEventListener('keydown', (event) => {
    const direction = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
    if (!direction) return;
    event.preventDefault();
    select((index + direction + tabs.length) % tabs.length, true);
  }));
});
