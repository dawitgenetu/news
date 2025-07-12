<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Fetch business listings
$stmt = $db->prepare("
    SELECT b.*, c.name as category_name 
    FROM business_listings b 
    JOIN business_categories c ON b.category_id = c.id 
    ORDER BY b.created_at DESC
");
$stmt->execute();
$business_listings = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Business Listings</h1>
        <p class="text-lg text-gray-600">Find and connect with businesses in Ethiopia</p>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <input type="text" 
                       placeholder="Search businesses..." 
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                <option value="">All Categories</option>
                <option value="restaurants">Restaurants</option>
                <option value="hotels">Hotels</option>
                <option value="retail">Retail</option>
                <option value="services">Services</option>
            </select>
            <select class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                <option value="">All Locations</option>
                <option value="addis-ababa">Addis Ababa</option>
                <option value="dire-dawa">Dire Dawa</option>
                <option value="bahir-dar">Bahir Dar</option>
                <option value="hawassa">Hawassa</option>
            </select>
        </div>
    </div>

    <!-- Featured Businesses -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Businesses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php for ($i = 0; $i < min(3, count($business_listings)); $i++): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="<?php echo htmlspecialchars($business_listings[$i]['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($business_listings[$i]['name']); ?>"
                     class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            <?php echo htmlspecialchars($business_listings[$i]['category_name']); ?>
                        </span>
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="ml-1 text-gray-700"><?php echo number_format($business_listings[$i]['rating'], 1); ?></span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($business_listings[$i]['name']); ?>
                    </h3>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars($business_listings[$i]['description']); ?>
                    </p>
                    <div class="flex items-center space-x-4 text-gray-500 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span><?php echo htmlspecialchars($business_listings[$i]['location']); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span><?php echo htmlspecialchars($business_listings[$i]['phone']); ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="business-details.php?id=<?php echo $business_listings[$i]['id']; ?>" 
                           class="text-red-700 hover:text-red-800 font-medium">
                            View Details →
                        </a>
                        <button class="text-gray-500 hover:text-red-700">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- All Businesses -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">All Businesses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php for ($i = 3; $i < count($business_listings); $i++): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="<?php echo htmlspecialchars($business_listings[$i]['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($business_listings[$i]['name']); ?>"
                     class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            <?php echo htmlspecialchars($business_listings[$i]['category_name']); ?>
                        </span>
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="ml-1 text-gray-700"><?php echo number_format($business_listings[$i]['rating'], 1); ?></span>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($business_listings[$i]['name']); ?>
                    </h3>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars(substr($business_listings[$i]['description'], 0, 100)) . '...'; ?>
                    </p>
                    <div class="flex items-center space-x-4 text-gray-500 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span><?php echo htmlspecialchars($business_listings[$i]['location']); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span><?php echo htmlspecialchars($business_listings[$i]['phone']); ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="business-details.php?id=<?php echo $business_listings[$i]['id']; ?>" 
                           class="text-red-700 hover:text-red-800 font-medium">
                            View Details →
                        </a>
                        <button class="text-gray-500 hover:text-red-700">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Add Business Button -->
    <div class="fixed bottom-8 right-8">
        <a href="add-business.php" 
           class="bg-red-700 text-white px-6 py-3 rounded-full shadow-lg hover:bg-red-800 transition-colors flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Add Your Business
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 