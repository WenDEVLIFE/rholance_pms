<?php
require 'config/database.php';

$dir = __DIR__ . '/assets/variants';
$items = scandir($dir);

$conn->query("TRUNCATE TABLE custom_product_variants");

foreach ($items as $folder) {
    if ($folder === '.' || $folder === '..') continue;
    
    $folderPath = $dir . '/' . $folder;
    if (is_dir($folderPath)) {
        
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                
                $variantName = pathinfo($file, PATHINFO_FILENAME);
                
                // Map folder name to product name exactly as it appears in the landing page H3 tags:
                // 1. Gate, 2. Water Tank (Stainless), 3. Table, 4. Lababo (Sink), 5. Stainless Letters
                // 6. Windows (Metal Frame), 7. Handrail, 8. Push Cart, 9. Carrier (Push Cart)
                // 10. Terrace (Metal Structure), 11. Upuan (Chair), 12. Laboratory Cabinet
                $productName = $folder; // Default
                $f = strtoupper($folder);
                
                if (strpos($f, 'GATES') !== false) $productName = 'Gate';
                elseif (strpos($f, 'WATER TANK') !== false) $productName = 'Water Tank (Stainless)';
                elseif (strpos($f, 'TABLE') !== false) $productName = 'Table';
                elseif (strpos($f, 'LABABO') !== false) $productName = 'Lababo (Sink)';
                elseif (strpos($f, 'LETTER') !== false) $productName = 'Stainless Letters';
                elseif (strpos($f, 'WINDOWS') !== false) $productName = 'Windows (Metal Frame)';
                elseif (strpos($f, 'HANDTRAIL') !== false || strpos($f, 'HANDRAIL') !== false) $productName = 'Handrail';
                elseif ($f === 'PUSH CART') $productName = 'Push Cart';
                elseif (strpos($f, 'CARRIER') !== false) $productName = 'Carrier (Push Cart)';
                elseif (strpos($f, 'TERRACE') !== false) $productName = 'Terrace (Metal Structure)';
                elseif (strpos($f, 'UPUAN') !== false) $productName = 'Upuan (Chair)';
                elseif (strpos($f, 'CABINET') !== false) $productName = 'Laboratory Cabinet';
                
                $desc = "Design variant for " . $productName;
                
                // Encode spaces for URL
                $urlFolder = rawurlencode($folder);
                $urlFile = rawurlencode($file);
                // But wait, it's easier to just use standard paths and let browser handle spaces
                // Or just replace spaces with %20
                $imgUrl = "assets/variants/" . str_replace('+', '%20', urlencode($folder)) . "/" . str_replace('+', '%20', urlencode($file));
                
                $stmt = $conn->prepare("INSERT INTO custom_product_variants (product_name, variant_name, description, image_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $productName, $variantName, $desc, $imgUrl);
                $stmt->execute();
            }
        }
    }
}

echo "Seeded variants successfully.";
?>
