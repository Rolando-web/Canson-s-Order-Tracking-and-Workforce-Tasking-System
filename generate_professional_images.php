<?php

/**
 * Professional Inventory Product Image Generator
 * Creates clean, modern product images for Canson inventory
 */

$items = [
    ['file' => 'data-filer-box-blue.jpg',       'name' => 'Data Filer Box',       'sub' => 'Blue',            'bg' => [[41,98,255],[56,189,248]],    'dark' => true],
    ['file' => 'whiteboard-standard.jpg',        'name' => 'Whiteboard',           'sub' => 'Standard',        'bg' => [[240,240,240],[220,220,220]], 'dark' => false],
    ['file' => 'corrugated-board-sheets.jpg',    'name' => 'Corrugated Board',     'sub' => 'Sheets',          'bg' => [[217,119,6],[251,191,36]],    'dark' => true],
    ['file' => 'glue-industrial.jpg',            'name' => 'Industrial Glue',      'sub' => 'High Strength',   'bg' => [[234,179,8],[253,224,71]],    'dark' => false],
    ['file' => 'storage-box-large.jpg',          'name' => 'Storage Box',          'sub' => 'Large',           'bg' => [[75,85,99],[120,130,145]],    'dark' => true],
    ['file' => 'single-data-filer.jpg',          'name' => 'Single Data Filer',    'sub' => 'Best Seller',     'bg' => [[22,163,74],[74,222,128]],    'dark' => true],
    ['file' => 'storage-box-small.jpg',          'name' => 'Storage Box',          'sub' => 'Small',           'bg' => [[245,158,11],[251,191,36]],   'dark' => false],
    ['file' => 'storage-box-big.jpg',            'name' => 'Storage Box',          'sub' => 'Big Size',        'bg' => [[55,65,81],[90,100,116]],     'dark' => true],
    ['file' => 'a4-bond-paper.jpg',              'name' => 'A4 Bond Paper',        'sub' => 'Premium Quality', 'bg' => [[255,255,255],[235,235,235]], 'dark' => false],
    ['file' => 'double-data-filer.jpg',          'name' => 'Double Data Filer',    'sub' => 'Extra Capacity',  'bg' => [[220,38,38],[248,113,113]],   'dark' => true],
    ['file' => 'whiteboard-small.jpg',           'name' => 'Whiteboard',           'sub' => 'Small',           'bg' => [[235,240,245],[215,225,235]], 'dark' => false],
    ['file' => 'whiteboard-medium.jpg',          'name' => 'Whiteboard',           'sub' => 'Medium',          'bg' => [[242,245,248],[230,235,240]], 'dark' => false],
    ['file' => 'whiteboard-large.jpg',           'name' => 'Whiteboard',           'sub' => 'Large',           'bg' => [[250,250,250],[238,238,238]], 'dark' => false],
    ['file' => 'chip-board.jpg',                 'name' => 'Chip Board',           'sub' => 'Industrial Grade','bg' => [[160,82,45],[200,140,80]],    'dark' => true],
    ['file' => 'big-single-data-filer.jpg',      'name' => 'Big Single Filer',     'sub' => 'Extra Large',     'bg' => [[5,150,105],[16,185,129]],    'dark' => true],
];

$outputPath = __DIR__ . '/storage/app/public/inventory/';
if (!is_dir($outputPath)) mkdir($outputPath, 0755, true);

$fontFile = 'C:/Windows/Fonts/arial.ttf';
$fontBold = 'C:/Windows/Fonts/arialbd.ttf';
if (!file_exists($fontBold)) $fontBold = $fontFile;
$hasFont = file_exists($fontFile);

foreach ($items as $item) {
    $w = 600;
    $h = 600;
    $img = imagecreatetruecolor($w, $h);
    imageantialias($img, true);
    imagesavealpha($img, true);

    // Gradient background
    $c1 = $item['bg'][0];
    $c2 = $item['bg'][1];
    for ($y = 0; $y < $h; $y++) {
        $t = $y / $h;
        $r = (int)($c1[0] + ($c2[0] - $c1[0]) * $t);
        $g = (int)($c1[1] + ($c2[1] - $c1[1]) * $t);
        $b = (int)($c1[2] + ($c2[2] - $c1[2]) * $t);
        $col = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $w, $y, $col);
    }

    // Colours for text/shapes
    if ($item['dark']) {
        $white  = imagecolorallocate($img, 255, 255, 255);
        $faded  = imagecolorallocatealpha($img, 255, 255, 255, 60);
        $faded2 = imagecolorallocatealpha($img, 255, 255, 255, 90);
        $faded3 = imagecolorallocatealpha($img, 255, 255, 255, 110);
    } else {
        $white  = imagecolorallocate($img, 40, 40, 40);
        $faded  = imagecolorallocatealpha($img, 40, 40, 40, 60);
        $faded2 = imagecolorallocatealpha($img, 40, 40, 40, 90);
        $faded3 = imagecolorallocatealpha($img, 40, 40, 40, 110);
    }

    // ── Decorative background circles ──
    imagefilledellipse($img, $w / 2, (int)($h * 0.38), 260, 260, $faded3);
    imagefilledellipse($img, $w / 2, (int)($h * 0.38), 200, 200, $faded2);

    // ── Draw product icon (centred at ~38% height) ──
    $cx = $w / 2;
    $cy = (int)($h * 0.35);
    $s  = 100; // half-size

    $name = strtolower($item['name']);
    if (str_contains($name, 'whiteboard')) {
        // Whiteboard rectangle + stand
        imagefilledrectangle($img, $cx - $s, $cy - (int)($s*0.6), $cx + $s, $cy + (int)($s*0.5), $faded);
        imagerectangle($img, $cx - $s, $cy - (int)($s*0.6), $cx + $s, $cy + (int)($s*0.5), $white);
        // inner frame
        imagerectangle($img, $cx - $s + 8, $cy - (int)($s*0.6) + 8, $cx + $s - 8, $cy + (int)($s*0.5) - 8, $faded);
        // stand legs
        imageline($img, $cx - 30, $cy + (int)($s*0.5), $cx - 50, $cy + $s, $white);
        imageline($img, $cx + 30, $cy + (int)($s*0.5), $cx + 50, $cy + $s, $white);
        imagesetthickness($img, 2);
        imageline($img, $cx - 30, $cy + (int)($s*0.5), $cx - 50, $cy + $s, $white);
        imageline($img, $cx + 30, $cy + (int)($s*0.5), $cx + 50, $cy + $s, $white);
        imagesetthickness($img, 1);

    } elseif (str_contains($name, 'filer') || str_contains($name, 'filer box')) {
        // File folder shape
        $fw = $s; $fh = (int)($s * 0.8);
        // Back tab
        imagefilledrectangle($img, $cx - $fw, $cy - $fh, $cx - $fw + (int)($fw*0.6), $cy - $fh + 20, $faded);
        // Main body
        imagefilledrectangle($img, $cx - $fw, $cy - $fh + 18, $cx + $fw, $cy + $fh, $faded);
        imagerectangle($img, $cx - $fw, $cy - $fh + 18, $cx + $fw, $cy + $fh, $white);
        // Tab outline
        imageline($img, $cx - $fw, $cy - $fh, $cx - $fw + (int)($fw*0.6), $cy - $fh, $white);
        imageline($img, $cx - $fw, $cy - $fh, $cx - $fw, $cy - $fh + 18, $white);
        imageline($img, $cx - $fw + (int)($fw*0.6), $cy - $fh, $cx - $fw + (int)($fw*0.6) + 12, $cy - $fh + 18, $white);
        // Lines inside
        for ($i = 1; $i <= 3; $i++) {
            $ly = $cy - $fh + 18 + (int)(($fh * 2 - 18) * $i / 4);
            imageline($img, $cx - $fw + 20, $ly, $cx + $fw - 20, $ly, $faded2);
        }

    } elseif (str_contains($name, 'storage box')) {
        // 3D box
        $bw = $s; $bh = (int)($s * 0.7);
        // Front face
        imagefilledrectangle($img, $cx - $bw, $cy - 10, $cx + $bw - 30, $cy + $bh, $faded);
        imagerectangle($img, $cx - $bw, $cy - 10, $cx + $bw - 30, $cy + $bh, $white);
        // Top face
        imagefilledpolygon($img, [
            $cx - $bw, $cy - 10,
            $cx - $bw + 30, $cy - $bh,
            $cx + $bw, $cy - $bh,
            $cx + $bw - 30, $cy - 10
        ], 4, $faded2);
        imagepolygon($img, [
            $cx - $bw, $cy - 10,
            $cx - $bw + 30, $cy - $bh,
            $cx + $bw, $cy - $bh,
            $cx + $bw - 30, $cy - 10
        ], 4, $white);
        // Right face
        imagefilledpolygon($img, [
            $cx + $bw - 30, $cy - 10,
            $cx + $bw, $cy - $bh,
            $cx + $bw, $cy + $bh - 30,
            $cx + $bw - 30, $cy + $bh
        ], 4, $faded);
        imagepolygon($img, [
            $cx + $bw - 30, $cy - 10,
            $cx + $bw, $cy - $bh,
            $cx + $bw, $cy + $bh - 30,
            $cx + $bw - 30, $cy + $bh
        ], 4, $white);

    } elseif (str_contains($name, 'corrugated') || str_contains($name, 'chip')) {
        // Stacked sheets
        for ($i = 3; $i >= 0; $i--) {
            $off = $i * 8;
            imagefilledrectangle($img, $cx - $s + $off, $cy - (int)($s*0.5) + $off, $cx + $s + $off, $cy + (int)($s*0.5) + $off, $faded);
            imagerectangle($img, $cx - $s + $off, $cy - (int)($s*0.5) + $off, $cx + $s + $off, $cy + (int)($s*0.5) + $off, $white);
        }
        // Wavy lines for corrugation texture
        for ($ly = $cy - (int)($s*0.3); $ly <= $cy + (int)($s*0.3); $ly += 16) {
            for ($lx = $cx - $s + 35; $lx < $cx + $s - 10; $lx += 12) {
                imageline($img, $lx, $ly, $lx + 6, $ly - 5, $faded2);
                imageline($img, $lx + 6, $ly - 5, $lx + 12, $ly, $faded2);
            }
        }

    } elseif (str_contains($name, 'glue')) {
        // Bottle shape
        $bw2 = 35; $bh2 = (int)($s * 0.7);
        // Neck
        imagefilledrectangle($img, $cx - 15, $cy - $s, $cx + 15, $cy - (int)($s*0.4), $faded);
        imagerectangle($img, $cx - 15, $cy - $s, $cx + 15, $cy - (int)($s*0.4), $white);
        // Cap
        imagefilledrectangle($img, $cx - 20, $cy - $s - 20, $cx + 20, $cy - $s, $white);
        // Body
        imagefilledrectangle($img, $cx - $bw2 - 15, $cy - (int)($s*0.4), $cx + $bw2 + 15, $cy + $bh2, $faded);
        imagerectangle($img, $cx - $bw2 - 15, $cy - (int)($s*0.4), $cx + $bw2 + 15, $cy + $bh2, $white);
        // Label
        imagerectangle($img, $cx - $bw2, $cy - (int)($s * 0.1), $cx + $bw2, $cy + (int)($s * 0.35), $faded2);

    } elseif (str_contains($name, 'paper') || str_contains($name, 'bond')) {
        // Paper stack with page curl
        // shadow sheets
        for ($i = 2; $i >= 0; $i--) {
            $off = $i * 6;
            imagefilledrectangle($img, $cx - (int)($s*0.7) + $off, $cy - $s + $off, $cx + (int)($s*0.7) + $off, $cy + (int)($s*0.6) + $off, $faded);
        }
        // Main page
        imagefilledrectangle($img, $cx - (int)($s*0.7), $cy - $s, $cx + (int)($s*0.7), $cy + (int)($s*0.6), $faded);
        imagerectangle($img, $cx - (int)($s*0.7), $cy - $s, $cx + (int)($s*0.7), $cy + (int)($s*0.6), $white);
        // Lines on page
        for ($i = 1; $i <= 5; $i++) {
            $ly2 = $cy - $s + 25 + $i * 22;
            $lineW = ($i < 5) ? (int)($s * 0.5) : (int)($s * 0.3);
            imageline($img, $cx - (int)($s*0.5), $ly2, $cx - (int)($s*0.5) + $lineW * 2, $ly2, $faded2);
        }
        // Corner fold
        imagefilledpolygon($img, [
            $cx + (int)($s*0.7) - 25, $cy - $s,
            $cx + (int)($s*0.7), $cy - $s + 25,
            $cx + (int)($s*0.7), $cy - $s
        ], 3, $faded);
        imageline($img, $cx + (int)($s*0.7) - 25, $cy - $s, $cx + (int)($s*0.7), $cy - $s + 25, $white);
    }

    // ── Text ──
    if ($hasFont) {
        // Product name
        $fontSize = 34;
        $bbox = imagettfbbox($fontSize, 0, $fontBold, $item['name']);
        $tw = $bbox[2] - $bbox[0];
        imagettftext($img, $fontSize, 0, (int)(($w - $tw) / 2), (int)($h * 0.68), $white, $fontBold, $item['name']);

        // Subtitle
        $subSize = 20;
        $bbox2 = imagettfbbox($subSize, 0, $fontFile, $item['sub']);
        $tw2 = $bbox2[2] - $bbox2[0];
        imagettftext($img, $subSize, 0, (int)(($w - $tw2) / 2), (int)($h * 0.75), $faded, $fontFile, $item['sub']);

        // Bottom watermark
        imagettftext($img, 13, 0, $w - 105, $h - 22, $faded3, $fontFile, 'CANSON');

        // Top-left category badge
        $cat = str_contains(strtolower($item['name']), 'board') || str_contains(strtolower($item['name']), 'glue')
            ? 'RAW MATERIAL' : 'FINISHED GOOD';
        imagettftext($img, 11, 0, 24, 34, $faded2, $fontFile, $cat);
    } else {
        // Fallback built-in font
        $words = explode(' ', $item['name']);
        $yText = (int)($h * 0.65);
        foreach ($words as $word) {
            $twFallback = imagefontwidth(5) * strlen($word);
            imagestring($img, 5, (int)(($w - $twFallback)/2), $yText, $word, $white);
            $yText += 22;
        }
        $twSub = imagefontwidth(3) * strlen($item['sub']);
        imagestring($img, 3, (int)(($w - $twSub)/2), (int)($h * 0.75), $item['sub'], $faded);
    }

    // ── Thin inner border ──
    $borderCol = imagecolorallocatealpha($img, 255, 255, 255, 100);
    imagerectangle($img, 12, 12, $w - 13, $h - 13, $borderCol);

    // Save
    imagejpeg($img, $outputPath . $item['file'], 95);
    imagedestroy($img);
    echo "  Created: {$item['file']}\n";
}

echo "\nAll 15 product images generated in:\n  {$outputPath}\n";
