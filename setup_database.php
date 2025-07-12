<?php
// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'reporter'
];

// Create connection
$conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS {$dbConfig['name']}";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists\n";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbConfig['name']);

// Create articles table
$sql = "CREATE TABLE IF NOT EXISTS articles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    local_image_path VARCHAR(255),
    local_content_path VARCHAR(255),
    category_id INT(11) NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'published',
    published_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug (slug)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully or already exists\n";
} else {
    die("Error creating table: " . $conn->error);
}

$conn->close();
echo "Database setup completed successfully\n"; 