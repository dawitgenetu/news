<?php
$page_title = 'Technology';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

$article = new Article($conn);
$articles = $article->getByCategory('technology');
$trendingArticles = $article->getTrending(2);
?>
<div class="max-w-7xl mx-auto px-4 py-8 fade-in">
    <h1 class="text-4xl font-bold mb-8">Technology News</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <?php echo displayArticles($articles); ?>
    </div>
    <h2 class="text-3xl font-bold mb-6">Trending</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php echo displayTrendingArticles($trendingArticles); ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?> 