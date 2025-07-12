<?php
require_once 'includes/config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($q !== '') {
    // Search for articles where the title or the category name matches the query
    $stmt = $db->prepare('
        SELECT a.*, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.title LIKE ? OR c.name LIKE ?
        ORDER BY a.created_at DESC
    ');
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results for <?= htmlspecialchars($q) ?> - Borkena News</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Search Results for "<?= htmlspecialchars($q) ?>"</h1>
        <?php if ($q === ''): ?>
            <p class="text-gray-600">Please enter a search term.</p>
        <?php elseif (empty($results)): ?>
            <p class="text-gray-600">No results found.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($results as $row): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="mb-2">
                            <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
                                <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        </div>
                        <h2 class="text-lg font-semibold mb-2">
                            <a href="article.php?id=<?= $row['id'] ?>" class="hover:text-red-700 transition">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>
                        </h2>
                        <p class="text-gray-600 mb-2">
                            <?= htmlspecialchars(mb_substr(strip_tags($row['content']), 0, 120)) ?>...
                        </p>
                        <a href="article.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline">Read More</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 