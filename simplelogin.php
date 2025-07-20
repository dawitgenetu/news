<?php
session_start();

// If already logged in, redirect to uploadnews.php
if (isset($_SESSION['user_id'])) {
    header('Location: uploadnews.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === 'dawitnews' && $password === 'default') {
        $_SESSION['user_id'] = 'dawitnews';
        header('Location: uploadnews.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 text-center">News Uploader Login</h1>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block font-semibold mb-2">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full py-3 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition">Login</button>
        </form>
    </div>
</div> 