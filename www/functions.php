<?php
/**
 * Nom : functions.php
 * Description : Fonctions utilitaires pour le scan des applications et le formatage.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 */

/** Adresse de contact proposée à qui n'a pas de code d'accès. */
const CONTACT_COURRIEL = 'olivier.booklage@lapetitesouris.net';

/**
 * Parcourir le dossier des applications et renvoyer les données structurées.
 */
function scanProjects(string $appsDir): array
{
    $projects = [];
    if (!is_dir($appsDir)) {
        return $projects;
    }

    $docRoot = rtrim(realpath(__DIR__), '/');
    $appsReal = rtrim(realpath($appsDir), '/');
    if ($appsReal === $docRoot) {
        $relPrefix = '';
    } else {
        $relPrefix = ltrim(str_replace($docRoot, '', $appsReal), '/') . '/';
    }

    $dirs = array_filter(glob($appsDir . '/*'), 'is_dir');
    sort($dirs);

    foreach ($dirs as $dir) {
        $name = basename($dir);

        $apkFiles = scanApkFiles($dir, $relPrefix, $name);
        $readmeData = scanReadme($dir, $relPrefix, $name);
        $shopUrl = scanShopUrl($dir);

        $icon = null;
        $pngFiles = glob($dir . '/*.png');
        if (!empty($pngFiles)) {
            $icon = $relPrefix . $name . '/' . basename($pngFiles[0]);
        }

        $projects[] = [
            'name'        => $name,
            'apks'        => $apkFiles,
            'readme'      => $readmeData['content'],
            'readmePath'  => $readmeData['path'],
            'icon'        => $icon,
            'description' => $readmeData['description'],
            'shopUrl'     => $shopUrl,
        ];
    }

    return $projects;
}

/**
 * Lister les fichiers APK d'un dossier, triés du plus récent au plus ancien.
 */
function scanApkFiles(string $dir, string $relPrefix, string $name): array
{
    $apks = glob($dir . '/*.apk');
    usort($apks, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $apkFiles = [];
    foreach ($apks as $apk) {
        $apkFiles[] = [
            'name'  => basename($apk),
            'path'  => $relPrefix . $name . '/' . basename($apk),
            'size'  => filesize($apk),
            'date'  => filemtime($apk),
        ];
    }

    return $apkFiles;
}

/**
 * Choisir le fichier de documentation d'un dossier d'application.
 * README.md est prioritaire : c'est le fichier publié avec l'application.
 * À défaut, le premier .md par ordre alphabétique. SHOP.md est écarté,
 * il ne contient que l'URL d'achat.
 */
function scanDocFile(string $dir): ?string
{
    $mdFiles = array_values(array_filter(glob($dir . '/*.md'), function ($file) {
        return strcasecmp(basename($file), 'SHOP.md') !== 0;
    }));
    if (empty($mdFiles)) {
        return null;
    }

    foreach ($mdFiles as $file) {
        if (strcasecmp(basename($file), 'README.md') === 0) {
            return $file;
        }
    }

    return $mdFiles[0];
}

/**
 * Lire le README d'un dossier et en extraire le contenu, le chemin et la description.
 */
function scanReadme(string $dir, string $relPrefix, string $name): array
{
    $result = ['content' => null, 'path' => null, 'description' => null];

    $docFile = scanDocFile($dir);
    if ($docFile === null) {
        return $result;
    }

    $result['content'] = file_get_contents($docFile);
    $result['path'] = $relPrefix . $name . '/' . basename($docFile);
    $result['description'] = extractDescription($result['content']);

    return $result;
}

/**
 * Lire l'URL d'achat depuis le fichier SHOP.md d'un dossier, s'il existe.
 * Renvoie la première ligne contenant une URL http(s), sinon null.
 */
function scanShopUrl(string $dir): ?string
{
    $shopFile = $dir . '/SHOP.md';
    if (!is_file($shopFile)) {
        return null;
    }

    $lines = preg_split('/\r?\n/', (string) file_get_contents($shopFile));
    foreach ($lines as $line) {
        $url = trim($line);
        if ($url !== '' && preg_match('#^https?://#i', $url)) {
            return $url;
        }
    }

    return null;
}

/**
 * Extraire le premier paragraphe après le premier titre Markdown.
 */
function extractDescription(?string $markdown): ?string
{
    if ($markdown === null) {
        return null;
    }

    $lines = preg_split('/\r?\n/', $markdown);
    $pastTitle = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (!$pastTitle && preg_match('/^#{1,2}\s/', $trimmed)) {
            $pastTitle = true;
            continue;
        }

        if ($pastTitle && $trimmed !== '' && !preg_match('/^#{1,6}\s/', $trimmed)) {
            /*
             * Ignorer les lignes d'images : beaucoup de README commencent par
             * une rangée de badges ou une capture d'écran, dont le texte de
             * remplacement ("License", "Version"...) ne décrit pas
             * l'application.
             */
            if (preg_replace('/!\[[^\]]*\]\([^)]*\)/', '', $trimmed) === '') {
                continue;
            }

            $cleaned = preg_replace('/\*\*(.+?)\*\*/', '$1', $trimmed);
            $cleaned = preg_replace('/\*(.+?)\*/', '$1', $cleaned);
            $cleaned = preg_replace('/`(.+?)`/', '$1', $cleaned);
            $cleaned = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $cleaned);
            return $cleaned;
        }
    }

    return null;
}

/**
 * Calculer le code à 2 chiffres propre à une application, à partir de son nom.
 * Il entre dans le code d'accès automatique décrit dans le dépôt privé
 * MagicPages-Private.
 */
function codeApplication(string $name): string
{
    preg_match_all('/\p{L}/u', $name, $lettres);
    return sprintf('%02d', count($lettres[0]) % 100);
}

/**
 * Formater une taille en octets en unité lisible (o, Ko, Mo).
 */
function formatSize(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 0, ',', ' ') . ' Ko';
    }
    return $bytes . ' o';
}
