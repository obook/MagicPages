/*
 * Nom : theme-init.js
 * Description : Applique le thème choisi avant le premier rendu, pour éviter
 *               le clignotement d'un thème à l'autre.
 * Auteur : O. Booklage
 * Date : Septembre 2026
 * Licence : MIT
 *
 * L'attribut n'est posé que si l'utilisateur a fait un choix. Sans choix, le
 * CSS décide seul : sombre par défaut, parchemin si le système le demande.
 * Le rendu est ainsi le même avec et sans JavaScript.
 */
(function () {
    var choix = localStorage.getItem('theme');
    if (choix === 'light' || choix === 'dark') {
        document.documentElement.setAttribute('data-theme', choix);
    }
})();
