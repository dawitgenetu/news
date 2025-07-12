<?php
// --- Article Data Preparation ---
$page_title = 'Article';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

// Get article ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get article details
$article = new Article($conn);
$articleData = $article->getById($id);

// If article not found, show error page
if (!$articleData) {
    http_response_code(404);
    echo '<main class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-700">';
    echo '<h1 class="text-4xl font-bold mb-4">Article Not Found</h1>';
    echo '<p class="mb-8">Sorry, the article you are looking for does not exist.</p>';
    echo '<a href="index.php" class="text-blue-600 hover:underline">Go back to homepage</a>';
    echo '</main>';
    require_once 'includes/footer.php';
    exit;
}

// Increment view count
$article->incrementViews($id);

// Get related articles
$relatedArticles = $article->getByCategory(isset($articleData['category']) && $articleData['category'] ? $articleData['category'] : 'news');
$relatedArticles = array_filter($relatedArticles, function($item) use ($id) {
    return $item['id'] != $id;
});
$relatedArticles = array_slice($relatedArticles, 0, 3);

// SEO & Social Meta
$metaTitle = htmlspecialchars($articleData['title']);
$metaDescription = htmlspecialchars(mb_substr(strip_tags($articleData['content']), 0, 160));
$metaImage = htmlspecialchars($articleData['image_url']);
$metaUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!-- SEO & Social Meta Tags -->
<head>
    <title><?php echo $metaTitle; ?> | Borkena News</title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta property="og:title" content="<?php echo $metaTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <meta property="og:image" content="<?php echo $metaImage; ?>">
    <meta property="og:url" content="<?php echo $metaUrl; ?>">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $metaTitle; ?>">
    <meta name="twitter:description" content="<?php echo $metaDescription; ?>">
    <meta name="twitter:image" content="<?php echo $metaImage; ?>">
</head>

<main>
    <!-- Article Header -->
    <header class="bg-gray-900 text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-4xl mx-auto">
                <nav aria-label="Breadcrumb" class="mb-4">
                    <ol class="flex items-center space-x-2 text-sm text-gray-300">
                        <li><a href="index.php" class="hover:text-white">Home</a></li>
                        <li><span aria-hidden="true">/</span></li>
                        <li><a href="<?php echo isset($articleData['category']) && $articleData['category'] ? strtolower($articleData['category']) : 'news'; ?>.php" class="hover:text-white"><?php echo isset($articleData['category']) && $articleData['category'] ? ucfirst($articleData['category']) : 'News'; ?></a></li>
                    </ol>
                </nav>
                <div class="flex items-center space-x-4 text-sm text-gray-300 mb-4">
                    <span><?php echo date('F j, Y', strtotime($articleData['created_at'])); ?></span>
                    <span>•</span>
                    <span><?php echo $articleData['views']; ?> views</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-6"><?php echo $metaTitle; ?></h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center" aria-hidden="true">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium"><?php echo isset($articleData['author']) && $articleData['author'] ? htmlspecialchars($articleData['author']) : 'Unknown Author'; ?></p>
                            <p class="text-sm text-gray-300">Author</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Article Content -->
    <article class="container mx-auto px-4 py-12" itemscope itemtype="https://schema.org/Article">
        <div class="max-w-4xl mx-auto">
            <!-- Featured Image -->
            <figure class="mb-8">
                <img src="<?php echo $metaImage; ?>" alt="<?php echo $metaTitle; ?>" class="w-full h-[500px] object-cover rounded-lg shadow-lg" loading="lazy"/>
            </figure>

            <!-- Article Body -->
            <section class="prose prose-lg max-w-none" itemprop="articleBody">
                <?php echo nl2br(htmlspecialchars($articleData['content'])); ?>
            </section>

            <!-- Social Share -->
            <section class="mt-12 pt-8 border-t border-gray-200" aria-label="Share this article">
                <h3 class="text-xl font-bold mb-4">Share this article</h3>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($metaUrl); ?>" target="_blank" rel="noopener" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" aria-label="Share on Facebook">
                        <i class="fab fa-facebook-f mr-2"></i> Share
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($metaUrl); ?>&text=<?php echo urlencode($metaTitle); ?>" target="_blank" rel="noopener" class="bg-blue-400 text-white px-4 py-2 rounded-lg hover:bg-blue-500 transition-colors" aria-label="Share on Twitter">
                        <i class="fab fa-twitter mr-2"></i> Tweet
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($metaUrl); ?>&title=<?php echo urlencode($metaTitle); ?>" target="_blank" rel="noopener" class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors" aria-label="Share on LinkedIn">
                        <i class="fab fa-linkedin-in mr-2"></i> Share
                    </a>
                </div>
            </section>

            <!-- Author Bio -->
            <aside class="mt-12 p-6 bg-gray-50 rounded-lg" aria-label="Author bio">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center" aria-hidden="true">
                        <i class="fas fa-user text-2xl text-white"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold"><?php echo isset($articleData['author']) && $articleData['author'] ? htmlspecialchars($articleData['author']) : 'Unknown Author'; ?></h3>
                        <p class="text-gray-600">Staff Writer at Borkena News</p>
                    </div>
                </div>
            </aside>

            <!-- Related Articles -->
            <section class="mt-16" aria-label="Related articles">
                <h2 class="text-3xl font-bold mb-8">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($relatedArticles as $related) { ?>
                        <a href="article.php?id=<?php echo $related['id']; ?>" class="group" aria-label="Read article: <?php echo htmlspecialchars($related['title']); ?>">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-48 object-cover" loading="lazy"/>
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2 text-gray-900 group-hover:text-red-700">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </h3>
                                    <p class="text-gray-600 mb-4">
                                        <?php echo htmlspecialchars(mb_substr($related['content'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div class="flex justify-between items-center text-sm text-gray-500">
                                        <span><?php echo date('M d, Y', strtotime($related['created_at'])); ?></span>
                                        <span><?php echo $related['views']; ?> views</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </section>
        </div>
    </article>
</main>

<?php require_once 'includes/footer.php'; ?> 