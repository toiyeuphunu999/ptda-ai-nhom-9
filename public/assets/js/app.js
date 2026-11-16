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
});
