<?php
require_once __DIR__ . '/../includes/db_config.php';

// Connect to the database using mysqli
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add is_featured column to articles table if it doesn't exist
$sql = "ALTER TABLE articles ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER category_id";
if ($conn->query($sql) === TRUE) {
    echo "Successfully added is_featured column to articles table.\n";
    // Optionally, set some articles as featured
    $updateSql = "UPDATE articles SET is_featured = 1 ORDER BY created_at DESC LIMIT 10";
    $conn->query($updateSql);
    echo "Set the 10 most recent articles as featured.\n";
} else {
    if (strpos($conn->error, 'Duplicate column name') !== false) {
        echo "Column is_featured already exists.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
$conn->close();
?> 