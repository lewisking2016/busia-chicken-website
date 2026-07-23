import gsap from 'gsap';

function setupUserActions() {
  const buttons = document.querySelectorAll('.user-action-btn');
  buttons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      const action = button.dataset.action;
      gsap.fromTo(
        button,
        { scale: 1 },
        { scale: 0.96, duration: 0.1, yoyo: true, repeat: 1 }
      );
      console.log(`User action: ${action}`);
    });
  });
}

function setupUserSearch() {
  const searchInput = document.getElementById('user-search');
  if (!searchInput) return;

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    document.querySelectorAll('.user-row').forEach((row) => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setupUserActions();
  setupUserSearch();
});
