<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

    // Validate data
    if (!$article_id || !$name || !$email || !$comment) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        // Insert comment into database
        $query = "INSERT INTO comments (article_id, name, email, comment) VALUES (:article_id, :name, :email, :comment)";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Your comment has been submitted and is awaiting approval.";
        } else {
            $_SESSION['error'] = "There was an error submitting your comment.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    // Redirect back to the article page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    // If not a POST request, redirect to homepage
    header('Location: index.php');
    exit;
}
?> 