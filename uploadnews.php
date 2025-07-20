<?php
session_start(); // Ensure session is started before any output
// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/article_functions.php';

// Fetch categories for dropdown
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $author = trim($_POST['author'] ?? '');
    $published_at = trim($_POST['published_at'] ?? date('Y-m-d H:i:s'));
    $image_url = '';
    $local_image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads/images/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filePath = $uploadDir . $filename;
        if (move_uploaded_file($tmp, $filePath)) {
            $local_image_path = 'uploads/images/' . $filename;
            $image_url = $local_image_path;
        } else {
            $error = 'Image upload failed.';
        }
    }

    if (!$error) {
        try {
            $stmt = $conn->prepare("INSERT INTO articles (title, slug, content, excerpt, image_url, local_image_path, category_id, author, status, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, NOW())");
            $stmt->bind_param("ssssssis", $title, $slug, $content, $excerpt, $image_url, $local_image_path, $category_id, $author, $published_at);
            if ($stmt->execute()) {
                $success = 'News article uploaded successfully!';
            } else {
                $error = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 text-blue-700">Upload News Article</h1>
    <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow"> <?php echo $success; ?> </div>
    <?php elseif ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg shadow"> <?php echo $error; ?> </div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-8 rounded-xl shadow-lg">
        <div>
            <label class="block font-semibold mb-2">Title</label>
            <input type="text" name="title" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block font-semibold mb-2">Slug</label>
            <input type="text" name="slug" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block font-semibold mb-2">Content</label>
            <textarea name="content" rows="8" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div>
            <label class="block font-semibold mb-2">Excerpt</label>
            <textarea name="excerpt" rows="2" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div>
            <label class="block font-semibold mb-2">Category</label>
            <select name="category_id" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-2">Author</label>
            <input type="text" name="author" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block font-semibold mb-2">Published Date</label>
            <input type="datetime-local" name="published_at" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block font-semibold mb-2">Image</label>
            <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full py-3 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition">Upload News</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 