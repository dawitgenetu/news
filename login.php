<?php
session_start();
require_once 'includes/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <i class="fa-solid fa-newspaper text-6xl text-blue-700"></i>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full animate-pulse"></div>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Welcome Back
            </h2>
            <p class="text-gray-600">
                Sign in to your Borkena News account
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           required 
                           class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition-colors"
                           placeholder="Enter your email">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           required 
                           class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm transition-colors"
                           placeholder="Enter your password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" 
                           name="remember-me" 
                           type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors hover:shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Sign in
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? 
                <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                    Create one here
                </a>
            </p>
        </div>

        <div class="mt-8">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">Or continue with</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fab fa-google text-blue-600 mr-2"></i>
                    Google
                </button>
                <button class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
                    Facebook
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 