<?php
/**
 * Nom : index.php
 * Description : Page d'accueil listant les applications Android disponibles.
 * Auteur : O. Booklage
 * Date : Septembre 2026
 * Licence : MIT
 */

require_once __DIR__ . '/functions.php';

$appsDir = __DIR__ . '/apps';
$documentTitle = 'Applications magiques - La Petite Souris';

$projects = scanProjects($appsDir);
$nombre = count($projects);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($documentTitle) ?></title>
    <meta name="description" content="Applications Android de tours de magie, à télécharger avec un code d'accès.">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="<?= versionne('theme-init.js') ?>"></script>
    <link rel="stylesheet" href="<?= versionne('css/style.css') ?>">
</head>
<body>
    <div class="conteneur">
        <header class="entete">
            <div class="entete__barre">
                <p class="surtitre">LaPetiteSouris.Net</p>
                <button class="theme" type="button" onclick="basculerTheme()" aria-label="Changer de thème">
                    <svg class="soleil" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 00-1.41 0 .996.996 0 000 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 000-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 000-1.41.996.996 0 00-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>
                    <svg class="lune" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 109 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 01-4.4 2.26 5.403 5.403 0 01-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
                </button>
            </div>
            <h1 class="titre-page">Applications</h1>
            <p class="contexte">
                <?= $nombre ?> application<?= $nombre > 1 ? 's' : '' ?> Android de tours de magie.
                Le téléchargement demande un code d'accès : pour en obtenir un, écrivez à
                <a href="mailto:<?= CONTACT_COURRIEL ?>?subject=Code%20d%27acc%C3%A8s">olivier.booklage</a>.
            </p>
        </header>

        <?php if (empty($projects)): ?>
            <p class="contexte">Aucune application n'est disponible pour le moment.</p>
        <?php else: ?>
            <ul class="applications">
                <?php foreach ($projects as $project): ?>
                    <?php
                    /* Une carte par APK : le statut et le bouton diffèrent selon
                       qu'il s'agit d'une démo ou d'une version complète. Une
                       application sans APK garde sa carte, sans pied. */
                    $apks = !empty($project['apks']) ? $project['apks'] : [null];
                    ?>
                    <?php foreach ($apks as $apk): ?>
                        <li>
                            <article class="app">
                                <header>
                                    <?php if ($project['icon'] !== null): ?>
                                        <img src="<?= htmlspecialchars($project['icon']) ?>" alt="" width="48" height="48">
                                    <?php endif; ?>
                                    <div>
                                        <h2><?= htmlspecialchars($project['name']) ?></h2>
                                        <?php if ($apk !== null): ?>
                                            <span class="badge <?= $apk['demo'] ? 'badge--demo' : 'badge--full' ?>"><?= $apk['demo'] ? 'Démo' : 'Version complète' ?></span>
                                        <?php endif; ?>
                                        <?php if ($project['readmePath'] !== null): ?>
                                            <a class="app__lien-doc" href="doc.php?project=<?= urlencode($project['name']) ?>">Grimoire</a>
                                        <?php endif; ?>
                                    </div>
                                </header>

                                <?php if (!empty($project['description'])): ?>
                                    <p class="app__desc"><?= htmlspecialchars($project['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($project['shopUrl'])): ?>
                                    <a class="achat" href="<?= htmlspecialchars($project['shopUrl']) ?>" target="_blank" rel="noopener">Acheter</a>
                                <?php endif; ?>

                                <?php if ($apk !== null): ?>
                                    <footer>
                                        <span class="app__meta">
                                            <?= formatSize($apk['size']) ?> &middot; <?= date('d.m.Y', $apk['date']) ?>
                                            <span class="app__fichier"><?= htmlspecialchars($apk['name']) ?></span>
                                        </span>
                                        <form method="post" action="download.php">
                                            <input type="hidden" name="project" value="<?= htmlspecialchars($project['name']) ?>">
                                            <input type="hidden" name="file" value="<?= htmlspecialchars($apk['name']) ?>">
                                            <button class="btn btn--primary" type="submit">Télécharger</button>
                                        </form>
                                    </footer>
                                <?php endif; ?>
                            </article>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <footer class="pied">
            <a href="https://android.keosystems.com/magie/">
                <img src="img/qrcode-share.svg" alt="QR code vers android.keosystems.com/magie" width="96" height="96">
            </a>
            <p>
                android.keosystems.com/magie/<br>
                &copy; <?= date('Y') ?> LaPetiteSouris.Net
            </p>
        </footer>
    </div>
    <script src="<?= versionne('theme.js') ?>"></script>
</body>
</html>
