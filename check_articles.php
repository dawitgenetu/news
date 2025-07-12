<?php
require_once 'includes/config.php';

try {
    $stmt = $db->query("SELECT id, title, image_url, category FROM articles ORDER BY created_at DESC LIMIT 10");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent articles in database:\n";
    echo "==========================\n";
    
    foreach ($articles as $article) {
        echo "ID: " . $article['id'] . "\n";
        echo "Title: " . $article['title'] . "\n";
        echo "Category: " . ($article['category'] ?: 'No category') . "\n";
        echo "Image URL: " . $article['image_url'] . "\n";
        echo "---\n";
    }
    
    if (empty($articles)) {
        echo "No articles found in database.\n";
    }
    
    // Also check what categories exist
    echo "\nCategories in database:\n";
    echo "======================\n";
    $catStmt = $db->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($categories)) {
        echo "No categories found.\n";
    } else {
        foreach ($categories as $category) {
            echo "- $category\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?> 