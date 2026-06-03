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

$mdFiles = glob($projectDir . '/*.md');
/* SHOP.md n'est pas de la documentation : il ne contient que l'URL d'achat. */
$mdFiles = array_values(array_filter($mdFiles, function ($file) {
    return strcasecmp(basename($file), 'SHOP.md') !== 0;
}));
if (empty($mdFiles)) {
    http_response_code(404);
    exit('Aucune documentation trouvée.');
}

$shopUrl = scanShopUrl($projectDir);

$mdContent = file_get_contents($mdFiles[0]);
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$htmlContent = $parsedown->text($mdContent);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project) ?> — Grimoire</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="fonts/fonts.css">
    <script src="theme-init.js"></script>
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/cosmos.css">
    <link rel="stylesheet" href="css/chrome.css">
    <link rel="stylesheet" href="css/markdown.css">
    <link rel="stylesheet" href="css/doc.css">
</head>
<body>
    <!-- ── Arrière-plan SVG animé ── -->
    <svg class="bg-cosmos"
         viewBox="0 0 1000 1000"
         aria-hidden="true"
         preserveAspectRatio="xMidYMid slice">
        <defs>
            <radialGradient id="nebula1" cx="25%" cy="35%" r="40%">
                <stop offset="0%" stop-color="#2a1050" stop-opacity="0.4"/>
                <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="nebula2" cx="70%" cy="60%" r="35%">
                <stop offset="0%" stop-color="#0a2a4a" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
            </radialGradient>
            <filter id="starGlow" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="2" result="blur"/>
                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
            <pattern id="sacredGrid" x="0" y="0" width="120" height="120" patternUnits="userSpaceOnUse">
                <line x1="60" y1="0" x2="60" y2="120" stroke="#8b5cf6" stroke-width="0.15" opacity="0.1"/>
                <line x1="0" y1="60" x2="120" y2="60" stroke="#8b5cf6" stroke-width="0.15" opacity="0.1"/>
                <circle cx="60" cy="60" r="40" fill="none" stroke="#d4a844" stroke-width="0.2" opacity="0.05"/>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#sacredGrid)"/>
        <rect width="100%" height="100%" fill="url(#nebula1)"/>
        <rect width="100%" height="100%" fill="url(#nebula2)"/>
        <g filter="url(#starGlow)">
            <circle cx="8%" cy="12%" r="1" fill="#d4a844" opacity="0.5"/>
            <circle cx="35%" cy="50%" r="0.9" fill="#8b5cf6" opacity="0.4"/>
            <circle cx="55%" cy="25%" r="1" fill="#e8e2f4" opacity="0.45"/>
            <circle cx="78%" cy="40%" r="0.8" fill="#d4a844" opacity="0.4"/>
            <circle cx="90%" cy="70%" r="1" fill="#8b5cf6" opacity="0.35"/>
            <circle cx="22%" cy="80%" r="0.9" fill="#34d399" opacity="0.3"/>
        </g>
    </svg>

    <div class="container">
        <div class="top-bar">
            <span class="top-bar-title">La Petite Souris</span>
            <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer de thème">
                <svg class="icon-sun" viewBox="0 0 24 24"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 000-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24"><path d="M12 3a9 9 0 109 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 01-4.4 2.26 5.403 5.403 0 01-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
            </button>
        </div>
        <header>
            <a class="back-link" href="index.php">
                <svg viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                Retour
            </a>
            <h1><span><?= htmlspecialchars($project) ?></span></h1>
            <?php if (!empty($shopUrl)): ?>
                <a class="shop-link" href="<?= htmlspecialchars($shopUrl) ?>" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Acheter
                </a>
            <?php endif; ?>
        </header>

        <div class="markdown-body">
            <?= $htmlContent ?>
        </div>
    </div>

    <script src="magic.js"></script>
</body>
</html>
