<?php
// run_scraper_10days.php
// Simple script to run the Borkena scraper with 10-day date filtering

echo "Borkena News Scraper - 10 Day Filter\n";
echo "====================================\n";
echo "This script will scrape articles from the last 10 days only.\n\n";

// Show date range
$cutoffDate = date('Y-m-d', strtotime('-10 days'));
echo "Date Range:\n";
echo "- From: $cutoffDate\n";
echo "- To: " . date('Y-m-d') . "\n";
echo "- Total days: 10\n\n";

echo "Starting scraper...\n";
echo "==================\n\n";

// Include and run the scraper
include 'borkena_scraper.php';

echo "\n\nScraping completed with 10-day filter!\n";
echo "Check borkena_articles.json for the results.\n";
?> 