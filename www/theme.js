/*
 * Nom : theme.js
 * Description : Basculement entre le thème sombre et le thème clair.
 * Auteur : O. Booklage
 * Date : Septembre 2026
 * Licence : MIT
 */

/*
 * Le choix est toujours enregistré explicitement, dans un sens comme dans
 * l'autre : retirer l'attribut rendrait la main au système, et l'utilisateur
 * qui vient de demander le sombre se retrouverait en clair sur un appareil
 * réglé en clair.
 */
function basculerTheme() {
    var choisi = document.documentElement.getAttribute('data-theme');
    var estClair = choisi === 'light'
        || (choisi === null && window.matchMedia('(prefers-color-scheme: light)').matches);
    var nouveau = estClair ? 'dark' : 'light';

    document.documentElement.setAttribute('data-theme', nouveau);
    localStorage.setItem('theme', nouveau);
}
