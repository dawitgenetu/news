<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Pagination logic
$newsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $newsPerPage;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE status = 'published'");
    $stmt->execute();
    $totalNews = $stmt->fetchColumn();
    $totalPages = max(1, ceil($totalNews / $newsPerPage));

    $stmt = $db->prepare("
        SELECT * FROM articles 
        WHERE status = 'published'
        ORDER BY published_at DESC, created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $newsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching articles: " . $e->getMessage());
    $articles = [];
    $totalPages = 1;
    $page = 1;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1">
            <h1 class="text-4xl font-bold mb-8 text-gray-900">Latest News</h1>
            <?php if (empty($articles)): ?>
                <div class="bg-blue-100 text-blue-800 px-6 py-4 rounded-lg mb-8 text-center">
                    No articles available at the moment.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($articles as $article): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden flex flex-col">
                            <?php if (!empty($article['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="w-full h-56 object-cover">
                            <?php endif; ?>
                        <div class="p-6 flex flex-col flex-1">
                            <h2 class="text-2xl font-semibold mb-2 text-gray-900">
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="hover:text-red-700 transition-colors">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </h2>
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                <?php 
                                $date = $article['published_at'] ?? $article['published_date'] ?? $article['date_published'] ?? $article['created_at'] ?? null;
                                echo $date ? date('F j, Y', strtotime($date)) : 'Unknown date';
                                ?>
                            </div>
                            <p class="text-gray-700 mb-4 flex-1">
                                        <?php 
                                $excerpt = $article['excerpt'] ?? '';
                                $content = $article['content'] ?? '';
                                if ($excerpt) {
                                    echo htmlspecialchars($excerpt);
                                } else {
                                        echo htmlspecialchars(
                                        strlen($content) > 200 
                                        ? substr($content, 0, 200) . '...' 
                                        : $content
                                        ); 
                                }
                                        ?>
                                    </p>
                            <div class="mt-auto flex justify-between items-center">
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="inline-block bg-red-700 text-white px-4 py-2 rounded-full font-medium hover:bg-red-800 transition-colors">
                                        Read More
                                    </a>
                                <?php if (!empty($article['category'])): ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold ml-2">
                                        <?php echo htmlspecialchars($article['category']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-8">
                <nav class="inline-flex rounded-md shadow-sm" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-red-50">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border-t border-b border-gray-300 <?php echo $i === $page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-red-50'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-red-50">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900">About Borkena News</h3>
                <p class="text-gray-700">
                        Stay updated with the latest news from Ethiopia and around the world. 
                        Our news aggregator brings you the most recent and relevant stories 
                        from Borkena.com.
                    </p>
                </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-900">News Categories</h3>
                <ul class="space-y-2">
                    <li><a href="news.php" class="flex items-center text-gray-700 hover:text-red-700 transition-colors"><i class="fas fa-newspaper mr-2"></i> Latest News</a></li>
                    <li><a href="business.php" class="flex items-center text-gray-700 hover:text-red-700 transition-colors"><i class="fas fa-chart-line mr-2"></i> Business</a></li>
                    <li><a href="entertainment.php" class="flex items-center text-gray-700 hover:text-red-700 transition-colors"><i class="fas fa-film mr-2"></i> Entertainment</a></li>
                    <li><a href="video.php" class="flex items-center text-gray-700 hover:text-red-700 transition-colors"><i class="fas fa-video mr-2"></i> Video</a></li>
                    </ul>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 