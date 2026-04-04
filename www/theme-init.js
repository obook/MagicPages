/*
 * Nom : theme-init.js
 * Description : Appliquer le thème sauvegardé avant le rendu de la page.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 *
 * Ce script doit être chargé dans le <head> (sans defer ni async)
 * pour éviter un flash de thème incorrect au chargement.
 */
(function () {
  var theme = localStorage.getItem('theme');
  if (theme === 'light') {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
