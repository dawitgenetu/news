<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'reporter');

try {
    // Create PDO instance
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // Log error and display user-friendly message
    error_log("Connection failed: " . $e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Site configuration
define('SITE_NAME', 'Borkena News Reporter');
define('SITE_URL', 'http://localhost/reporter');
define('SITE_EMAIL', 'info@borkena.com');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Africa/Addis_Ababa');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);

// Create upload directories if they don't exist
$upload_dirs = [
    UPLOAD_DIR,
    UPLOAD_DIR . 'images/',
    UPLOAD_DIR . 'videos/',
    UPLOAD_DIR . 'thumbnails/'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Helper functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_slug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function get_excerpt($content, $length = 150) {
    $excerpt = strip_tags($content);
    if (strlen($excerpt) > $length) {
        $excerpt = substr($excerpt, 0, $length) . '...';
    }
    return $excerpt;
}

// Security functions
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}

// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Pagination function
function get_pagination($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    return [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

// Cache configuration
define('CACHE_ENABLED', true);
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_TIME', 3600); // 1 hour

if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0777, true);
}

// API configuration
define('YOUTUBE_API_KEY', ''); // Add your YouTube API key here
define('GOOGLE_MAPS_API_KEY', ''); // Add your Google Maps API key here

// Social media configuration
define('FACEBOOK_APP_ID', '');
define('TWITTER_HANDLE', '@borkena');
define('INSTAGRAM_HANDLE', '@borkena');

// Newsletter configuration
define('MAILCHIMP_API_KEY', '');
define('MAILCHIMP_LIST_ID', '');

// Analytics configuration
define('GOOGLE_ANALYTICS_ID', ''); // Add your Google Analytics ID here
?> 