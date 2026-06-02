<?php
/**
 * Nom : auth.php
 * Description : Vérification du mot de passe protégeant le téléchargement
 *               des fichiers APK. Le mot de passe est exigé à chaque
 *               téléchargement (aucune mémorisation en session).
 * Auteur : O. Booklage
 * Date : Juin 2026
 * Licence : MIT
 */

/* Démarrer la session si elle ne l'est pas déjà (utile pour le jeton CSRF). */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifier un mot de passe.
 * Renvoie true si le mot de passe figure dans la liste de secret.php, ou s'il
 * correspond au code du jour : jour (2 chiffres) + mois (2 chiffres), par
 * exemple "0206" le 2 juin.
 */
function verifierMotDePasse(string $motDePasse): bool
{
    $motsDePasse = require __DIR__ . '/secret.php';

    /* Code du jour : "dm" donne le jour puis le mois, chacun sur 2 chiffres. */
    $motsDePasse[] = date('dm');

    $valide = false;
    foreach ($motsDePasse as $motAutorise) {
        /* hash_equals compare en temps constant (anti-attaque temporelle). */
        if (hash_equals($motAutorise, $motDePasse)) {
            $valide = true;
        }
    }

    return $valide;
}
