<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $r = $pdo->query('SELECT @@datadir AS d');
    $datadir = $r->fetch(PDO::FETCH_ASSOC)['d'];
    echo "MySQL data dir: $datadir\n";
    
    // Convert path for Windows
    $dbDir = str_replace('\\\\', '/', $datadir) . 'it12_project';
    $dbDir2 = $datadir . 'it12_project';
    
    // Try both path formats
    foreach ([$dbDir, $dbDir2] as $dir) {
        echo "Checking: $dir\n";
        if (is_dir($dir)) {
            echo "Found DB directory: $dir\n";
            $files = scandir($dir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $full = $dir . '/' . $f;
                echo "  Deleting: $full\n";
                @unlink($full);
            }
        }
    }
    
    $pdo->exec('DROP DATABASE IF EXISTS it12_project');
    echo "Database dropped.\n";
    $pdo->exec('CREATE DATABASE it12_project');
    echo "Database recreated.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
