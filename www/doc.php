<?php
/**
 * Nom : doc.php
 * Description : Affichage de la documentation Markdown d'une application.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 */

require_once __DIR__ . '/parsedown.php';
require_once __DIR__ . '/functions.php';

$appsDir = __DIR__ . '/apps';

$project = $_GET['project'] ?? '';

/* Valider le nom du projet : seuls les caractères simples sont autorisés */
if (!preg_match('/^[a-zA-Z0-9._-]+$/', $project)) {
    http_response_code(400);
    exit('Projet invalide.');
}

$projectDir = $appsDir . '/' . $project;
if (!is_dir($projectDir)) {
    http_response_code(404);
    exit('Projet non trouvé.');
}

$docFile = scanDocFile($projectDir);
if ($docFile === null) {
    http_response_code(404);
    exit('Aucune documentation trouvée.');
}

$shopUrl = scanShopUrl($projectDir);

$mdContent = file_get_contents($docFile);
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$htmlContent = $parsedown->text($mdContent);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($project) ?> - La Petite Souris</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="<?= versionne('theme-init.js') ?>"></script>
    <link rel="stylesheet" href="<?= versionne('css/style.css') ?>">
</head>
<body>
    <div class="conteneur">
        <header class="entete">
            <div class="entete__barre">
                <p class="surtitre"><a href="index.php">Retour aux applications</a></p>
                <button class="theme" type="button" onclick="basculerTheme()" aria-label="Changer de thème">
                    <svg class="soleil" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 000-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>
                    <svg class="lune" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 109 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 01-4.4 2.26 5.403 5.403 0 01-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
                </button>
            </div>
            <h1 class="titre-page"><?= htmlspecialchars($project) ?></h1>
            <?php if (!empty($shopUrl)): ?>
                <p class="contexte">
                    <a href="<?= htmlspecialchars($shopUrl) ?>" target="_blank" rel="noopener">Acheter cette application</a>
                </p>
            <?php endif; ?>
        </header>

        <div class="doc">
            <?= $htmlContent ?>
        </div>

        <footer class="pied">
            <p>
                android.keosystems.com/magie/<br>
                &copy; <?= date('Y') ?> LaPetiteSouris.Net
            </p>
        </footer>
    </div>
    <script src="<?= versionne('theme.js') ?>"></script>
</body>
</html>
