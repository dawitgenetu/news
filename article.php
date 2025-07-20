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
$metaImage = htmlspecialchars(!empty($articleData['local_image_path']) ? $articleData['local_image_path'] : $articleData['image_url']);
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

<main class="bg-white">
    <div class="container mx-auto px-4 py-10">
        <article class="max-w-3xl mx-auto">
            <!-- Title & Info -->
            <h1 class="text-4xl font-bold text-blue-700 leading-tight mb-4"><?php echo $metaTitle; ?></h1>
            <div class="flex items-center text-sm text-gray-600 mb-6">
                <span class="mr-4"><?php echo date('F j, Y', strtotime($articleData['published_at'])); ?></span>
                <?php if (isset($articleData['author']) && $articleData['author']) { ?>
                    <span class="italic">By <?php echo htmlspecialchars($articleData['author']); ?></span>
                <?php } ?>
            </div>

            <!-- Featured Image -->
            <?php if (!empty($metaImage)) { ?>
                <img src="<?php echo $metaImage; ?>" alt="<?php echo $metaTitle; ?>" class="w-full h-auto rounded-lg mb-8 shadow" loading="lazy">
            <?php } ?>

            <!-- Article Content -->
            <div class="prose max-w-none text-gray-800 text-2xl font-bold">
                <?php echo nl2br(htmlspecialchars($articleData['content'])); ?>
            </div>

            <!-- Social Share -->
            <div class="mt-12 border-t pt-6">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-share-nodes text-blue-500"></i> Share this article
                </h3>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($metaUrl); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"
                        class="w-14 h-14 flex items-center justify-center rounded-full bg-blue-600 hover:bg-blue-700 text-white text-2xl shadow transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($metaUrl); ?>&text=<?php echo urlencode($metaTitle); ?>" target="_blank" rel="noopener" aria-label="Share on Twitter"
                        class="w-14 h-14 flex items-center justify-center rounded-full bg-blue-400 hover:bg-blue-500 text-white text-2xl shadow transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($metaUrl); ?>&title=<?php echo urlencode($metaTitle); ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"
                        class="w-14 h-14 flex items-center justify-center rounded-full bg-blue-800 hover:bg-blue-900 text-white text-2xl shadow transition-colors">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <button onclick="copyArticleLink()" class="w-14 h-14 flex items-center justify-center rounded-full bg-gray-300 hover:bg-gray-400 text-gray-700 text-2xl shadow transition-colors" aria-label="Copy link">
                        <i class="fa-solid fa-link"></i>
                    </button>
                </div>
                <div id="copy-link-msg" class="mt-4 text-green-600 font-semibold text-center hidden">Link copied to clipboard!</div>
                    </div>
            <script>
            function copyArticleLink() {
                navigator.clipboard.writeText("<?php echo $metaUrl; ?>");
                var msg = document.getElementById('copy-link-msg');
                msg.classList.remove('hidden');
                setTimeout(function() { msg.classList.add('hidden'); }, 2000);
            }
            </script>

            <!-- Telegram CTA -->
            <style>
            @keyframes telegram-glow {
              0% { box-shadow: 0 0 0 0 #3b82f6, 0 0 0 0 #60a5fa; }
              70% { box-shadow: 0 0 20px 10px #3b82f6, 0 0 40px 20px #60a5fa; }
              100% { box-shadow: 0 0 0 0 #3b82f6, 0 0 0 0 #60a5fa; }
            }
            </style>
            <div class="mt-16 flex justify-center">
                <div class="bg-gradient-to-br from-blue-400 via-blue-200 to-blue-100 rounded-3xl shadow-2xl px-10 py-12 flex flex-col items-center w-full max-w-xl animate-fade-in">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-500 shadow-lg">
                            <i class="fab fa-telegram-plane text-3xl text-white"></i>
                        </span>
                        <h3 class="text-2xl font-extrabold text-blue-800 tracking-wide">Follow us on Telegram</h3>
                    </div>
                    <p class="text-blue-900 text-lg font-semibold mb-6 text-center max-w-md">Get the latest news, updates, and exclusive content directly on your phone. Join our Telegram channel and never miss a story!</p>
                    <a href="https://t.me/borkenanews" target="_blank" rel="noopener" class="inline-flex items-center px-10 py-4 bg-blue-600 text-white rounded-full text-xl font-bold shadow-lg hover:bg-blue-700 transition-colors relative" style="animation: telegram-glow 2.5s infinite alternate;">
                        <i class="fab fa-telegram-plane mr-4 text-2xl"></i> Join @borkenanews
                    </a>
                </div>
            </div>
        </article>

        <!-- Related Articles (outside main article card) -->
        <div class="mt-20">
            <h2 class="text-2xl font-bold mb-6">Related Articles</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($relatedArticles as $related) { ?>
                    <a href="article.php?id=<?php echo $related['id']; ?>" class="block bg-white rounded-lg shadow hover:shadow-lg transition">
                        <img src="<?php echo htmlspecialchars(!empty($related['local_image_path']) ? $related['local_image_path'] : $related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-40 object-cover rounded-t-lg" loading="lazy">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars(mb_substr($related['content'], 0, 90)) . '...'; ?></p>
                            <div class="text-xs text-gray-500 flex justify-between">
                                        <span><?php echo date('M d, Y', strtotime($related['published_at'])); ?></span>
                                        <span><?php echo $related['views']; ?> views</span>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?> 
