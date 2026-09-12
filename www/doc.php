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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project) ?> - La Petite Souris</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="conteneur">
        <header class="entete">
            <p class="surtitre"><a href="index.php">Retour aux applications</a></p>
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
</body>
</html>
