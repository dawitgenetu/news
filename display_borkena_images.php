<?php
require_once __DIR__ . '/includes/db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borkena Scraped Images</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-center text-red-700">Borkena Scraped Images</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
<?php
$result = $conn->query("SELECT image_url, article_url, scraped_at FROM borkena_images ORDER BY scraped_at DESC LIMIT 50");
while ($row = $result->fetch_assoc()) {
    $img = htmlspecialchars($row['image_url']);
    $article = htmlspecialchars($row['article_url']);
    $scraped = htmlspecialchars($row['scraped_at']);
    echo "<div class='bg-white rounded-lg shadow-md p-4 flex flex-col items-center'>";
    echo "<a href='$article' target='_blank'><img src='$img' alt='Borkena Image' class='max-w-full max-h-48 rounded mb-2 border border-gray-200 shadow-sm hover:shadow-lg transition'></a>";
    echo "<span class='text-xs text-gray-500'>Scraped: $scraped</span>";
    echo "</div>";
}
?>
        </div>
    </div>
</body>
</html> 