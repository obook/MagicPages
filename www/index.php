<?php
/**
 * Nom : index.php
 * Description : Page d'accueil listant les applications Android disponibles.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 */

require_once __DIR__ . '/parsedown.php';
require_once __DIR__ . '/functions.php';

$appsDir = __DIR__ . '/apps';
$pageTitle = 'Applications';

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);

$projects = scanProjects($appsDir);
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
    <link rel="stylesheet" href="css/markdown.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- ── Arrière-plan SVG animé ── -->
    <svg class="bg-cosmos"
         viewBox="0 0 1000 1000"
         aria-hidden="true"
         preserveAspectRatio="xMidYMid slice">
        <defs>
            <radialGradient id="nebula1" cx="20%" cy="30%" r="40%">
                <stop offset="0%" stop-color="#2a1050" stop-opacity="0.5"/>
                <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="nebula2" cx="75%" cy="65%" r="35%">
                <stop offset="0%" stop-color="#0a2a4a" stop-opacity="0.4"/>
                <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="nebula3" cx="50%" cy="80%" r="30%">
                <stop offset="0%" stop-color="#1a0a30" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
            </radialGradient>
            <filter id="starGlow" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="2" result="blur"/>
                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
            <filter id="softGlow" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="4"/>
            </filter>
            <pattern id="sacredGrid" x="0" y="0" width="120" height="120" patternUnits="userSpaceOnUse">
                <line x1="60" y1="0" x2="60" y2="120" stroke="#8b5cf6" stroke-width="0.15" opacity="0.12"/>
                <line x1="0" y1="60" x2="120" y2="60" stroke="#8b5cf6" stroke-width="0.15" opacity="0.12"/>
                <circle cx="60" cy="60" r="40" fill="none" stroke="#d4a844" stroke-width="0.2" opacity="0.06"/>
                <line x1="0" y1="0" x2="120" y2="120" stroke="#d4a844" stroke-width="0.1" opacity="0.05"/>
                <line x1="120" y1="0" x2="0" y2="120" stroke="#d4a844" stroke-width="0.1" opacity="0.05"/>
            </pattern>
        </defs>

        <rect width="100%" height="100%" fill="url(#sacredGrid)"/>

        <rect width="100%" height="100%" fill="url(#nebula1)"/>
        <rect width="100%" height="100%" fill="url(#nebula2)"/>
        <rect width="100%" height="100%" fill="url(#nebula3)"/>

        <!-- Étoiles lointaines -->
        <g class="stars-distant">
            <circle cx="5%" cy="8%" r="0.6" fill="#e8e2f4" opacity="0.5"><!--<animate attributeName="opacity" values="0.5;0.15;0.5" dur="4s" repeatCount="indefinite"/--></circle>
            <circle cx="12%" cy="22%" r="0.4" fill="#d4a844" opacity="0.4"><!--<animate attributeName="opacity" values="0.4;0.1;0.4" dur="5.2s" repeatCount="indefinite"/--></circle>
            <circle cx="18%" cy="45%" r="0.5" fill="#e8e2f4" opacity="0.35"><!--<animate attributeName="opacity" values="0.35;0.08;0.35" dur="3.8s" repeatCount="indefinite"/--></circle>
            <circle cx="25%" cy="12%" r="0.7" fill="#8b5cf6" opacity="0.4"><!--<animate attributeName="opacity" values="0.4;0.12;0.4" dur="6s" repeatCount="indefinite"/--></circle>
            <circle cx="32%" cy="67%" r="0.4" fill="#e8e2f4" opacity="0.3"><!--<animate attributeName="opacity" values="0.3;0.08;0.3" dur="4.5s" repeatCount="indefinite"/--></circle>
            <circle cx="40%" cy="5%" r="0.5" fill="#d4a844" opacity="0.45"><!--<animate attributeName="opacity" values="0.45;0.1;0.45" dur="5.8s" repeatCount="indefinite"/--></circle>
            <circle cx="48%" cy="35%" r="0.6" fill="#e8e2f4" opacity="0.3"><!--<animate attributeName="opacity" values="0.3;0.05;0.3" dur="7s" repeatCount="indefinite"/--></circle>
            <circle cx="55%" cy="78%" r="0.4" fill="#8b5cf6" opacity="0.35"><!--<animate attributeName="opacity" values="0.35;0.1;0.35" dur="4.2s" repeatCount="indefinite"/--></circle>
            <circle cx="62%" cy="18%" r="0.5" fill="#e8e2f4" opacity="0.4"><!--<animate attributeName="opacity" values="0.4;0.08;0.4" dur="5.5s" repeatCount="indefinite"/--></circle>
            <circle cx="70%" cy="52%" r="0.7" fill="#d4a844" opacity="0.3"><!--<animate attributeName="opacity" values="0.3;0.06;0.3" dur="6.5s" repeatCount="indefinite"/--></circle>
            <circle cx="78%" cy="88%" r="0.4" fill="#e8e2f4" opacity="0.35"><!--<animate attributeName="opacity" values="0.35;0.1;0.35" dur="3.5s" repeatCount="indefinite"/--></circle>
            <circle cx="85%" cy="30%" r="0.5" fill="#8b5cf6" opacity="0.4"><!--<animate attributeName="opacity" values="0.4;0.12;0.4" dur="5s" repeatCount="indefinite"/--></circle>
            <circle cx="92%" cy="60%" r="0.6" fill="#e8e2f4" opacity="0.3"><!--<animate attributeName="opacity" values="0.3;0.05;0.3" dur="4.8s" repeatCount="indefinite"/--></circle>
            <circle cx="95%" cy="15%" r="0.4" fill="#d4a844" opacity="0.45"><!--<animate attributeName="opacity" values="0.45;0.1;0.45" dur="6.2s" repeatCount="indefinite"/--></circle>
            <circle cx="8%" cy="72%" r="0.5" fill="#34d399" opacity="0.25"><!--<animate attributeName="opacity" values="0.25;0.05;0.25" dur="7.5s" repeatCount="indefinite"/--></circle>
            <circle cx="38%" cy="90%" r="0.4" fill="#e8e2f4" opacity="0.3"><!--<animate attributeName="opacity" values="0.3;0.08;0.3" dur="4s" repeatCount="indefinite"/--></circle>
            <circle cx="65%" cy="42%" r="0.5" fill="#34d399" opacity="0.2"><!--<animate attributeName="opacity" values="0.2;0.04;0.2" dur="8s" repeatCount="indefinite"/--></circle>
            <circle cx="82%" cy="72%" r="0.6" fill="#d4a844" opacity="0.35"><!--<animate attributeName="opacity" values="0.35;0.08;0.35" dur="5.3s" repeatCount="indefinite"/--></circle>
        </g>

        <!-- Étoiles moyennes -->
        <!-- filter="url(#starGlow)" retiré : flou Gaussien recalculé chaque frame -->
        <g class="stars-medium">
            <circle cx="10%" cy="15%" r="1" fill="#d4a844" opacity="0.6"><!--<animate attributeName="opacity" values="0.6;0.15;0.6" dur="6s" repeatCount="indefinite"/--></circle>
            <circle cx="30%" cy="55%" r="0.9" fill="#8b5cf6" opacity="0.5"><!--<animate attributeName="opacity" values="0.5;0.1;0.5" dur="7s" repeatCount="indefinite" begin="1s"/--></circle>
            <circle cx="50%" cy="20%" r="1.1" fill="#e8e2f4" opacity="0.55"><!--<animate attributeName="opacity" values="0.55;0.12;0.55" dur="5s" repeatCount="indefinite" begin="2s"/--></circle>
            <circle cx="72%" cy="40%" r="0.8" fill="#d4a844" opacity="0.5"><!--<animate attributeName="opacity" values="0.5;0.1;0.5" dur="8s" repeatCount="indefinite" begin="0.5s"/--></circle>
            <circle cx="88%" cy="75%" r="1" fill="#8b5cf6" opacity="0.45"><!--<animate attributeName="opacity" values="0.45;0.08;0.45" dur="6.5s" repeatCount="indefinite" begin="3s"/--></circle>
            <circle cx="20%" cy="85%" r="0.9" fill="#34d399" opacity="0.35"><!--<animate attributeName="opacity" values="0.35;0.06;0.35" dur="9s" repeatCount="indefinite" begin="1.5s"/--></circle>
            <circle cx="60%" cy="70%" r="1" fill="#e8e2f4" opacity="0.4"><!--<animate attributeName="opacity" values="0.4;0.08;0.4" dur="7.5s" repeatCount="indefinite" begin="4s"/--></circle>
            <circle cx="45%" cy="50%" r="0.8" fill="#d4a844" opacity="0.5"><!--<animate attributeName="opacity" values="0.5;0.12;0.5" dur="5.5s" repeatCount="indefinite" begin="2.5s"/--></circle>
        </g>

        <!-- Particules flottantes -->
        <g class="particles">
            <circle cx="15%" cy="25%" r="1.5" fill="#8b5cf6" opacity="0.04">
                <!--<animateTransform attributeName="transform" type="translate" values="0,0; 20,-30; -10,10; 0,0" dur="25s" repeatCount="indefinite"/-->

            </circle>
            <circle cx="75%" cy="20%" r="2" fill="#d4a844" opacity="0.035">
                <!--<animateTransform attributeName="transform" type="translate" values="0,0; -15,25; 10,-15; 0,0" dur="30s" repeatCount="indefinite"/-->

            </circle>
            <circle cx="40%" cy="70%" r="1.8" fill="#34d399" opacity="0.03">
                <!--<animateTransform attributeName="transform" type="translate" values="0,0; 25,15; -20,-10; 0,0" dur="22s" repeatCount="indefinite"/-->

            </circle>
            <circle cx="85%" cy="55%" r="1.5" fill="#8b5cf6" opacity="0.035">
                <!--<animateTransform attributeName="transform" type="translate" values="0,0; -18,-20; 12,18; 0,0" dur="28s" repeatCount="indefinite"/-->

            </circle>
        </g>

        <!-- Géométrie sacrée centrale -->
        <g class="sigil-main" transform="translate(500, 450)" opacity="0.04">
            <g>
                <!--<animateTransform attributeName="transform" type="rotate" values="0;360" dur="180s" repeatCount="indefinite"/-->

                <circle cx="0" cy="0" r="280" fill="none" stroke="#8b5cf6" stroke-width="0.5"/>
                <circle cx="0" cy="0" r="260" fill="none" stroke="#d4a844" stroke-width="0.3" stroke-dasharray="4 8"/>
                <polygon points="0,-240 207.8,120 -207.8,120" fill="none" stroke="#d4a844" stroke-width="0.4"/>
                <polygon points="0,240 207.8,-120 -207.8,-120" fill="none" stroke="#d4a844" stroke-width="0.4"/>
                <circle cx="0" cy="0" r="180" fill="none" stroke="#8b5cf6" stroke-width="0.3"/>
                <circle cx="0" cy="0" r="100" fill="none" stroke="#d4a844" stroke-width="0.3" stroke-dasharray="2 6"/>
                <line x1="0" y1="-290" x2="0" y2="290" stroke="#8b5cf6" stroke-width="0.15"/>
                <line x1="-290" y1="0" x2="290" y2="0" stroke="#8b5cf6" stroke-width="0.15"/>
                <line x1="-205" y1="-205" x2="205" y2="205" stroke="#d4a844" stroke-width="0.1"/>
                <line x1="205" y1="-205" x2="-205" y2="205" stroke="#d4a844" stroke-width="0.1"/>
            </g>
        </g>

    </svg>

    <canvas id="shootingStars"></canvas>

    <div class="container">
        <div class="top-bar">
            <span class="top-bar-title">Les applications magiques de <b>LaPetiteSouris.Net</b></span>
            <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer de thème">
                <svg class="icon-sun" viewBox="0 0 24 24"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 000-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24"><path d="M12 3a9 9 0 109 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 01-4.4 2.26 5.403 5.403 0 01-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
            </button>
        </div>
        <header>
            <h1><span>&#x2726;</span> <?= htmlspecialchars($pageTitle) ?></h1>
            <p>Grimoire des applications Android</p>
            <?php if (!empty($projects)): ?>
                <span class="project-count"><?= count($projects) ?> application<?= count($projects) > 1 ? 's' : '' ?> magique<?= count($projects) > 1 ? 's' : '' ?> disponible<?= count($projects) > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </header>

        <!-- Bandeau d'information sur le code d'accès -->
        <div class="bandeau-info">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
            <p>Le téléchargement des applications nécessite un code d'accès</p>
        </div>

        <?php if (empty($projects)): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24"><path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/></svg>
                <p>Le grimoire est vide... aucune application trouvée.</p>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project">
                    <div class="project-header">
                        <div class="project-icon">
                            <?php if ($project['icon'] !== null): ?>
                                <img src="<?= htmlspecialchars($project['icon']) ?>" alt="<?= htmlspecialchars($project['name']) ?>">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24"><path d="M17.6 11.48l1.34-2.32c.07-.12.04-.27-.08-.34s-.27-.04-.34.08l-1.36 2.36C15.78 10.5 14.44 10.12 13 10.12s-2.78.38-4.16 1.14L7.48 8.9c-.07-.12-.22-.15-.34-.08s-.15.22-.08.34l1.34 2.32C5.55 13.01 3.84 15.74 3.5 19h17c-.34-3.26-2.05-5.99-4.9-7.52zM9.5 16.5c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm5 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
                            <?php endif; ?>
                        </div>
                        <span class="project-name"><?= htmlspecialchars($project['name']) ?></span>
                        <?php if ($project['readmePath'] !== null): ?>
                            <a class="doc-link" href="doc.php?project=<?= urlencode($project['name']) ?>">
                                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                Grimoire
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($project['description'])): ?>
                        <p class="project-description"><?= htmlspecialchars($project['description']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($project['apks'])): ?>
                        <div class="apk-list">
                            <?php foreach ($project['apks'] as $apk): ?>
                                <div class="apk-item">
                                    <span class="apk-badge">APK</span>
                                    <div class="apk-info">
                                        <div class="apk-name"><?= htmlspecialchars($apk['name']) ?></div>
                                        <div class="apk-meta"><?= formatSize($apk['size']) ?> &middot; <?= date('d/m/Y H:i', $apk['date']) ?></div>
                                    </div>
                                    <form class="apk-download-form" method="post" action="download.php">
                                        <input type="hidden" name="project" value="<?= htmlspecialchars($project['name']) ?>">
                                        <input type="hidden" name="file" value="<?= htmlspecialchars($apk['name']) ?>">
                                        <button class="apk-download" type="submit">
                                            <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                                            Télécharger
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="project share-card">
            <div class="project-header">
                <div class="project-icon">
                    <svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                </div>
                <span class="project-name">Partager</span>
            </div>
            <div class="share-content">
                <a href="https://android.keosystems.com/magie/" target="_blank" rel="noopener">
                    <img src="img/qrcode-share.svg" alt="QR Code" class="qrcode">
                </a>
                <p class="share-url">android.keosystems.com/magie/</p>
            </div>
        </div>

        <footer>
            <?= date('Y') ?>
        </footer>
    </div>

    <script src="magic.js"></script>
</body>
</html>
