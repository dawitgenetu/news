<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

$article = new Article($conn);

// Pagination logic
$articlesPerPage = 12; // 3 columns x 4 rows
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $articlesPerPage;

// Get the category_ids for Travel and Entertainment
$excludeCategoryIds = [];
$catResult = $conn->query("SELECT id FROM categories WHERE name IN ('Travel', 'Entertainment')");
while ($catRow = $catResult->fetch_assoc()) {
    $excludeCategoryIds[] = $catRow['id'];
}

if (count($excludeCategoryIds) === 2) {
    // Exclude both Travel and Entertainment
    $placeholders = implode(',', array_fill(0, count($excludeCategoryIds), '?'));
    $types = str_repeat('i', count($excludeCategoryIds));
    // Count
    $query = "SELECT COUNT(*) as count FROM articles WHERE category_id NOT IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $params = array_merge([$types], $excludeCategoryIds);
    call_user_func_array([$stmt, 'bind_param'], refValues($params));
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalArticles = $row['count'];
    $stmt->close();
    $totalPages = max(1, ceil($totalArticles / $articlesPerPage));
    // Fetch articles
    $query = "SELECT * FROM articles WHERE category_id NOT IN ($placeholders) ORDER BY published_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $params = array_merge([$types . 'ii'], $excludeCategoryIds, [$articlesPerPage, $offset]);
    call_user_func_array([$stmt, 'bind_param'], refValues($params));
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Fallback: show all articles
    $result = $conn->query("SELECT COUNT(*) as count FROM articles");
    $row = $result->fetch_assoc();
    $totalArticles = $row['count'];
    $totalPages = max(1, ceil($totalArticles / $articlesPerPage));
    $stmt = $conn->prepare("SELECT * FROM articles ORDER BY published_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $articlesPerPage, $offset);
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Helper for call_user_func_array with references
function refValues($arr) {
    $refs = array();
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
// $trendingArticles = $article->getTrending(2); // No longer needed
?>

<!-- Category Navigation -->
<div class="mb-8">
    <h2 class="text-2xl font-bold mb-4">Browse by Category</h2>
    <div class="flex flex-wrap gap-4">
        <a href="news.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-blue-600 hover:text-white">News</a>
        <a href="business.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-blue-600 hover:text-white">Business</a>
        <a href="sport.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-blue-600 hover:text-white">Sport</a>
        <a href="entertainment.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-blue-600 hover:text-white">Entertainment</a>
        <!-- Add more categories as needed -->
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8 fade-in">
    <h1 class="text-4xl font-bold mb-8">News</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 mb-12">
        <?php echo displayArticles($articles); ?>
    </div>
    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-8">
        <nav class="inline-flex rounded-md shadow-sm" aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-blue-50">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border-t border-b border-gray-300 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-blue-50'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-blue-50">Next</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 