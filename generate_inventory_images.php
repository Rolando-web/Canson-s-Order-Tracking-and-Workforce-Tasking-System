<?php

/**
 * Script to generate placeholder images for inventory items
 * Run: php generate_inventory_images.php
 */

$items = [
    ['file' => 'data-filer-box-blue.jpg', 'label' => 'Data Filer\nBox (Blue)', 'color' => [59, 130, 246]],
    ['file' => 'whiteboard-standard.jpg', 'label' => 'Whiteboard\n(Standard)', 'color' => [255, 255, 255]],
    ['file' => 'corrugated-board-sheets.jpg', 'label' => 'Corrugated\nBoard Sheets', 'color' => [210, 180, 140]],
    ['file' => 'glue-industrial.jpg', 'label' => 'Glue\n(Industrial)', 'color' => [255, 255, 200]],
    ['file' => 'storage-box-large.jpg', 'label' => 'Storage Box\n(Large)', 'color' => [156, 163, 175]],
    ['file' => 'single-data-filer.jpg', 'label' => 'Single\nData Filer', 'color' => [34, 197, 94]],
    ['file' => 'storage-box-small.jpg', 'label' => 'Storage Box\n(Small)', 'color' => [251, 191, 36]],
    ['file' => 'storage-box-big.jpg', 'label' => 'Storage Box\n(Big)', 'color' => [107, 114, 128]],
    ['file' => 'a4-bond-paper.jpg', 'label' => 'A4 Bond\nPaper', 'color' => [255, 255, 255]],
    ['file' => 'double-data-filer.jpg', 'label' => 'Double\nData Filer', 'color' => [239, 68, 68]],
    ['file' => 'whiteboard-small.jpg', 'label' => 'Whiteboard\n(Small)', 'color' => [245, 245, 245]],
    ['file' => 'whiteboard-medium.jpg', 'label' => 'Whiteboard\n(Medium)', 'color' => [243, 244, 246]],
    ['file' => 'whiteboard-large.jpg', 'label' => 'Whiteboard\n(Large)', 'color' => [249, 250, 251]],
    ['file' => 'chip-board.jpg', 'label' => 'Chip Board', 'color' => [245, 222, 179]],
    ['file' => 'big-single-data-filer.jpg', 'label' => 'Big Single\nData Filer', 'color' => [16, 185, 129]],
];

$outputPath = __DIR__ . '/storage/app/public/inventory/';

// Create directory if it doesn't exist
if (!is_dir($outputPath)) {
    mkdir($outputPath, 0755, true);
}

foreach ($items as $item) {
    // Create image 400x400
    $width = 400;
    $height = 400;
    $image = imagecreatetruecolor($width, $height);
    
    // Allocate colors
    $bgColor = imagecolorallocate($image, $item['color'][0], $item['color'][1], $item['color'][2]);
    $textColor = imagecolorallocate($image, 50, 50, 50);
    $borderColor = imagecolorallocate($image, 200, 200, 200);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Add border
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
    imagerectangle($image, 1, 1, $width - 2, $height - 2, $borderColor);
    
    // Add text
    $lines = explode("\n", $item['label']);
    $fontSize = 5;
    $y = ($height / 2) - (count($lines) * 20 / 2);
    
    foreach ($lines as $line) {
        $textWidth = imagefontwidth($fontSize) * strlen($line);
        $x = ($width - $textWidth) / 2;
        imagestring($image, $fontSize, $x, $y, $line, $textColor);
        $y += 20;
    }
    
    // Save image
    $filePath = $outputPath . $item['file'];
    imagejpeg($image, $filePath, 90);
    imagedestroy($image);
    
    echo "Created: {$item['file']}\n";
}

echo "\nAll images created successfully in: {$outputPath}\n";
