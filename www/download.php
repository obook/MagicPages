<?php
/**
 * Nom : download.php
 * Description : Téléchargement protégé des fichiers APK. Demande un mot de
 *               passe à chaque téléchargement avant d'envoyer le fichier
 *               demandé. C'est le seul point d'accès aux .apk depuis le site.
 * Auteur : O. Booklage
 * Date : Juin 2026
 * Licence : MIT
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$appsDir = __DIR__ . '/apps';

/* Le fichier demandé peut arriver par l'URL (clic) ou par le formulaire. */
$project = $_GET['project'] ?? $_POST['project'] ?? '';
$file = $_GET['file'] ?? $_POST['file'] ?? '';

/*
 * Valider les noms reçus : uniquement des caractères simples, et un fichier
 * se terminant par ".apk". Cela bloque les tentatives de traversée de
 * dossier (pas de "/" ni de "..").
 */
if (!preg_match('/^[a-zA-Z0-9._-]+$/', $project)
    || !preg_match('/^[a-zA-Z0-9._-]+\.apk$/', $file)) {
    http_response_code(400);
    exit('Requête invalide.');
}

$chemin = $appsDir . '/' . $project . '/' . $file;

/*
 * Vérifier que le fichier existe réellement et qu'il reste bien à
 * l'intérieur du dossier apps (realpath neutralise les liens et "..").
 */
$cheminReel = realpath($chemin);
$baseReelle = realpath($appsDir);
if ($cheminReel === false
    || $baseReelle === false
    || strpos($cheminReel, $baseReelle . '/') !== 0
    || !is_file($cheminReel)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

/*
 * Déterminer la position de l'application dans la liste, dans le même ordre
 * que la page d'accueil. Cette position (1 pour la première) entre dans le
 * code d'accès automatique.
 */
$position = 0;
foreach (scanProjects($appsDir) as $i => $p) {
    if ($p['name'] === $project) {
        $position = $i + 1;
        break;
    }
}

/* Générer un jeton CSRF s'il n'existe pas encore. */
if (empty($_SESSION['jeton_csrf'])) {
    $_SESSION['jeton_csrf'] = bin2hex(random_bytes(32));
}

$erreur = '';
$autorise = false;

/*
 * Deux POST différents arrivent ici :
 *   - depuis l'accueil : seulement project/file (clic sur "Télécharger") ;
 *     il faut afficher le panneau de mot de passe ;
 *   - depuis le panneau : project/file + motdepasse ; il faut vérifier.
 * On ne valide donc le mot de passe que si le champ "motdepasse" est présent.
 * Le mot de passe est ainsi redemandé à chaque téléchargement.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['motdepasse'])) {
    $jetonRecu = $_POST['jeton_csrf'] ?? '';
    $motDePasse = $_POST['motdepasse'] ?? '';

    if (!hash_equals($_SESSION['jeton_csrf'], $jetonRecu)) {
        /* Jeton absent ou invalide : la session a probablement expiré. */
        $erreur = 'Session expirée, merci de réessayer.';
    } elseif (verifierMotDePasse($motDePasse, $position)) {
        $autorise = true;
    } else {
        $erreur = 'Code d\'accès incorrect.';
    }
}

/* Mot de passe correct : envoyer le fichier et terminer. */
if ($autorise) {
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="' . basename($cheminReel) . '"');
    header('Content-Length: ' . filesize($cheminReel));
    header('X-Content-Type-Options: nosniff');
    readfile($cheminReel);
    exit;
}

/* Sinon, afficher le formulaire de mot de passe. */
$pageTitle = 'Téléchargement protégé';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="fonts/fonts.css">
    <script src="theme-init.js"></script>
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/cosmos.css">
    <link rel="stylesheet" href="css/chrome.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-page">
        <?php if ($erreur !== ''): ?>
            <!-- Échec de la saisie : message seul et retour à l'accueil. -->
            <div class="login-card">
                <h1><span>&#x2726;</span> Téléchargement</h1>
                <p class="login-erreur"><?= htmlspecialchars($erreur) ?></p>
                <a class="bouton-accueil" href="index.php">Accueil</a>
            </div>
        <?php else: ?>
            <form class="login-card" method="post" action="download.php" autocomplete="on">
                <h1><span>&#x2726;</span> Téléchargement</h1>
                <p class="login-fichier"><?= htmlspecialchars($file) ?></p>

                <label for="motdepasse">Code d'accès</label>
                <input type="password"
                       id="motdepasse"
                       name="motdepasse"
                       autocomplete="current-password"
                       required
                       autofocus>

                <input type="hidden" name="project" value="<?= htmlspecialchars($project) ?>">
                <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                <input type="hidden" name="jeton_csrf" value="<?= htmlspecialchars($_SESSION['jeton_csrf']) ?>">

                <button type="submit">Télécharger</button>
                <a class="bouton-secondaire" href="index.php">Accueil</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
