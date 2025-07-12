<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Fetch entertainment articles
$stmt = $db->prepare("
    SELECT a.*, c.name as category_name, u.name as author_name 
    FROM articles a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.author_id = u.id 
    WHERE c.name = 'Entertainment' 
    ORDER BY a.published_at DESC
");
$stmt->execute();
$entertainment_articles = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Entertainment</h1>
        <p class="text-lg text-gray-600">Latest news from the world of entertainment, music, movies, and culture</p>
    </div>

    <!-- Featured Entertainment -->
    <?php if (!empty($entertainment_articles)): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Main Article -->
        <div class="lg:col-span-2">
            <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="<?php echo htmlspecialchars($entertainment_articles[0]['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($entertainment_articles[0]['title']); ?>"
                     class="w-full h-96 object-cover">
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            Entertainment
                        </span>
                        <span class="text-gray-500 text-sm">
                            <?php echo date('F j, Y', strtotime($entertainment_articles[0]['published_at'])); ?>
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        <a href="article.php?id=<?php echo $entertainment_articles[0]['id']; ?>" 
                           class="hover:text-red-700 transition-colors">
                            <?php echo htmlspecialchars($entertainment_articles[0]['title']); ?>
                        </a>
                    </h2>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars(substr($entertainment_articles[0]['content'], 0, 200)) . '...'; ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($entertainment_articles[0]['author_name']); ?>" 
                                 alt="<?php echo htmlspecialchars($entertainment_articles[0]['author_name']); ?>"
                                 class="w-10 h-10 rounded-full">
                            <span class="text-gray-700"><?php echo htmlspecialchars($entertainment_articles[0]['author_name']); ?></span>
                        </div>
                        <a href="article.php?id=<?php echo $entertainment_articles[0]['id']; ?>" 
                           class="text-red-700 hover:text-red-800 font-medium">
                            Read More →
                        </a>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Entertainment Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-red-700 transition-colors">
                            <span>Movies</span>
                            <span class="text-gray-500">(15)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-red-700 transition-colors">
                            <span>Music</span>
                            <span class="text-gray-500">(12)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-red-700 transition-colors">
                            <span>TV Shows</span>
                            <span class="text-gray-500">(8)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-red-700 transition-colors">
                            <span>Celebrity News</span>
                            <span class="text-gray-500">(20)</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Trending Entertainment -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Trending Now</h3>
                <div class="space-y-4">
                    <?php for ($i = 1; $i < min(4, count($entertainment_articles)); $i++): ?>
                    <article class="flex space-x-4">
                        <img src="<?php echo htmlspecialchars($entertainment_articles[$i]['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($entertainment_articles[$i]['title']); ?>"
                             class="w-24 h-24 object-cover rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-1">
                                <a href="article.php?id=<?php echo $entertainment_articles[$i]['id']; ?>" 
                                   class="hover:text-red-700 transition-colors">
                                    <?php echo htmlspecialchars($entertainment_articles[$i]['title']); ?>
                                </a>
                            </h4>
                            <p class="text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($entertainment_articles[$i]['published_at'])); ?>
                            </p>
                        </div>
                    </article>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Entertainment Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php for ($i = 4; $i < count($entertainment_articles); $i++): ?>
        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
            <img src="<?php echo htmlspecialchars($entertainment_articles[$i]['image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($entertainment_articles[$i]['title']); ?>"
                 class="w-full h-48 object-cover">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                        Entertainment
                    </span>
                    <span class="text-gray-500 text-sm">
                        <?php echo date('F j, Y', strtotime($entertainment_articles[$i]['published_at'])); ?>
                    </span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <a href="article.php?id=<?php echo $entertainment_articles[$i]['id']; ?>" 
                       class="hover:text-red-700 transition-colors">
                        <?php echo htmlspecialchars($entertainment_articles[$i]['title']); ?>
                    </a>
                </h2>
                <p class="text-gray-600 mb-4">
                    <?php echo htmlspecialchars(substr($entertainment_articles[$i]['content'], 0, 150)) . '...'; ?>
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($entertainment_articles[$i]['author_name']); ?>" 
                             alt="<?php echo htmlspecialchars($entertainment_articles[$i]['author_name']); ?>"
                             class="w-8 h-8 rounded-full">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($entertainment_articles[$i]['author_name']); ?></span>
                    </div>
                    <a href="article.php?id=<?php echo $entertainment_articles[$i]['id']; ?>" 
                       class="text-red-700 hover:text-red-800 text-sm font-medium">
                        Read More →
                    </a>
                </div>
            </div>
        </article>
        <?php endfor; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">No Entertainment Articles Found</h2>
        <p class="text-gray-600">Check back later for new entertainment content.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 