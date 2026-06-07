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
 * correspond au code automatique de l'application : jour (2 chiffres) +
 * position dans la liste (2 chiffres) + mois (2 chiffres). Par exemple, le
 * 7 juin, la 1re application de la liste a pour code "070106".
 *
 * $position est le rang de l'application sur la page d'accueil (1 pour la
 * première). Il est calculé par l'appelant à partir du même ordre de tri.
 */
function verifierMotDePasse(string $motDePasse, int $position): bool
{
    /*
     * Charger la liste des mots de passe SI le fichier existe. S'il est
     * absent (par exemple non encore créé sur le serveur), on n'échoue pas :
     * seul le code automatique restera accepté, ce qui évite une erreur 500.
     */
    $motsDePasse = [];
    $fichierSecret = __DIR__ . '/secret.php';
    if (is_file($fichierSecret)) {
        $motsDePasse = require $fichierSecret;
    }

    /*
     * Code automatique propre à chaque application : jour + position + mois,
     * chacun sur 2 chiffres. La position est celle affichée au survol de
     * l'icône sur la page d'accueil.
     */
    $motsDePasse[] = date('d') . sprintf('%02d', $position) . date('m');

    $valide = false;
    foreach ($motsDePasse as $motAutorise) {
        /* hash_equals compare en temps constant (anti-attaque temporelle). */
        if (hash_equals($motAutorise, $motDePasse)) {
            $valide = true;
        }
    }

    return $valide;
}
