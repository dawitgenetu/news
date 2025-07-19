<?php
require_once 'db_config.php';

class Article {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getByCategory($category) {
        $stmt = $this->conn->prepare(
            "SELECT a.* FROM articles a
             JOIN categories c ON a.category_id = c.id
             WHERE c.name = ? ORDER BY a.created_at DESC"
        );
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTrending($limit = 5) {
        $stmt = $this->conn->prepare("SELECT * FROM articles ORDER BY views DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getFeatured($limit = 3) {
        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE is_featured = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getLatest($limit = 6) {
        $stmt = $this->conn->prepare("SELECT * FROM articles ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPopular($limit = 6) {
        $stmt = $this->conn->prepare("SELECT * FROM articles ORDER BY views DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getBySlug($slug) {
        $stmt = $this->conn->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->bind_param("i", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function incrementViews($id) {
        $stmt = $this->conn->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

// Helper function to display articles in a grid
function displayArticles($articles) {
    $output = '';
    foreach ($articles as $article) {
        $output .= '
        <a href="article.php?id=' . $article['id'] . '" class="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-48 object-cover">
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-2 text-gray-900">' . htmlspecialchars($article['title']) . '</h3>
                <p class="text-gray-600 mb-4">' . htmlspecialchars(substr($article['content'], 0, 150)) . '...</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">' . date('M d, Y', strtotime($article['created_at'])) . '</span>
                </div>
            </div>
        </a>';
    }
    return $output;
}

// Helper function to display trending articles
function displayTrendingArticles($articles) {
    $output = '';
    foreach ($articles as $article) {
        $output .= '
        <a href="article.php?id=' . $article['id'] . '" class="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-64 object-cover">
            <div class="p-6">
                <h3 class="text-2xl font-semibold mb-3 text-gray-900">' . htmlspecialchars($article['title']) . '</h3>
                <p class="text-gray-600 mb-4">' . htmlspecialchars(substr($article['content'], 0, 200)) . '...</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">' . date('M d, Y', strtotime($article['created_at'])) . '</span>
                </div>
            </div>
        </a>';
    }
    return $output;
}

// Helper function to display featured articles
function displayFeaturedArticles($articles) {
    $output = '';
    foreach ($articles as $article) {
        $output .= '
        <a href="article.php?id=' . $article['id'] . '" class="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-72 object-cover">
            <div class="p-6">
                <span class="text-sm text-blue-600 font-semibold">FEATURED</span>
                <h3 class="text-2xl font-semibold mt-2 mb-3 text-gray-900">' . htmlspecialchars($article['title']) . '</h3>
                <p class="text-gray-600 mb-4">' . htmlspecialchars(substr($article['content'], 0, 200)) . '...</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">By ' . htmlspecialchars($article['author']) . ' | ' . date('M d, Y', strtotime($article['created_at'])) . '</span>
                </div>
            </div>
        </a>';
    }
    return $output;
}

// Helper function to get article by slug
function getArticleBySlug($slug) {
    global $conn;
    $article = new Article($conn);
    return $article->getBySlug($slug);
}

function incrementArticleViews($article_id) {
    global $conn;
    $query = "UPDATE articles SET views = views + 1 WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $article_id);
    return $stmt->execute();
}
?> 