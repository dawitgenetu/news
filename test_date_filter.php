<?php
// test_date_filter.php
// Test script to verify date filtering functionality

// Simulate the date filtering logic from the scraper
$daysToScrape = 10;
$cutoffDate = date('Y-m-d', strtotime("-$daysToScrape days"));

echo "Testing Date Filter Functionality\n";
echo "================================\n";
echo "Cutoff date: $cutoffDate (only articles from this date onwards)\n";
echo "Current date: " . date('Y-m-d') . "\n\n";

// Test date parsing function
function parseArticleDate($dateString) {
    global $cutoffDate;
    
    if (empty($dateString)) {
        return false;
    }
    
    // Clean the date string
    $dateString = trim($dateString);
    
    // Try different date formats
    $dateFormats = [
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'm/d/Y H:i:s',
        'm/d/Y H:i',
        'm/d/Y',
        'F j, Y H:i:s',
        'F j, Y H:i',
        'F j, Y',
        'j F Y H:i:s',
        'j F Y H:i',
        'j F Y'
    ];
    
    foreach ($dateFormats as $format) {
        $parsedDate = DateTime::createFromFormat($format, $dateString);
        if ($parsedDate !== false) {
            $articleDate = $parsedDate->format('Y-m-d');
            
            // Check if article is within the cutoff date
            if ($articleDate >= $cutoffDate) {
                return $parsedDate->format('Y-m-d H:i:s');
            } else {
                return false; // Article is too old
            }
        }
    }
    
    // If no format matches, try to extract date from common patterns
    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $dateString, $matches)) {
        $year = $matches[1];
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
        $articleDate = "$year-$month-$day";
        
        if ($articleDate >= $cutoffDate) {
            return "$articleDate 00:00:00";
        }
    }
    
    return false; // Could not parse date or article is too old
}

// Test cases
$testDates = [
    // Recent dates (should be included)
    date('Y-m-d'), // Today
    date('Y-m-d', strtotime('-1 day')), // Yesterday
    date('Y-m-d', strtotime('-5 days')), // 5 days ago
    date('Y-m-d', strtotime('-9 days')), // 9 days ago
    date('Y-m-d', strtotime('-10 days')), // Exactly 10 days ago
    
    // Old dates (should be excluded)
    date('Y-m-d', strtotime('-11 days')), // 11 days ago
    date('Y-m-d', strtotime('-20 days')), // 20 days ago
    date('Y-m-d', strtotime('-1 month')), // 1 month ago
    
    // Different formats
    date('F j, Y'), // June 20, 2025
    date('d/m/Y'), // 20/06/2025
    date('m/d/Y'), // 06/20/2025
    date('Y-m-d H:i:s'), // 2025-06-20 10:30:00
    
    // Edge cases
    '',
    'invalid date',
    '2025-13-45', // Invalid month/day
];

echo "Testing various date formats:\n";
echo "-----------------------------\n";

foreach ($testDates as $testDate) {
    $result = parseArticleDate($testDate);
    $status = $result ? "✓ INCLUDED: $result" : "✗ EXCLUDED (too old or invalid)";
    echo sprintf("%-25s -> %s\n", $testDate, $status);
}

echo "\nDate Range Summary:\n";
echo "-------------------\n";
echo "Articles will be included if published between:\n";
echo "From: $cutoffDate 00:00:00\n";
echo "To:   " . date('Y-m-d') . " 23:59:59\n";
echo "\nTotal days: $daysToScrape\n";

// Test with some real-world examples
echo "\nReal-world examples:\n";
echo "--------------------\n";

$realExamples = [
    '2025-01-15 14:30:00' => 'January 15, 2025 with time',
    '2025-01-10' => 'January 10, 2025 (date only)',
    'January 15, 2025' => 'Full month name format',
    '15/01/2025' => 'DD/MM/YYYY format',
    '01/15/2025' => 'MM/DD/YYYY format',
    '2024-12-25' => 'Christmas 2024 (should be excluded)',
    '2024-06-20' => 'June 2024 (should be excluded)',
];

foreach ($realExamples as $date => $description) {
    $result = parseArticleDate($date);
    $status = $result ? "✓ INCLUDED: $result" : "✗ EXCLUDED";
    echo sprintf("%-25s (%s) -> %s\n", $date, $description, $status);
}

echo "\nTest completed!\n";
?> 