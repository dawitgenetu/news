<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Fetch video articles
$stmt = $db->prepare("
    SELECT a.*, c.name as category_name, u.name as author_name 
    FROM articles a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.author_id = u.id 
    WHERE c.name = 'Video' 
    ORDER BY a.published_at DESC
");
$stmt->execute();
$video_articles = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Video</h1>
        <p class="text-lg text-gray-600">Watch the latest news videos and exclusive content</p>
    </div>

    <!-- Featured Video -->
    <?php if (!empty($video_articles)): ?>
    <div class="mb-12">
        <div class="relative aspect-video rounded-lg overflow-hidden shadow-lg">
            <iframe src="<?php echo htmlspecialchars($video_articles[0]['video_url']); ?>" 
                    class="absolute top-0 left-0 w-full h-full"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        </div>
        <div class="mt-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo htmlspecialchars($video_articles[0]['title']); ?>
            </h2>
            <p class="text-gray-600">
                <?php echo htmlspecialchars($video_articles[0]['content']); ?>
            </p>
        </div>
    </div>

    <!-- Video Categories -->
    <div class="flex space-x-4 mb-8 overflow-x-auto pb-4">
        <button class="px-4 py-2 bg-red-700 text-white rounded-full font-medium">All Videos</button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium hover:bg-gray-200">News</button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium hover:bg-gray-200">Interviews</button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium hover:bg-gray-200">Documentaries</button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium hover:bg-gray-200">Special Reports</button>
    </div>

    <!-- Video Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php for ($i = 1; $i < count($video_articles); $i++): ?>
        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="relative aspect-video">
                <iframe src="<?php echo htmlspecialchars($video_articles[$i]['video_url']); ?>" 
                        class="absolute top-0 left-0 w-full h-full"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                        Video
                    </span>
                    <span class="text-gray-500 text-sm">
                        <?php echo date('F j, Y', strtotime($video_articles[$i]['published_at'])); ?>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($video_articles[$i]['title']); ?>
                </h3>
                <p class="text-gray-600 mb-4">
                    <?php echo htmlspecialchars(substr($video_articles[$i]['content'], 0, 150)) . '...'; ?>
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video_articles[$i]['author_name']); ?>" 
                             alt="<?php echo htmlspecialchars($video_articles[$i]['author_name']); ?>"
                             class="w-8 h-8 rounded-full">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($video_articles[$i]['author_name']); ?></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-eye mr-1"></i>
                            <?php echo number_format($video_articles[$i]['views']); ?> views
                        </span>
                    </div>
                </div>
            </div>
        </article>
        <?php endfor; ?>
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-12">
        <button class="px-8 py-3 bg-red-700 text-white rounded-full font-medium hover:bg-red-800 transition-colors">
            Load More Videos
        </button>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">No Videos Available</h2>
        <p class="text-gray-600">Check back later for new video content.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 