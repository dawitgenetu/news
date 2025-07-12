<?php
set_time_limit(600); // Allow up to 10 minutes for script execution
// borkena_scraper.php
// Scrapes news articles from https://borkena.com/ for all categories and saves directly to the database

// CONFIGURATION
$baseUrl = 'https://borkena.com/';
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$logFile = 'borkena_scraper.log';

// Database config
$dbHost = 'localhost';
$dbName = 'reporter';
$dbUser = 'root';
$dbPass = '';

// Updated category pages with proper URLs
$categoryPages = [
    'news' => 'https://borkena.com/category/news/',
    'politics' => 'https://borkena.com/category/politics/',
    'business' => 'https://borkena.com/category/business/',
    'sport' => 'https://borkena.com/category/sport/',
    'opinion' => 'https://borkena.com/category/opinion/',
    'video' => 'https://borkena.com/category/video/',
    'entertainment' => 'https://borkena.com/category/entertainment/',
    'society' => 'https://borkena.com/category/society/',
    'technology' => 'https://borkena.com/category/technology/',
    'health' => 'https://borkena.com/category/health/',
    'education' => 'https://borkena.com/category/education/',
    'international' => 'https://borkena.com/category/international/'
];

// Helper: Log messages
function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

// Helper: Fetch a URL using cURL
function fetchUrl($url, $userAgent) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        logMessage('cURL error: ' . curl_error($ch));
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        logMessage("HTTP error $httpCode for URL: $url");
        return false;
    }
    
    return $html;
}

// Helper: Clean text
function cleanText($text) {
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($text))));
}

// Helper: Parse and validate article date
function parseArticleDate($dateString) {
    if (empty($dateString)) {
        return null;
    }
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

// Helper: Extract full article content
function fetchArticleContent($url, $userAgent) {
    $html = fetchUrl($url, $userAgent);
    if (!$html) {
        logMessage("Failed to fetch article URL: $url");
        return '';
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $contentSelectors = [
        "//div[contains(@class, 'td-post-content')]//p",
        "//div[contains(@class, 'entry-content')]//p",
        "//article//div[contains(@class, 'content')]//p",
        "//div[contains(@class, 'post-content')]//p",
        "//div[@class='td-post-content']//p",
        "//div[contains(@class, 'article-content')]//p",
        "//div[contains(@class, 'post-body')]//p"
    ];
    $content = '';
    foreach ($contentSelectors as $selector) {
        $contentNodes = $xpath->query($selector);
        if ($contentNodes->length > 0) {
            foreach ($contentNodes as $p) {
                $text = cleanText($p->textContent);
                // Filter out paragraphs containing 'borkena subscribe' or 'editor\'s note'
                if (stripos($text, 'borkena subscribe') !== false) continue;
                if (stripos($text, "editor's note") !== false) continue;
                if (strlen($text) > 20) {
                    $content .= $text . "\n\n";
                }
            }
            break;
        }
    }
    return trim($content);
}

// Helper: Extract image URL with multiple fallback methods
function extractImageUrl($node, $xpath) {
    $image = '';
    // Priority: <img> with class containing 'wp-image-'
    $imgNodes = $xpath->query('.//img[contains(@class, "wp-image-")]', $node);
    if ($imgNodes->length > 0) {
        $imgNode = $imgNodes->item(0);
        $image = $imgNode->getAttribute('src');
        if (!$image) {
            $image = $imgNode->getAttribute('data-src');
        }
        if ($image) return $image;
    }
    // Fallback to previous logic
    $thumbDiv = $xpath->query(".//div[contains(@class, 'td-module-thumb')]", $node)->item(0);
    if ($thumbDiv) {
        $dataImgUrl = $thumbDiv->getAttribute('data-img-url');
        if ($dataImgUrl) {
            $image = $dataImgUrl;
        } else {
            $imgNode = $xpath->query('.//img', $thumbDiv)->item(0);
            if ($imgNode) {
                $image = $imgNode->getAttribute('src');
                if (!$image) {
                    $image = $imgNode->getAttribute('data-src');
                }
            }
            if (!$image) {
                $spanNodes = $xpath->query('.//span[contains(@class, "entry-thumb")]', $thumbDiv);
                foreach ($spanNodes as $span) {
                    $style = $span->getAttribute('style');
                    if (preg_match('/background-image:\\s*url\\([\"\']?([^\"\']+)[\"\']?\\)/i', $style, $matches)) {
                        $image = $matches[1];
                        break;
                    }
                }
            }
            if (!$image) {
                $style = $thumbDiv->getAttribute('style');
                if (preg_match('/background-image:\\s*url\\([\"\']?([^\"\']+)[\"\']?\\)/i', $style, $matches)) {
                    $image = $matches[1];
                }
            }
            if (!$image) {
                $noscript = $xpath->query('.//noscript', $thumbDiv)->item(0);
                if ($noscript) {
                    if (preg_match('/<img[^>]+src=[\"\']([^\"\']+)[\"\']/i', $noscript->nodeValue, $matches)) {
                        $image = $matches[1];
                    }
                }
            }
        }
    }
    if (!$image) {
        $imgNode = $xpath->query('.//img', $node)->item(0);
        if ($imgNode) {
            $image = $imgNode->getAttribute('src');
            if (!$image) {
                $image = $imgNode->getAttribute('data-src');
            }
        }
    }
    return $image;
}

// Database connection
try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper: Get or create category
function getCategoryId($db, $categoryName) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $categoryName));
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

// Main scraping function for a given category page
function scrapeBorkenaCategory($categoryUrl, $userAgent, $categoryName, $db) {
    echo "\n=== Scraping category: $categoryName ===\n";
    echo "URL: $categoryUrl\n";
    $html = fetchUrl($categoryUrl, $userAgent);
    if (!$html) {
        echo "Failed to fetch category page: $categoryName\n";
        logMessage("Failed to fetch category page: $categoryName");
        return 0;
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $articleSelectors = [
        "//div[contains(@class, 'td_module_')]",
        "//article[contains(@class, 'post')]",
        "//div[contains(@class, 'post-item')]",
        "//div[contains(@class, 'article-item')]",
        "//div[@class='td-block-span6']",
        "//div[contains(@class, 'td-block-span')]",
        "//article",
        "//div[contains(@class, 'entry')]",
        "//div[contains(@class, 'post')]",
        "//div[contains(@class, 'item')]"
    ];
    $articleNodes = null;
    foreach ($articleSelectors as $selector) {
        $articleNodes = $xpath->query($selector);
        if ($articleNodes->length > 0) {
            echo "Found " . $articleNodes->length . " articles using selector: $selector\n";
            break;
        }
    }
    if (!$articleNodes || $articleNodes->length === 0) {
        echo "Warning: No article nodes found in $categoryName.\n";
        logMessage("No article nodes found in $categoryName.");
        return 0;
    }
    $count = 0;
    $maxArticles = 15;
    foreach ($articleNodes as $node) {
        if ($count >= $maxArticles) break;
        // Title and URL extraction
        $titleSelectors = [
            ".//h3/a", ".//h2/a", ".//h1/a",
            ".//a[contains(@class, 'title')]",
            ".//a[contains(@class, 'entry-title')]",
            ".//a[contains(@class, 'post-title')]",
            ".//a[contains(@class, 'article-title')]",
            ".//a[contains(@class, 'link')]",
            ".//a[contains(@href, '/20')]",
            ".//a"
        ];
        $titleNode = null;
        foreach ($titleSelectors as $selector) {
            $titleNodes = $xpath->query($selector, $node);
            foreach ($titleNodes as $tn) {
                $href = $tn->getAttribute('href');
                $text = cleanText($tn->textContent);
                if ($href && $text && strpos($href, '/20') !== false && strlen($text) > 10 && strpos($href, 'borkena.com') !== false) {
                    $titleNode = $tn;
                    break 2;
                }
            }
        }
        if (!$titleNode) {
            echo "Skipping article - no valid title found\n";
            continue;
        }
        $title = cleanText($titleNode->textContent);
        $url = $titleNode->getAttribute('href');
        if (empty($title) || empty($url)) {
            echo "Skipping article - empty title or URL\n";
            continue;
        }
        if (strpos($url, '/20') === false || strpos($url, 'borkena.com') === false) {
            echo "Skipping article - invalid URL: $url\n";
            continue;
        }
        // Extract image
        $image = extractImageUrl($node, $xpath);
        // Extract excerpt/summary
        $excerptSelectors = [
            ".//div[contains(@class, 'td-excerpt')]",
            ".//div[contains(@class, 'entry-summary')]",
            ".//p[contains(@class, 'excerpt')]",
            ".//div[contains(@class, 'td-module-meta-info')]/p",
            ".//div[contains(@class, 'summary')]",
            ".//p[contains(@class, 'description')]"
        ];
        $excerpt = '';
        foreach ($excerptSelectors as $selector) {
            $excerptNode = $xpath->query($selector, $node)->item(0);
            if ($excerptNode) {
                $excerpt = cleanText($excerptNode->textContent);
                break;
            }
        }
        // Extract date
        $dateSelectors = [
            ".//time",
            ".//span[contains(@class, 'date')]",
            ".//div[contains(@class, 'date')]",
            ".//time[@datetime]",
            ".//span[contains(@class, 'time')]",
            ".//div[contains(@class, 'meta')]//span"
        ];
        $date = '';
        foreach ($dateSelectors as $selector) {
            $dateNode = $xpath->query($selector, $node)->item(0);
            if ($dateNode) {
                $date = cleanText($dateNode->textContent);
                if (!$date) {
                    $date = $dateNode->getAttribute('datetime');
                }
                break;
            }
        }
        $parsedDate = parseArticleDate($date);
        // Fetch full article content
        echo "Fetching content for: $title\n";
        $content = fetchArticleContent($url, $userAgent);
        if (!$content) {
            echo "Warning: No content found for $url\n";
            logMessage("No content found for $url");
        }
        // Check for duplicate by URL
        $stmt = $db->prepare("SELECT id FROM articles WHERE url = ?");
        $stmt->execute([$url]);
        if ($stmt->fetch()) {
            echo "Skipping duplicate: $title (URL already exists)\n";
            continue;
        }
        // Get or create category
        $categoryId = getCategoryId($db, ucfirst($categoryName));
        // Insert article
        try {
            $stmt = $db->prepare("
                INSERT INTO articles (
                    title, slug, content, excerpt, image_url, category_id, 
                    published_at, status, url
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?)
            ");
            $slug = strtolower(trim(preg_replace('/[^a-z0-9\s-]/', '', $title)));
            $slug = preg_replace('/[\s-]+/', '-', $slug);
            $slug = trim($slug, '-');
            if (strlen($slug) > 200) {
                $slug = substr($slug, 0, 200);
                $slug = rtrim($slug, '-');
            }
            $stmt->execute([
                $title, $slug, $content, $excerpt, $image, $categoryId, $parsedDate, $url
            ]);
            echo "Inserted: $title (Category: $categoryName)\n";
            $count++;
        } catch (Exception $e) {
            echo "Error inserting '$title': " . $e->getMessage() . "\n";
            logMessage("Error inserting '$title': " . $e->getMessage());
        }
        sleep(2); // Be respectful to the server
    }
    echo "Completed scraping $categoryName: $count articles inserted\n";
    return $count;
}

// MAIN EXECUTION
$start = microtime(true);
logMessage('Scraping started (direct to DB, no JSON).');

echo "Starting Borkena News Scraper (direct to DB)\n";
echo "=============================\n";
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

echo "\n========================================\n";
echo "SCRAPING COMPLETE\n";
echo "========================================\n";
echo "Total articles inserted: $totalInserted\n";
echo "Time taken: $duration seconds\n";
echo "END OF SCRIPT\n";
?> 