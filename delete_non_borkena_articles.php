<?php
require_once 'includes/config.php';

try {
    // Delete articles where url does not contain 'borkena.com' (non-scraped news)
    $stmt = $db->prepare("DELETE FROM articles WHERE url IS NULL OR url NOT LIKE ?");
    $like = '%borkena.com%';
    $stmt->execute([$like]);
    $deleted = $stmt->rowCount();
    echo "Deleted $deleted non-scraped news articles (not from borkena.com).\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 