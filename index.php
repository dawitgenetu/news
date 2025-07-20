<?php
if (!function_exists('refValues')) {
    function refValues($arr) {
        $refs = array();
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
}
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

// Get featured and trending articles
$article = new Article($conn);
$featuredArticles = $article->getFeatured(6); // Show more featured
$trendingArticles = $article->getTrending(5);
// Get the category_id for Travel
$travelCategoryId = null;
$catResult = $conn->query("SELECT id FROM categories WHERE name = 'Travel' LIMIT 1");
if ($catRow = $catResult->fetch_assoc()) {
    $travelCategoryId = $catRow['id'];
}

// Get the category_ids for Travel and Entertainment
$excludeCategoryIds = [];
$catResult = $conn->query("SELECT id FROM categories WHERE name IN ('Travel', 'Entertainment')");
while ($catRow = $catResult->fetch_assoc()) {
    $excludeCategoryIds[] = $catRow['id'];
}

// Fetch latest articles excluding Travel and Entertainment
if (count($excludeCategoryIds) === 2) {
    $placeholders = implode(',', array_fill(0, count($excludeCategoryIds), '?'));
    $types = str_repeat('i', count($excludeCategoryIds));
    $query = "SELECT * FROM articles WHERE category_id NOT IN ($placeholders) ORDER BY published_at DESC LIMIT 6";
    $stmt = $conn->prepare($query);
    $params = array_merge([$types], $excludeCategoryIds);
    call_user_func_array([$stmt, 'bind_param'], refValues($params));
    $stmt->execute();
    $latestArticles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM articles ORDER BY published_at DESC LIMIT 6");
    $stmt->execute();
    $latestArticles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Helper for call_user_func_array with references

$popularArticles = $article->getPopular(6); // New method for popular
$latestNews = $article->getByCategory('news', 6);
$latestPolitics = $article->getByCategory('politics', 3);
$latestBusiness = $article->getByCategory('business', 3);

require_once 'includes/article_functions.php';
$categories = [
    'news' => 'News',
    'business' => 'Business',
    'sport' => 'Sport',
    'entertainment' => 'Entertainment',
    'opinion' => 'Opinion'
];
$categoryArticles = [];

foreach ($categories as $slug => $name) {
    if ($slug === 'news' && $travelCategoryId) {
        // Exclude travel articles from news
        $stmt = $conn->prepare("SELECT * FROM articles WHERE category_id != ? AND category_id = (SELECT id FROM categories WHERE name = 'News' LIMIT 1) ORDER BY published_at DESC");
        $stmt->bind_param("i", $travelCategoryId);
        $stmt->execute();
        $categoryArticles[$slug] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $categoryArticles[$slug] = $article->getByCategory($name);
    }
}
?>

<!-- Hero Carousel: Top Stories -->
<div class="w-full bg-gray-900 text-white mb-12">
    <div class="container mx-auto px-4 py-12">
        <div class="relative">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ($featuredArticles as $fa) { ?>
                        <div class="swiper-slide">
                            <div class="relative rounded-xl overflow-hidden shadow-2xl group">
                                <img src="<?php echo htmlspecialchars(!empty($fa['local_image_path']) ? $fa['local_image_path'] : $fa['image_url']); ?>" alt="<?php echo htmlspecialchars($fa['title']); ?>" class="w-full h-96 object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-8 w-full">
                                    <span class="inline-block bg-blue-700 text-white px-3 py-1 rounded-full text-xs mb-2">Top Story</span>
                                    <h2 class="text-3xl md:text-4xl font-bold mb-2 group-hover:text-blue-400 transition-colors">
                                        <?php echo htmlspecialchars($fa['title']); ?>
                                    </h2>
                                    <p class="text-gray-200 text-lg max-w-2xl mb-4">
                                        <?php echo htmlspecialchars(substr($fa['content'], 0, 120)) . '...'; ?>
                                    </p>
                                    <a href="article.php?id=<?php echo $fa['id']; ?>" class="inline-block mt-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-full font-semibold text-white transition">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
                <!-- Add Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
</div>

<!-- Latest Articles Section -->
<section class="container mx-auto px-4 mb-12">
    <h2 class="text-3xl font-bold mb-6">Latest Articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($latestArticles as $article) { ?>
            <a href="article.php?id=<?php echo $article['id']; ?>" class="group">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow relative">
                    <img src="<?php echo htmlspecialchars(!empty($article['local_image_path']) ? $article['local_image_path'] : $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-blue-700 transition-colors">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars(substr($article['content'], 0, 120)) . '...'; ?>
                        </p>
                        <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($article['published_at'])); ?></span>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>
</section>

<!-- Featured Articles Section -->
<section class="container mx-auto px-4 mb-12">
    <h2 class="text-3xl font-bold mb-6">Featured Articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($featuredArticles as $article) { ?>
            <a href="article.php?id=<?php echo $article['id']; ?>" class="group">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow relative">
                    <img src="<?php echo htmlspecialchars(!empty($article['local_image_path']) ? $article['local_image_path'] : $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-blue-700 transition-colors">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars(substr($article['content'], 0, 120)) . '...'; ?>
                        </p>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>
</section>

<!-- Popular Articles Section -->
<section class="container mx-auto px-4 mb-12">
    <h2 class="text-3xl font-bold mb-6">Popular Articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($popularArticles as $article) { ?>
            <a href="article.php?id=<?php echo $article['id']; ?>" class="group">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow relative">
                    <img src="<?php echo htmlspecialchars(!empty($article['local_image_path']) ? $article['local_image_path'] : $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-blue-700 transition-colors">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars(substr($article['content'], 0, 120)) . '...'; ?>
                        </p>
                        <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($article['published_at'])); ?></span>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>
</section>

<?php foreach ($categories as $slug => $name): ?>
<section class="container mx-auto px-4 mb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold"><?php echo $name; ?></h2>
        <a href="<?php echo $slug; ?>.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full font-semibold transition">View All</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach (array_slice($categoryArticles[$slug], 0, 6) as $article): ?>
            <a href="article.php?id=<?php echo $article['id']; ?>" class="group">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow relative">
                    <img src="<?php echo htmlspecialchars(!empty($article['local_image_path']) ? $article['local_image_path'] : $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-blue-700 transition-colors">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars(substr($article['content'], 0, 120)) . '...'; ?>
                        </p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><?php echo date('M d, Y', strtotime($article['published_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<div class="container mx-auto px-4 mb-12 grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-12">
        <!-- All Categories Section -->
        <section class="mt-16">
            <h2 class="text-3xl font-bold mb-8">Browse by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <a href="news.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-newspaper text-3xl text-blue-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-blue-700">News</div>
                </a>
                <a href="politics.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-landmark text-3xl text-blue-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-blue-700">Politics</div>
                </a>
                <a href="business.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-briefcase text-3xl text-green-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-green-700">Business</div>
                </a>
                <a href="sport.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-futbol text-3xl text-yellow-500 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-yellow-700">Sports</div>
                </a>
                <a href="opinion.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-comments text-3xl text-purple-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-purple-700">Opinion</div>
                </a>
                <a href="entertainment.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-film text-3xl text-indigo-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-indigo-700">Entertainment</div>
                </a>
                <a href="society.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-users text-3xl text-teal-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-teal-700">Society</div>
                </a>
                <a href="technology.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-microchip text-3xl text-orange-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-orange-700">Technology</div>
                </a>
                <a href="business-listings.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-building text-3xl text-gray-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-gray-700">Business Listings</div>
                </a>
                <a href="travel.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-plane text-3xl text-blue-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-blue-700">Travel</div>
                </a>
                <a href="restaurant.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-utensils text-3xl text-red-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-red-700">Restaurant</div>
                </a>
            </div>
        </section>

        <!-- Politics and Business Section -->
        <!-- Removed as per user request -->

        <!-- Sidebar -->
        <!-- Removed Trending Now and Newsletter sections as per user request -->
    </div>
</div>

<!-- SwiperJS for Hero Carousel -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.swiper-container', {
            loop: true,
            autoplay: { delay: 6000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            effect: 'fade',
        });
    });
</script>

<div class="container mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold mb-6">Latest News</h2>
    <div id="latest-news-marquee" class="flex space-x-6 overflow-x-auto whitespace-nowrap scroll-smooth">
        <?php foreach ($latestArticles as $article): ?>
            <a href="article.php?id=<?php echo $article['id']; ?>" class="inline-block min-w-[320px] max-w-xs bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow">
                <img src="<?php echo htmlspecialchars(!empty($article['local_image_path']) ? $article['local_image_path'] : $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2 text-gray-900"><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p class="text-gray-600 mb-2"><?php echo htmlspecialchars(substr($article['content'], 0, 80)) . '...'; ?></p>
                    <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($article['published_at'])); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marquee = document.getElementById('latest-news-marquee');
    let scrollStep = 1; // pixels per frame
    let maxScroll = marquee.scrollWidth - marquee.clientWidth;
    function autoScroll() {
        if (marquee.scrollLeft >= maxScroll) {
            marquee.scrollLeft = 0;
        } else {
            marquee.scrollLeft += scrollStep;
        }
        requestAnimationFrame(autoScroll);
    }
    if (marquee && marquee.scrollWidth > marquee.clientWidth) {
        autoScroll();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 