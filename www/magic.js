/*
 * Nom : magic.js
 * Description : Basculement entre le thème sombre et le thème clair.
 * Auteur : O. Booklage
 * Date : Septembre 2026
 * Licence : MIT
 */

/* ── Basculement de thème ── */
function toggleTheme() {
  var estClair = document.documentElement.getAttribute('data-theme') === 'light';
  if (estClair) {
    document.documentElement.removeAttribute('data-theme');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.setAttribute('data-theme', 'light');
    localStorage.removeItem('theme');
  }
}
