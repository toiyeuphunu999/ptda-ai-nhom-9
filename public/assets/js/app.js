document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.menu-toggle').forEach((button) => {
    const target = document.getElementById(button.getAttribute('aria-controls'));
    if (!target) return;
    button.addEventListener('click', () => {
      const expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!expanded));
      target.classList.toggle('is-open', !expanded);
    });
  });
});
