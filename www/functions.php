<?php
/**
 * Nom : functions.php
 * Description : Fonctions utilitaires pour le scan des applications et le formatage.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 */

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
 * Lire le README d'un dossier et en extraire le contenu, le chemin et la description.
 */
function scanReadme(string $dir, string $relPrefix, string $name): array
{
    $result = ['content' => null, 'path' => null, 'description' => null];

    $mdFiles = glob($dir . '/*.md');
    /* SHOP.md n'est pas de la documentation : il ne contient que l'URL d'achat. */
    $mdFiles = array_values(array_filter($mdFiles, function ($file) {
        return strcasecmp(basename($file), 'SHOP.md') !== 0;
    }));
    if (empty($mdFiles)) {
        return $result;
    }

    $result['content'] = file_get_contents($mdFiles[0]);
    $result['path'] = $relPrefix . $name . '/' . basename($mdFiles[0]);
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
