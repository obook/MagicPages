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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($documentTitle) ?></title>
    <meta name="description" content="Applications Android de tours de magie, à télécharger avec un code d'accès.">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="conteneur">
        <header class="entete">
            <p class="surtitre">LaPetiteSouris.Net</p>
            <h1 class="titre-page">Applications</h1>
            <p class="contexte">
                <?= $nombre ?> application<?= $nombre > 1 ? 's' : '' ?> Android de tours de magie.
                Le téléchargement demande un code d'accès : pour en obtenir un, écrivez à
                <a href="mailto:<?= CONTACT_COURRIEL ?>?subject=Code%20d%27acc%C3%A8s"><?= CONTACT_COURRIEL ?></a>.
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
                                            <button class="btn <?= $apk['demo'] ? 'btn--secondary' : 'btn--primary' ?>" type="submit">Télécharger</button>
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
</body>
</html>
