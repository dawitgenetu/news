<?php
// import_scraped_news.php
// Import articles from borkena_articles.json into the articles table
require_once 'includes/config.php';

$jsonFile = 'borkena_articles.json';
if (!file_exists($jsonFile)) {
    die("JSON file not found: $jsonFile\n");
}

$articles = json_decode(file_get_contents($jsonFile), true);
if (!$articles) {
    die("Failed to decode JSON or no articles found.\n");
}

// Category mapping to ensure consistency
$categoryMapping = [
    'news' => 'News',
    'politics' => 'Politics', 
    'business' => 'Business',
    'sport' => 'Sports',
    'sports' => 'Sports',
    'opinion' => 'Opinion',
    'video' => 'Video',
    'entertainment' => 'Entertainment',
    'society' => 'Society',
    'technology' => 'Technology',
    'health' => 'Health',
    'education' => 'Education',
    'international' => 'International'
];

function getCategoryId($db, $categoryName) {
    global $categoryMapping;
    
    // Normalize category name
    $normalizedName = strtolower(trim($categoryName));
    
    // Map to standard category name
    if (isset($categoryMapping[$normalizedName])) {
        $categoryName = $categoryMapping[$normalizedName];
    } else {
        // If not in mapping, use as is but capitalize first letter
        $categoryName = ucfirst($normalizedName);
    }
    
    // Check if category exists
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $categoryName));
    $stmt->execute([$categoryName, $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        return $row['id'];
    }
    
    // Create category if not exists
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    $description = $categoryName . ' news and updates';
    $stmt->execute([$categoryName, $slug, $description]);
    
    echo "Created new category: $categoryName\n";
    return $db->lastInsertId();
}

function cleanContent($content) {
    // Remove common footer text and boilerplate
    $patterns = [
        '/Subscribe\s*:\s*https:\/\/borkena\.com\/subscribe-borkena\/.*$/im',
        '/Join our Telegram Channel.*$/im',
        '/Like borkena on Facebook.*$/im',
        '/Add your business to Ethiopian Business Listing.*$/im',
        '/Join the conversation.*$/im',
        '/Subscribe to YouTube channel.*$/im',
        '/To share information or for submission.*$/im',
        '/Editor\'s Note.*$/im',
        '/borkena subscribe.*$/im',
        '/editors? note.*$/im',
        '/__\s*$/im',
        '/\n{3,}/', // Remove excessive newlines
    ];
    $content = preg_replace($patterns, '', $content);
    // Remove any paragraph containing those phrases
    $lines = preg_split('/\r?\n/', $content);
    $lines = array_filter($lines, function($line) {
        $l = strtolower($line);
        if (strpos($l, 'borkena subscribe') !== false) return false;
        if (strpos($l, "editor's note") !== false) return false;
        if (strpos($l, 'editors note') !== false) return false;
        return true;
    });
    return trim(implode("\n", $lines));
}

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Ensure slug is not too long
    if (strlen($slug) > 200) {
        $slug = substr($slug, 0, 200);
        $slug = rtrim($slug, '-');
    }
    
    return $slug;
}

function createUniqueSlug($db, $baseSlug) {
    $slug = $baseSlug;
    $counter = 1;
    
    while (true) {
        $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        
        if (!$stmt->fetch()) {
            return $slug;
        }
        
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

echo "Starting import of " . count($articles) . " articles...\n";
echo "========================================\n";

$imported = 0;
$skipped = 0;
$errors = 0;
$categoryStats = [];

foreach ($articles as $index => $article) {
    $title = trim($article['title']);
    $content = cleanContent($article['content']);
    $excerpt = trim($article['excerpt']);
    $image_url = trim($article['image_url']);
    $category = trim($article['category']);
    $url = trim($article['url']);
    
    // Skip if essential data is missing
    if (empty($title) || empty($content)) {
        echo "Skipping article $index: Missing title or content\n";
        $skipped++;
        continue;
    }
    
    // Generate slug
    $baseSlug = generateSlug($title);
    $slug = createUniqueSlug($db, $baseSlug);
    
    // Get category ID
    try {
        $category_id = getCategoryId($db, $category);
        
        // Track category statistics
        if (!isset($categoryStats[$category])) {
            $categoryStats[$category] = 0;
        }
        $categoryStats[$category]++;
        
    } catch (Exception $e) {
        echo "Error getting category ID for '$category': " . $e->getMessage() . "\n";
        $errors++;
        continue;
    }
    
    // Parse date
    $published_at = null;
    if (!empty($article['date_published'])) {
        $dateStr = $article['date_published'];
        // Try different date formats
        $dateFormats = [
            'F j, Y', // June 20, 2025
            'Y-m-d',  // 2025-06-20
            'd/m/Y',  // 20/06/2025
            'm/d/Y',  // 06/20/2025
            'Y-m-d H:i:s', // 2025-06-20 10:30:00
        ];
        
        foreach ($dateFormats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                $published_at = $date->format('Y-m-d H:i:s');
                break;
            }
        }
        
        // If no format worked, try strtotime
        if (!$published_at) {
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                $published_at = date('Y-m-d H:i:s', $timestamp);
            }
        }
    }
    
    // Check for duplicate by URL (more reliable than slug)
    $stmt = $db->prepare("SELECT id FROM articles WHERE url = ?");
    $stmt->execute([$url]);
    if ($stmt->fetch()) {
        echo "Skipping duplicate: $title (URL already exists)\n";
        $skipped++;
        continue;
    }
    
    // Insert article
    try {
        $stmt = $db->prepare("
            INSERT INTO articles (
                title, slug, content, excerpt, image_url, category_id, 
                published_at, status, url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?)
        ");
        
        $stmt->execute([
            $title, $slug, $content, $excerpt, $image_url, 
            $category_id, $published_at, $url
        ]);
        
        $imported++;
        echo "Imported: $title (Category: $category)\n";
        
    } catch (Exception $e) {
        echo "Error importing '$title': " . $e->getMessage() . "\n";
        $errors++;
    }
    
    // Progress indicator
    if (($index + 1) % 10 == 0) {
        echo "Progress: " . ($index + 1) . "/" . count($articles) . " articles processed\n";
    }
}

echo "\n========================================\n";
echo "IMPORT COMPLETE\n";
echo "========================================\n";
echo "Total articles processed: " . count($articles) . "\n";
echo "Successfully imported: $imported\n";
echo "Skipped (duplicates/missing data): $skipped\n";
echo "Errors: $errors\n";

if (!empty($categoryStats)) {
    echo "\nCategory breakdown:\n";
    foreach ($categoryStats as $category => $count) {
        echo "- $category: $count articles\n";
    }
}

echo "\nImport complete!\n";
?> 