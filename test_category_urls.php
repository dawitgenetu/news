<?php
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

function fetchUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['html' => $html, 'code' => $httpCode];
}

// Test more category URLs
$testUrls = [
    'https://borkena.com/category/news/',
    'https://borkena.com/category/politics/',
    'https://borkena.com/category/business/',
    'https://borkena.com/category/sport/',
    'https://borkena.com/category/opinion/',
    'https://borkena.com/category/video/',
    'https://borkena.com/category/entertainment/',
    'https://borkena.com/category/society/',
    'https://borkena.com/category/culture/',
    'https://borkena.com/category/technology/',
    'https://borkena.com/category/health/',
    'https://borkena.com/category/education/'
];

echo "Testing category URLs:\n";
echo "=====================\n";

foreach ($testUrls as $url) {
    $category = basename(parse_url($url, PHP_URL_PATH));
    echo "Testing: $category\n";
    $result = fetchUrl($url);
    echo "HTTP Code: " . $result['code'] . "\n";
    
    if ($result['code'] == 200 && $result['html']) {
        $doc = new DOMDocument();
        @$doc->loadHTML($result['html']);
        $xpath = new DOMXPath($doc);
        $articleNodes = $xpath->query("//div[contains(@class, 'td_module_')]");
        echo "Found " . $articleNodes->length . " article nodes\n";
        
        if ($articleNodes->length > 0) {
            echo "SUCCESS! This URL works.\n";
            // Show first article title
            $titleNode = $xpath->query(".//h3/a", $articleNodes->item(0))->item(0);
            if ($titleNode) {
                echo "First article: " . trim($titleNode->textContent) . "\n";
            }
        }
    } else {
        echo "Failed to fetch or invalid response\n";
    }
    echo "---\n";
}

// Also check homepage to see what categories are linked
echo "\nChecking homepage for available categories:\n";
echo "===========================================\n";

$homeResult = fetchUrl('https://borkena.com/');
if ($homeResult['code'] == 200 && $homeResult['html']) {
    $doc = new DOMDocument();
    @$doc->loadHTML($homeResult['html']);
    $xpath = new DOMXPath($doc);
    
    // Look for category links
    $categoryLinks = $xpath->query("//a[contains(@href, '/category/')]");
    $categories = [];
    foreach ($categoryLinks as $link) {
        $href = $link->getAttribute('href');
        if (preg_match('/\/category\/([^\/]+)/', $href, $matches)) {
            $categories[] = $matches[1];
        }
    }
    
    $categories = array_unique($categories);
    echo "Categories found on homepage:\n";
    foreach ($categories as $cat) {
        echo "- $cat\n";
    }
}
?> 