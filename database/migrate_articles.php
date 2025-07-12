<?php
require_once __DIR__ . '/../includes/db_config.php';

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'reporter'
];

// Connect to database
$db = new mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['pass'],
    $dbConfig['name']
);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Create upload directories if they don't exist
$directories = [
    __DIR__ . '/../uploads/images',
    __DIR__ . '/../uploads/articles'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Function to download and save image
function downloadImage($url, $articleId) {
    if (empty($url)) return null;
    
    $uploadsDir = __DIR__ . '/../uploads/images/';
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!$ext) $ext = 'jpg';
    $filename = 'article_' . $articleId . '_' . uniqid() . '.' . $ext;
    $filePath = $uploadsDir . $filename;
    
    try {
        $imgData = file_get_contents($url);
        if ($imgData !== false) {
            file_put_contents($filePath, $imgData);
            return 'uploads/images/' . $filename;
        }
    } catch (Exception $e) {
        echo "Error downloading image for article $articleId: " . $e->getMessage() . "\n";
    }
    return null;
}

// Function to save content to file
function saveContentToFile($content, $articleId) {
    if (empty($content)) return null;
    
    $uploadsDir = __DIR__ . '/../uploads/articles/';
    $filename = 'article_' . $articleId . '_' . uniqid() . '.html';
    $filePath = $uploadsDir . $filename;
    
    try {
        file_put_contents($filePath, $content);
        return 'uploads/articles/' . $filename;
    } catch (Exception $e) {
        echo "Error saving content for article $articleId: " . $e->getMessage() . "\n";
    }
    return null;
}

// Get all articles
$result = $db->query("SELECT id, title, content, image_url FROM articles");
if (!$result) {
    die("Error fetching articles: " . $db->error);
}

// Process each article
while ($article = $result->fetch_assoc()) {
    echo "Processing article ID: " . $article['id'] . "\n";
    
    // Download and save image
    $localImagePath = downloadImage($article['image_url'], $article['id']);
    
    // Save content to file
    $localContentPath = saveContentToFile($article['content'], $article['id']);
    
    // Update database with local paths
    $stmt = $db->prepare("
        UPDATE articles 
        SET local_image_path = ?, 
            local_content_path = ? 
        WHERE id = ?
    ");
    
    $stmt->bind_param("ssi", $localImagePath, $localContentPath, $article['id']);
    
    if (!$stmt->execute()) {
        echo "Error updating article " . $article['id'] . ": " . $stmt->error . "\n";
    } else {
        echo "Successfully migrated article " . $article['id'] . "\n";
    }
}

echo "Migration completed!\n";
$db->close(); 