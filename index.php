<?php
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

// Get featured and trending articles
$article = new Article($conn);
$featuredArticles = $article->getFeatured(3);
$trendingArticles = $article->getTrending(5);
$latestNews = $article->getByCategory('news', 6);
$latestPolitics = $article->getByCategory('politics', 3);
$latestBusiness = $article->getByCategory('business', 3);
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
                                <img src="<?php echo htmlspecialchars($fa['image_url']); ?>" alt="<?php echo htmlspecialchars($fa['title']); ?>" class="w-full h-96 object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 p-8 w-full">
                                    <span class="inline-block bg-red-700 text-white px-3 py-1 rounded-full text-xs mb-2">Top Story</span>
                                    <h2 class="text-3xl md:text-4xl font-bold mb-2 group-hover:text-red-400 transition-colors">
                                        <?php echo htmlspecialchars($fa['title']); ?>
                                    </h2>
                                    <p class="text-gray-200 text-lg max-w-2xl mb-4">
                                        <?php echo htmlspecialchars(substr($fa['content'], 0, 120)) . '...'; ?>
                                    </p>
                                    <a href="article.php?id=<?php echo $fa['id']; ?>" class="inline-block mt-2 px-6 py-2 bg-red-600 hover:bg-red-700 rounded-full font-semibold text-white transition">Read More</a>
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

<div class="container mx-auto px-4 mb-12 grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-12">
        <!-- Latest News -->
        <section>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Latest News</h2>
                <a href="news.php" class="text-red-700 hover:text-red-800 font-medium">View All</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($latestNews as $article) { ?>
                    <a href="article.php?id=<?php echo $article['id']; ?>" class="group">
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-shadow relative">
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-4 left-4 bg-red-600 text-white text-xs px-3 py-1 rounded-full shadow">News</div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-red-700 transition-colors">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    <?php echo htmlspecialchars(substr($article['content'], 0, 120)) . '...'; ?>
                                </p>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span><?php echo date('M d, Y', strtotime($article['created_at'])); ?></span>
                                    <span><?php echo $article['views']; ?> views</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </section>

        <!-- All Categories Section -->
        <section class="mt-16">
            <h2 class="text-3xl font-bold mb-8">Browse by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <a href="news.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-newspaper text-3xl text-red-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-red-700">News</div>
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
                <a href="video.php" class="block bg-white rounded-xl shadow hover:shadow-lg transition p-6 text-center group">
                    <i class="fa-solid fa-video text-3xl text-pink-600 mb-2"></i>
                    <div class="font-semibold text-lg text-gray-900 group-hover:text-pink-700">Video</div>
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
            </div>
        </section>

        <!-- Politics and Business Section -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Politics -->
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Politics</h2>
                    <a href="politics.php" class="text-red-700 hover:text-red-800 font-medium">View All</a>
                </div>
                <div class="space-y-6">
                    <?php foreach ($latestPolitics as $article) { ?>
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="group block">
                            <div class="flex gap-4 items-center bg-white rounded-lg shadow hover:shadow-xl transition p-4">
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-24 h-20 object-cover rounded-lg">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-red-700 mb-2">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <!-- Business -->
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Business</h2>
                    <a href="business.php" class="text-red-700 hover:text-red-800 font-medium">View All</a>
                </div>
                <div class="space-y-6">
                    <?php foreach ($latestBusiness as $article) { ?>
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="group block">
                            <div class="flex gap-4 items-center bg-white rounded-lg shadow hover:shadow-xl transition p-4">
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-24 h-20 object-cover rounded-lg">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-red-700 mb-2">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </section>
    </div>
    <!-- Sidebar -->
    <aside class="space-y-10 lg:sticky lg:top-24">
        <!-- Trending Now -->
        <section class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-red-700 flex items-center"><i class="fa-solid fa-fire mr-2"></i>Trending Now</h3>
            <ul class="space-y-4">
                <?php foreach ($trendingArticles as $ta) { ?>
                    <li>
                        <a href="article.php?id=<?php echo $ta['id']; ?>" class="flex items-center gap-3 group">
                            <img src="<?php echo htmlspecialchars($ta['image_url']); ?>" alt="<?php echo htmlspecialchars($ta['title']); ?>" class="w-14 h-14 object-cover rounded-lg">
                            <div>
                                <h4 class="text-base font-semibold text-gray-900 group-hover:text-red-700 transition-colors"><?php echo htmlspecialchars($ta['title']); ?></h4>
                                <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($ta['created_at'])); ?></span>
                            </div>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </section>
        <!-- Newsletter Signup -->
        <section class="bg-gradient-to-br from-red-600 to-red-400 rounded-xl shadow-lg p-6 text-white">
            <h3 class="text-xl font-bold mb-2"><i class="fa-solid fa-envelope mr-2"></i>Newsletter</h3>
            <p class="mb-4 text-sm">Get the latest news delivered to your inbox. Subscribe to our newsletter!</p>
            <form action="#" method="POST" class="space-y-3">
                <input type="email" name="email" placeholder="Your email address" class="w-full px-4 py-2 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-300">
                <button type="submit" class="w-full bg-white text-red-700 font-bold py-2 rounded-lg hover:bg-gray-100 transition">Subscribe</button>
            </form>
        </section>
    </aside>
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

<?php require_once 'includes/footer.php'; ?> 