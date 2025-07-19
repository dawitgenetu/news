<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // Here you would typically send an email or save to database
        // For now, we'll just show a success message
        $success = 'Thank you for your message. We will get back to you soon.';
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Contact Us</h1>
        <p class="text-lg text-gray-600">Get in touch with our team for any inquiries or feedback</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
            
            <div class="space-y-6">
                <div class="flex items-start space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-map-marker-alt text-blue-700"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Address</h3>
                        <p class="text-gray-600">Bole, Addis Ababa, Ethiopia</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-phone text-blue-700"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Phone</h3>
                        <p class="text-gray-600">+251 11 123 4567</p>
                        <p class="text-gray-600">+251 11 123 4568</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-envelope text-blue-700"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Email</h3>
                        <p class="text-gray-600">info@borkena.com</p>
                        <p class="text-gray-600">support@borkena.com</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-clock text-blue-700"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Working Hours</h3>
                        <p class="text-gray-600">Monday - Friday: 8:00 AM - 6:00 PM</p>
                        <p class="text-gray-600">Saturday: 9:00 AM - 2:00 PM</p>
                    </div>
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="mt-8">
                <h3 class="font-semibold text-gray-900 mb-4">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="#" class="bg-blue-100 p-3 rounded-full text-blue-700 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="bg-blue-100 p-3 rounded-full text-blue-700 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="bg-blue-100 p-3 rounded-full text-blue-700 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="bg-blue-100 p-3 rounded-full text-blue-700 hover:bg-blue-200 transition-colors">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="process-contact.php" method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" id="subject" name="subject" required
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="message" name="message" rows="6" required
                              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-700 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-800 transition-colors">
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Map Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Location</h2>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.520510588731!2d38.7579!3d9.0222!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDEnMjAuMCJOIDM4wrA0NScyOC40IkU!5e0!3m2!1sen!2set!4v1620000000000!5m2!1sen!2set" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 