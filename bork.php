<?php
// borkena_scraper.php - Scrape news from borkena.com and upload to MySQL database
set_time_limit(600);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_config.php';

// CONFIG
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
];

function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

function fetchUrl($url, $userAgent) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1',
            'Referer: https://borkena.com/'
        ]
    ]);
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
        $parsed = DateTime::createFromFormat($format, $dateString);
        if ($parsed !== false) return $parsed->format('Y-m-d H:i:s');
    }
    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $dateString, $m)) {
        return "{$m[1]}-".str_pad($m[2], 2, '0', STR_PAD_LEFT)."-".str_pad($m[3], 2, '0', STR_PAD_LEFT)." 00:00:00";
    }
    return null;
}

function getOrCreateCategoryId($db, $name) {
    $slug = generate_slug($name);
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
    $stmt->execute([$name, $slug]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) return $row['id'];
    $desc = $name . ' news and updates';
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
    $stmt->execute([$name, $slug, $desc]);
    return $db->lastInsertId();
}

function extractImageUrl($node, $xpath) {
    $imgNode = $xpath->query('.//img', $node)->item(0);
    if ($imgNode) {
        $attrs = ['src', 'data-src', 'data-lazy-src', 'data-original', 'data-srcset'];
        foreach ($attrs as $attr) {
            $url = $imgNode->getAttribute($attr);
            if ($url) return normalize_borkena_image_url($url);
        }
    }
    return '';
}

function normalize_borkena_image_url($img_url) {
    $img_url = html_entity_decode(trim($img_url));
    if (strpos($img_url, '//') === 0) $img_url = 'https:' . $img_url;
    if (strpos($img_url, '/wp-content/uploads/') === 0) {
        $img_url = 'https://borkena.com' . $img_url;
    }
    $img_url = preg_replace('#(?<!:)//+#', '/', $img_url);
    if (strpos($img_url, 'https://borkena.com/wp-content/uploads/') === 0) {
        return $img_url;
    }
    return '';
}

function extract_published_date($xpath) {
    $timeNode = $xpath->query('//time')->item(0);
    if ($timeNode) {
        $raw = $timeNode->getAttribute('datetime') ?: $timeNode->textContent;
        return parseArticleDate($raw);
    }
    $metaDate = $xpath->query('//meta[@property="article:published_time"]')->item(0);
    if ($metaDate) return parseArticleDate($metaDate->getAttribute('content'));

    $fallback = $xpath->query("//*[contains(@class,'date')]")->item(0);
    if ($fallback) return parseArticleDate($fallback->textContent);

    return null;
}

function fetchArticleContent($url, $userAgent) {
    $html = fetchUrl($url, $userAgent);
    if (!$html) return '';
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $node = $xpath->query("//div[contains(@class, 'tdb_single_content')]")->item(0);
    if (!$node) return '';
    $content = '';
    foreach ($xpath->query(".//p|.//h2|.//ul|.//ol", $node) as $n) {
        $text = trim($n->textContent);
        if (stripos($text, 'borkena subscribe') !== false) continue;
        if (stripos($text, "editor's note") !== false) continue;
        if (strlen($text) > 20) $content .= $text . "\n\n";
    }
    return trim($content);
}

function download_and_save_image($imageUrl) {
    $data = @file_get_contents($imageUrl);
    if ($data === false) return '';
    $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $name = uniqid('img_') . '.' . $ext;
    $dir = __DIR__ . '/uploads/images/';
    if (!file_exists($dir)) mkdir($dir, 0777, true);
    $path = $dir . $name;
    return file_put_contents($path, $data) ? 'uploads/images/' . $name : '';
}

function get_excerpt($text, $length = 200) {
    return mb_substr(trim(strip_tags($text)), 0, $length) . '...';
}

function scrapeBorkenaCategory($categoryUrl, $userAgent, $categoryName, $db) {
    echo "\nScraping category: $categoryName\n";
    $html = fetchUrl($categoryUrl, $userAgent);
    if (!$html) return 0;
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $articles = $xpath->query("//div[contains(@class, 'td_module_')]//h3/a");
    if ($articles->length === 0) return 0;
    $count = 0;
    foreach ($articles as $a) {
        if ($count >= 10) break;
        $title = cleanText($a->textContent);
        $url = $a->getAttribute('href');
        if (empty($title) || empty($url)) continue;

        $stmt = $db->prepare("SELECT id FROM articles WHERE url = ?");
        $stmt->execute([$url]);
        if ($stmt->fetch()) continue;

        $image = extractImageUrl($a->parentNode->parentNode, $xpath);
        $localImagePath = $image ? download_and_save_image($image) : '';

        $content = fetchArticleContent($url, $userAgent);
        $excerpt = get_excerpt($content);
        $published_at = date('Y-m-d H:i:s');

        $articleHtml = fetchUrl($url, $userAgent);
        if ($articleHtml) {
            $doc2 = new DOMDocument();
            @$doc2->loadHTML($articleHtml);
            $xp2 = new DOMXPath($doc2);
            $date = extract_published_date($xp2);
            if ($date) $published_at = $date;
            logMessage("Article Date for '$title': $published_at");
        }

        $catId = getOrCreateCategoryId($db, ucfirst($categoryName));
        try {
            $slug = generate_slug($title);
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, local_image_path, url, category_id, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $localImagePath, $localImagePath, $url, $catId, $published_at]);
            echo "Inserted: $title\n";
            $count++;
        } catch (Exception $e) {
            logMessage("Insert error: " . $e->getMessage());
        }
        sleep(2);
    }
    return $count;
}

// START SCRAPER
echo "=== Borkena Scraper Started ===\n";
logMessage('Scraper started');
$total = 0;
foreach ($categoryPages as $name => $url) {
    $inserted = scrapeBorkenaCategory($url, $userAgent, $name, $db);
    $total += $inserted;
    echo "Total so far: $total\n";
    sleep(3);
}
logMessage("Scraper finished. Total inserted: $total");
echo "=== Scraper Complete. Total: $total ===\n";
?>
