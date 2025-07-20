<?php
// borkena_scraper.php - Scrape news from borkena.com and upload to MySQL database
set_time_limit(0);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_config.php';

// Ensure borkena_images table exists with local_image_path
// Remove borkena_images table creation and related logic

// --- CONFIG ---
$baseUrl = 'https://borkena.com/';
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$logFile = __DIR__ . '/borkena_scraper.log';

$categoryPages = [
    'news' => 'https://borkena.com/ethiopia-news/',
    'business' => 'https://borkena.com/ethiopia-business-news/',
    'opinion' => 'https://borkena.com/ethiopian-news-and-opinion/',
    'entertainment' => 'https://borkena.com/entertainment-ethiopian-music-drama-show/',
    'politics' => 'https://borkena.com/category/politics/',
    'sport' => 'https://borkena.com/category/sport/',
    'video' => 'https://borkena.com/category/video/',
    'travel' => 'https://borkena.com/category/travel/',
    'restaurant' => 'https://borkena.com/category/restaurant/',
    'health' => 'https://borkena.com/category/health/',
    'technology' => 'https://borkena.com/category/technology/',
    // The following categories were removed due to broken or inaccessible URLs:
    // 'society' => 'https://borkena.com/category/society/',
    // 'education' => 'https://borkena.com/category/education/',
    // 'international' => 'https://borkena.com/category/international/'
];

function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

function fetchUrl($url, $userAgent) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Lower timeout to 10 seconds
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    // Add more headers to mimic a real browser
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive',
        'Cache-Control: max-age=0',
        'Upgrade-Insecure-Requests: 1',
        'Referer: https://borkena.com/'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        logMessage('cURL error: ' . curl_error($ch) . ' for URL: ' . $url);
        curl_close($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
        logMessage("HTTP error $httpCode for URL: $url");
        return false;
    }
    logMessage('Fetched HTML (first 500 chars): ' . substr($html, 0, 500));
    return $html;
}

function cleanText($text) {
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($text))));
}

function parseArticleDate($dateString) {
    if (empty($dateString)) return null;
    $dateString = trim($dateString);
    $dateFormats = [
        'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
        'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
        'm/d/Y H:i:s', 'm/d/Y H:i', 'm/d/Y',
        'F j, Y H:i:s', 'F j, Y H:i', 'F j, Y',
        'j F Y H:i:s', 'j F Y H:i', 'j F Y'
    ];
    foreach ($dateFormats as $format) {
        $parsedDate = DateTime::createFromFormat($format, $dateString);
        if ($parsedDate !== false) {
            return $parsedDate->format('Y-m-d H:i:s');
        }
    }
    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $dateString, $matches)) {
        $year = $matches[1];
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
        $articleDate = "$year-$month-$day";
        return "$articleDate 00:00:00";
    }
    return null;
}

function getOrCreateCategoryId($db, $categoryName) {
    $slug = generate_slug($categoryName);
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
    $stmt->execute([$categoryName, $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row['id'];
    $desc = $categoryName . ' news and updates';
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    $stmt->execute([$categoryName, $slug, $desc]);
    logMessage("Created new category: $categoryName");
    return $db->lastInsertId();
}

function fetchArticleContent($url, $userAgent) {
    $html = fetchUrl($url, $userAgent);
    if (!$html) {
        logMessage("Failed to fetch article URL: $url");
        return '';
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $contentNode = $xpath->query("//div[contains(@class, 'tdb_single_content')]")->item(0);
    if (!$contentNode) {
        logMessage("No main content found for $url");
        return '';
    }
    $content = '';
    foreach ($xpath->query(".//p|.//h2|.//ul|.//ol", $contentNode) as $node) {
        $text = trim($node->textContent);
        if (stripos($text, 'borkena subscribe') !== false) continue;
        if (stripos($text, "editor's note") !== false) continue;
        if (strlen($text) > 20) {
            $content .= $text . "\n\n";
        }
    }
    return trim($content);
}

function extractImageUrl($node, $xpath, $articleUrl = null, $userAgent = null) {
    // Log the HTML of the node being checked
    if ($node instanceof DOMNode) {
        $tmpDoc = new DOMDocument();
        $tmpDoc->appendChild($tmpDoc->importNode($node, true));
        logMessage("extractImageUrl node HTML: " . $tmpDoc->saveHTML());
    }
    // Find any <img> with class containing 'wp-image-' and 'lazyloaded'
    $imgNode = $xpath->query('.//img[contains(@class, "wp-image-") and contains(@class, "lazyloaded")]', $node)->item(0);
    if ($imgNode) {
        $image = $imgNode->getAttribute('src');
        if (!$image) {
            $image = $imgNode->getAttribute('data-src');
        }
        logMessage("extractImageUrl: Found <img> with class 'wp-image-* lazyloaded', src: $image");
        if ($image) {
            return $image;
        }
    }
    // Fallback: any <img>
    $imgNode = $xpath->query('.//img', $node)->item(0);
    if ($imgNode) {
        $image = $imgNode->getAttribute('src');
        if (!$image) {
            $image = $imgNode->getAttribute('data-src');
        }
        logMessage("extractImageUrl: Fallback <img>, src: $image");
        if ($image) {
            return $image;
        }
    }
    logMessage("extractImageUrl: No image found in node.");
    return '';
}

// Helper to download image and return local path
function download_and_save_image($imageUrl) {
    $imageData = @file_get_contents($imageUrl);
    if ($imageData === false) {
        return '';
    }
    $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!$ext) $ext = 'jpg';
    $filename = uniqid('img_') . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filePath = $uploadDir . $filename;
    if (file_put_contents($filePath, $imageData)) {
        return 'uploads/images/' . $filename;
    } else {
        return '';
    }
}

function extract_featured_image($xpath) {
    // Try og:image meta tag
    $meta = $xpath->query('//meta[@property="og:image"]')->item(0);
    if ($meta) {
        $url = normalize_image_url($meta->getAttribute('content'));
        if ($url) return $url;
    }
    // Fallback: first <img> in main content
    $content = $xpath->query("//div[contains(@class, 'tdb_single_content')]")->item(0);
    if ($content) {
        foreach ($content->getElementsByTagName('img') as $img) {
            foreach (['src', 'data-src', 'data-lazy-src', 'data-original'] as $attr) {
                $url = normalize_image_url($img->getAttribute($attr));
                if ($url) return $url;
            }
        }
    }
    return '';
}

function extract_publish_date($xpath) {
    $time = $xpath->query('//time')->item(0);
    if ($time) {
        $date = $time->getAttribute('datetime') ?: $time->textContent;
        if ($date) return date('Y-m-d H:i:s', strtotime($date));
    }
    $meta = $xpath->query('//meta[@property="article:published_time"]')->item(0);
    if ($meta) {
        $date = $meta->getAttribute('content');
        if ($date) return date('Y-m-d H:i:s', strtotime($date));
    }
    return date('Y-m-d H:i:s'); // fallback
}

function normalize_image_url($url) {
    $url = html_entity_decode(trim($url));
    if (!$url) return '';
    if (strpos($url, '//borkena.com') === 0) $url = 'https:' . $url;
    if (strpos($url, '/wp-content/uploads/') === 0) $url = 'https://borkena.com' . $url;
    if (strpos($url, 'https://borkena.com/wp-content/uploads/') === 0) {
        $url = preg_replace('#(?<!:)//+#', '/', $url);
        return $url;
    }
    return '';
}

function scrapeBorkenaCategory($categoryUrl, $userAgent, $categoryName, $db) {
    $cutoffDate = date('Y-m-d');
    echo "\n=== Scraping category: $categoryName ===\n";
    echo "Current date: $cutoffDate\n";
    $html = fetchUrl($categoryUrl, $userAgent);
    if (!$html) {
        echo "Failed to fetch category page: $categoryName\n";
        logMessage("Failed to fetch category page: $categoryName");
        return 0;
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $articleNodes = $xpath->query("//div[contains(@class, 'td_module_')]//h3/a");
    if ($articleNodes->length === 0) {
        echo "Warning: No article nodes found in $categoryName.\n";
        logMessage("No article nodes found in $categoryName.");
        return 0;
    }
    $count = 0;
    $maxArticles = 20;
    foreach ($articleNodes as $titleNode) {
        if ($count >= $maxArticles) break;
        $title = cleanText($titleNode->textContent);
        $url = $titleNode->getAttribute('href');
        if (empty($title) || empty($url)) continue;
        // Check for duplicate by URL
        $stmt = $db->prepare("SELECT id FROM articles WHERE url = ?");
        $stmt->execute([$url]);
        if ($stmt->fetch()) {
            echo "Skipping duplicate: $title (URL already exists)\n";
            continue;
        }
        // Fetch article HTML
        $articleHtml = fetchUrl($url, $userAgent);
        if (!$articleHtml) {
            echo "Warning: Could not fetch article HTML for $url\n";
            continue;
        }
        $adoc = new DOMDocument();
        @$adoc->loadHTML($articleHtml);
        $axp = new DOMXPath($adoc);
        // Featured image
        $image_url = extract_featured_image($axp);
        $localImagePath = $image_url ? download_and_save_image($image_url) : '';
        // Content
        $content = fetchArticleContent($url, $userAgent);
        if (!$content) {
            echo "Warning: No content found for $url\n";
            logMessage("No content found for $url");
        }
        $excerpt = get_excerpt($content, 200);
        $published_at = extract_publish_date($axp);
        $categoryId = getOrCreateCategoryId($db, ucfirst($categoryName));
        // No date filter: insert all articles regardless of date
        try {
            $slug = generate_slug($title);
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, local_image_path, url, category_id, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $image_url, $localImagePath, $url, $categoryId, $published_at]);
            echo "Inserted: $title | $published_at (Category: $categoryName)\n";
            logMessage("Inserted: $title | $published_at (Category: $categoryName)");
            $count++;
        } catch (Exception $e) {
            echo "Error inserting '$title': " . $e->getMessage() . "\n";
            logMessage("Error inserting '$title': " . $e->getMessage());
        }
        sleep(2);
    }
    echo "Completed scraping $categoryName: $count articles inserted\n";
    return $count;
}

/**
 * Scrape all image URLs from a borkena.com article page.
 * Handles absolute/relative URLs, src/data-src, and only collects /wp-content/uploads/ images.
 * Usage: php borkena_scraper.php or run in browser.
 */

function get_borkena_article_images($article_url) {
    // Try file_get_contents first
    $html = @file_get_contents($article_url);
    if ($html === false) {
        // Fallback to cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $article_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; BorkenaScraper/1.0)');
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false || $http_code !== 200) {
            echo "<p style='color:red;'>Failed to fetch article HTML from: $article_url (HTTP code: $http_code)</p>";
            return [];
        }
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $images = [];
    $xpath = new DOMXPath($dom);

    // 1. Try to get the featured image from meta tags
    $metaImage = $xpath->query('//meta[@property="og:image"]')->item(0);
    if ($metaImage) {
        $featured_url = html_entity_decode(trim($metaImage->getAttribute('content')));
        if ($featured_url && strpos($featured_url, 'borkena.com/wp-content/uploads/') !== false) {
            // Normalize protocol-relative and relative URLs
            if (strpos($featured_url, '//borkena.com') === 0) $featured_url = 'https:' . $featured_url;
            if (strpos($featured_url, '/wp-content/uploads/') === 0) $featured_url = 'https://borkena.com' . $featured_url;
            if (strpos($featured_url, 'https://borkena.com/wp-content/uploads/') === 0) {
                $featured_url = preg_replace('#(?<!:)//+#', '/', $featured_url);
                $images[] = $featured_url;
            }
        }
    }

    // 2. Then, get images from .tdb_single_content as before
    echo "<h3>Debug: All <img> tags found in the HTML</h3>";
    echo "<ul style='font-size:13px;'>";
    $contentNode = $xpath->query("//div[contains(@class, 'tdb_single_content')]")->item(0);
    if ($contentNode) {
        foreach ($contentNode->getElementsByTagName('img') as $img) {
            $src = html_entity_decode(trim($img->getAttribute('src')));
            $data_src = html_entity_decode(trim($img->getAttribute('data-src')));
            $data_lazy_src = html_entity_decode(trim($img->getAttribute('data-lazy-src')));
            $data_original = html_entity_decode(trim($img->getAttribute('data-original')));
            $data_srcset = html_entity_decode(trim($img->getAttribute('data-srcset')));
            echo "<li>src: <code>" . htmlspecialchars($src) . "</code> | data-src: <code>" . htmlspecialchars($data_src) . "</code> | data-lazy-src: <code>" . htmlspecialchars($data_lazy_src) . "</code> | data-original: <code>" . htmlspecialchars($data_original) . "</code> | data-srcset: <code>" . htmlspecialchars($data_srcset) . "</code></li>";

            // Prefer src, then data-src, then data-lazy-src, then data-original, then data-srcset
            $img_url = $src ?: $data_src ?: $data_lazy_src ?: $data_original ?: $data_srcset;

            // Normalize protocol-relative URLs
            if (strpos($img_url, '//borkena.com/wp-content/uploads/') === 0) $img_url = 'https:' . $img_url;
            if (strpos($img_url, '/wp-content/uploads/') === 0) $img_url = 'https://borkena.com' . $img_url;
            if (strpos($img_url, 'https://borkena.com/wp-content/uploads/') === 0) {
                $img_url = preg_replace('#(?<!:)//+#', '/', $img_url);
                if (!in_array($img_url, $images)) { // Avoid duplicates
                    $images[] = $img_url;
                }
            }
        }
    } else {
        echo "<li style='color:red;'>No .tdb_single_content node found in article HTML.</li>";
    }
    echo "</ul>";
    return $images;
}

// Add this function at the top level
function extract_published_date($xpath) {
    $published_at = null;
    $timeNode = $xpath->query('//time')->item(0);
    if ($timeNode) {
        $published_at = $timeNode->getAttribute('datetime') ?: $timeNode->textContent;
        $published_at = date('Y-m-d H:i:s', strtotime($published_at));
    } else {
        $metaDate = $xpath->query('//meta[@property="article:published_time"]')->item(0);
        if ($metaDate) {
            $published_at = $metaDate->getAttribute('content');
            $published_at = date('Y-m-d H:i:s', strtotime($published_at));
        }
    }
    return $published_at;
}

// Example usage:
$test_url = isset($_GET['url']) ? $_GET['url'] : 'https://borkena.com/2024/12/01/ethiopia-news-headline/'; // Use a real article URL
$found_images = get_borkena_article_images($test_url);

if (empty($found_images)) {
    echo "<p>No images found in the article.</p>";
} else {
    echo "<h2>Images found in the article:</h2>";
    foreach ($found_images as $url) {
        echo "<div style='display:inline-block;margin:10px;'><img src='$url' style='max-width:300px;'><br><small>$url</small></div>";
    }
}

// Remove all borkena_images insert logic

// MAIN EXECUTION
$start = microtime(true);
logMessage('Scraping started.');
echo "Starting Borkena News Scraper\n=============================\n";
echo "Current date: " . date('Y-m-d') . "\n\n";

$totalInserted = 0;
$totalCategories = count($categoryPages);
$currentCategory = 0;

foreach ($categoryPages as $catName => $catUrl) {
    $currentCategory++;
    echo "\nProgress: $currentCategory/$totalCategories\n";
    $inserted = scrapeBorkenaCategory($catUrl, $userAgent, $catName, $db);
    $totalInserted += $inserted;
    echo "Total articles inserted so far: $totalInserted\n";
    sleep(3);
}

$end = microtime(true);
$duration = round($end - $start, 2);
logMessage('Scraping finished. Articles inserted: ' . $totalInserted . '. Time taken: ' . $duration . 's.');
echo "\n========================================\nSCRAPING COMPLETE\n========================================\n";
echo "Total articles inserted: $totalInserted\n";
echo "Time taken: $duration seconds\n";
echo "END OF SCRIPT\n";
?> 