<?php
require_once 'includes/config.php';

$stmt = $db->query("SELECT id, title, image_url FROM articles ORDER BY id DESC LIMIT 10");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Latest 10 Articles and Their Image URLs</h2>";
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Title</th><th>Image URL</th><th>Image Preview</th></tr>";
foreach ($articles as $article) {
    echo "<tr>";
    echo "<td>{$article['id']}</td>";
    echo "<td>" . htmlspecialchars($article['title']) . "</td>";
    echo "<td>" . htmlspecialchars($article['image_url']) . "</td>";
    echo "<td>";
    if ($article['image_url']) {
        echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='' width='120'>";
    } else {
        echo "No image";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
?> 