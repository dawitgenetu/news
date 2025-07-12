<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'reporter';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($database);
    
    // Create articles table if not exists
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
        // Check if table is empty and insert sample data
        $result = $conn->query("SELECT COUNT(*) as count FROM articles");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $sample_data = [
                ['Breaking News: Major Development', 'breaking-news-major-development', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'Lorem ipsum dolor sit amet...', 'https://picsum.photos/800/400', 1, '2024-03-20 10:00:00'],
                ['Business Update: Market Trends', 'business-update-market-trends', 'Ut enim ad minim veniam, quis nostrud exercitation.', 'Ut enim ad minim veniam...', 'https://picsum.photos/800/401', 2, '2024-03-20 11:00:00'],
                ['Political Analysis: Current Affairs', 'political-analysis-current-affairs', 'Duis aute irure dolor in reprehenderit.', 'Duis aute irure dolor...', 'https://picsum.photos/800/402', 3, '2024-03-20 12:00:00']
            ];
            
            $stmt = $conn->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, category_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($sample_data as $data) {
                $stmt->bind_param("sssssis", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
                $stmt->execute();
            }
        }
    } else {
        die("Error creating table: " . $conn->error);
    }
} else {
    die("Error creating database: " . $conn->error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?> 