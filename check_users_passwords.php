<?php
require_once 'includes/db_config.php';

function is_hashed($password) {
    // bcrypt hashes start with $2y$ or $2a$
    return (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0);
}

$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $updated = 0;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $password = $row['password'];
        if (!is_hashed($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $id);
            if ($update->execute()) {
                $updated++;
                echo "Updated user ID $id password to hashed.<br>\n";
            } else {
                echo "Failed to update user ID $id.<br>\n";
            }
        } else {
            echo "User ID $id password already hashed.<br>\n";
        }
    }
    echo "<br>Done. $updated user(s) updated.";
} else {
    echo "No users found or query failed.";
}
?> 