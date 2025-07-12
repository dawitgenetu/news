<?php
require_once 'includes/config.php';

try {
    $db->exec("DELETE FROM articles");
    // $db->exec("TRUNCATE TABLE articles"); // Uncomment to reset auto-increment
    echo "All articles deleted successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 