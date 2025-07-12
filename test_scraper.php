<?php
// test_scraper.php
// Test script to verify scraper functionality

echo "Testing Borkena Scraper\n";
echo "======================\n\n";

// Test 1: Check if scraper file exists
if (file_exists('borkena_scraper.php')) {
    echo "✓ Scraper file exists\n";
} else {
    echo "✗ Scraper file not found\n";
    exit(1);
}

// Test 2: Check if we can run the scraper
echo "\nRunning scraper test...\n";
echo "This will take a few minutes to complete.\n\n";

// Capture output from scraper
ob_start();
include 'borkena_scraper.php';
$output = ob_get_clean();

echo "Scraper output:\n";
echo "===============\n";
echo $output;

// Test 3: Check if JSON file was created
if (file_exists('borkena_articles.json')) {
    $jsonContent = file_get_contents('borkena_articles.json');
    $articles = json_decode($jsonContent, true);
    
    if ($articles && is_array($articles)) {
        echo "\n✓ JSON file created successfully\n";
        echo "Total articles scraped: " . count($articles) . "\n";
        
        // Analyze categories
        $categories = [];
        foreach ($articles as $article) {
            $cat = $article['category'] ?? 'Unknown';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;
        }
        
        echo "\nCategory breakdown:\n";
        foreach ($categories as $category => $count) {
            echo "- $category: $count articles\n";
        }
        
        // Check for content quality
        $withContent = 0;
        $withImages = 0;
        $withDates = 0;
        
        foreach ($articles as $article) {
            if (!empty($article['content'])) $withContent++;
            if (!empty($article['image_url'])) $withImages++;
            if (!empty($article['date_published'])) $withDates++;
        }
        
        echo "\nContent quality:\n";
        echo "- Articles with content: $withContent/" . count($articles) . "\n";
        echo "- Articles with images: $withImages/" . count($articles) . "\n";
        echo "- Articles with dates: $withDates/" . count($articles) . "\n";
        
        // Show sample article
        if (!empty($articles)) {
            $sample = $articles[0];
            echo "\nSample article:\n";
            echo "Title: " . $sample['title'] . "\n";
            echo "Category: " . $sample['category'] . "\n";
            echo "URL: " . $sample['url'] . "\n";
            echo "Content length: " . strlen($sample['content']) . " characters\n";
        }
        
    } else {
        echo "✗ JSON file is invalid or empty\n";
    }
} else {
    echo "✗ JSON file was not created\n";
}

// Test 4: Check log file
if (file_exists('borkena_scraper.log')) {
    echo "\n✓ Log file created\n";
    $logContent = file_get_contents('borkena_scraper.log');
    echo "Log file size: " . strlen($logContent) . " bytes\n";
} else {
    echo "\n✗ Log file not found\n";
}

echo "\nTest completed!\n";
?> 