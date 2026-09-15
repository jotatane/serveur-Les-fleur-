<?php
// Désactiver l'affichage des erreurs directes pour ne pas corrompre le JSON en cas de petit souci
error_reporting(0);
ini_set('display_errors', '0');

// Définir l'en-tête pour indiquer qu'on renvoie du JSON proprement encodé en UTF-8
header('Content-Type: application/json; charset=utf-8');

/**
 * Fonction pour scanner proprement un dossier de manière récursive
 */
function scanFolder($baseDir, $ignoreList = ['.', '..', 'index.php', '.htaccess']) {
    $results = [];
    
    // Vérifier si le dossier racine existe
    if (!is_dir($baseDir)) {
        return $results;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    // Déterminer l'URL de base actuelle (protocole + domaine + chemin du script)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    // Éviter les doubles slashes si on est à la racine
    $baseUrl = $protocol . "://" . $host . ($scriptPath === '/' ? '' : $scriptPath) . "/";

    foreach ($iterator as $file) {
        $fileName = $file->getFilename();

        // Ignorer les fichiers non désirés
        if (in_array($fileName, $ignoreList) || strpos($fileName, '.') === 0) {
            continue;
        }

        $filePath = $file->getPathname();
        
        // Obtenir le chemin relatif propre par rapport au dossier de base (ex: mods/monmod.jar)
        $relativePath = str_replace('\\', '/', substr($filePath, strlen($baseDir) + 1));

        if ($file->isDir()) {
            // Optionnel : si vous voulez lister les dossiers vides ou laisser le launcher les créer
            continue;
        } else {
            // C'est un fichier : on récupère ses infos
            $results[] = [
                "path" => $relativePath,
                "checksumSHA1" => @sha1_file($filePath),
                "url" => $baseUrl . $relativePath
            ];
        }
    }

    return $results;
}

// Définition des répertoires principaux à analyser pour votre launcher Fabric
$foldersToScan = ['.']; // Ou spécifiez par exemple ['.'] si tout est à la racine du script
$fileList = [];

// Analyse du répertoire courant
$fileList = scanFolder('.');

// Si vous avez besoin de spécifier des dossiers ou fichiers de nettoyage (pour supprimer les vieux mods obsolètes)
$dirCheckUselessFiles = ["mods", "config"];
foreach ($dirCheckUselessFiles as $uselessDir) {
    if (is_dir($uselessDir)) {
        // Le launcher saura qu'il doit nettoyer ces dossiers si besoin
        // (Vous pouvez adapter cette structure selon ce que votre launcher attend)
    }
}

// Affichage du JSON final propre
echo json_encode($fileList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
?>