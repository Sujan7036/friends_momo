<?php
/**
 * Image Helper Functions
 * Maps menu items to local images
 */

function getMenuItemImage($itemName, $categoryName = '') {
    // Normalize the item name for comparison
    $normalizedName = strtolower(trim($itemName));
    
    // Image mappings based on your available images
    $imageMap = [
        // Momos
        'steam momo' => 'momo.png',
        'fried momo' => 'momo.png',
        'chilly momo' => 'momo.png',
        'momo' => 'momo.png',
        
        // Noodles
        'chowmein' => 'chowmin.png',
        'chowmin' => 'chowmin.png',
        'noodle' => 'chowmin.png',
        
        // Street Food
        'laphing' => 'laphing.png',
        'pani puri' => 'panipuri.png',
        'aaloo nimki' => 'allonimkin.png',
        
        // General food items
        'dal bhat' => 'food.png',
        'curry' => 'food.png',
        'chicken' => 'food.png',
        'mutton' => 'food.png',
        'vegetable' => 'food.png',
        'rice' => 'food.png',
    ];
    
    // Check for exact matches first
    if (isset($imageMap[$normalizedName])) {
        return $imageMap[$normalizedName];
    }
    
    // Check for partial matches
    foreach ($imageMap as $keyword => $image) {
        if (strpos($normalizedName, $keyword) !== false) {
            return $image;
        }
    }
    
    // Default fallback
    return 'food.png';
}

function getMenuItemImageUrl($itemName, $categoryName = '') {
    $imageName = getMenuItemImage($itemName, $categoryName);
    return ASSETS_URL . '/images/' . $imageName;
}
?>
