<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Borkena News' : 'Borkena News'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            scroll-behavior: smooth;
        }
        
        /* Improve font for article content */
        .article-content, .prose, .prose-lg, .article-body, article, .article-text {
            font-family: 'Poppins', 'Inter', Arial, sans-serif !important;
            font-size: 1.15rem;
            line-height: 1.8;
            color: #222;
        }
        
        .fade-in { 
            animation: fadeIn 0.6s ease-out; 
        }
        
        .slide-down {
            animation: slideDown 0.4s ease-out;
        }
        
        .scale-in {
            animation: scaleIn 0.3s ease-out;
        }
        
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .dropdown:hover .dropdown-menu { 
            display: block; 
            animation: slideDown 0.2s ease-out;
        }
        
        .marquee {
            animation: marquee 20s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #dc2626;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        .search-focus {
            transition: all 0.3s ease;
        }
        
        .search-focus:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Breaking News Bar -->
    <div class="gradient-bg text-white py-3 relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="font-bold text-sm bg-white text-red-700 px-3 py-1 rounded-full animate-pulse">
                        BREAKING
                    </span>
                    <div class="overflow-hidden flex-1">
                        <div class="marquee whitespace-nowrap text-sm">
                            🚨 Latest updates from Ethiopia and around the world. Stay informed with Borkena News. 
                            📰 Breaking: Major developments in regional politics. 
                            🌍 International news and analysis. 
                            📊 Economic updates and market insights.
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-gray-200 transition-colors hover:scale-110 transform duration-200">
                        <i class="fa-solid fa-rss"></i>
                    </a>
                    <button id="theme-toggle" class="hover:text-gray-200 transition-colors hover:scale-110 transform duration-200">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="bg-gray-800 text-white py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center hover:text-red-400 transition-colors">
                        <i class="fa-solid fa-clock mr-2"></i>
                        <span><?php echo date('l, F j, Y'); ?></span>
                    </div>
                    <div class="flex items-center hover:text-red-400 transition-colors">
                        <i class="fa-solid fa-location-dot mr-2"></i>
                        <span>Addis Ababa, Ethiopia</span>
                    </div>
                    <div class="flex items-center hover:text-red-400 transition-colors">
                        <i class="fa-solid fa-temperature-high mr-2"></i>
                        <span>25°C</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-red-400 transition-colors hover:scale-110 transform duration-200"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-red-400 transition-colors hover:scale-110 transform duration-200"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="hover:text-red-400 transition-colors hover:scale-110 transform duration-200"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-red-400 transition-colors hover:scale-110 transform duration-200"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="hover:text-red-400 transition-colors hover:scale-110 transform duration-200"><i class="fa-brands fa-telegram"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="glass-effect shadow-lg sticky top-0 z-50 border-b border-gray-100">
        <div class="container mx-auto px-4">
            <!-- Logo and Search -->
            <div class="py-6 flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-3 hover-lift">
                    <div class="relative">
                        <i class="fa-solid fa-newspaper text-4xl text-red-700"></i>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                    </div>
                    <div>
                        <span class="text-3xl font-bold text-red-700 font-['Poppins']">Borkena</span>
                        <span class="text-xl font-medium text-gray-600 block -mt-1">News</span>
                    </div>
                </a>
                
                <div class="hidden lg:flex items-center space-x-4 flex-1 max-w-2xl mx-8">
                    <form action="search.php" method="GET" class="w-full relative">
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fa-solid fa-search"></i>
                            </span>
                            <input type="text" name="q" aria-label="Search" autocomplete="off"
                                   placeholder="Search news, topics, or categories (e.g. Politics, Business, Sports, Technology)..."
                                   class="w-full pl-12 pr-6 py-3 rounded-full border border-gray-300 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 search-focus bg-white shadow-sm relative z-20 text-gray-900 placeholder-gray-400"
                                   oninput="liveSuggest(this, 'search-suggestions')" onkeydown="suggestionKeydown(event, 'search-suggestions', this)" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="search-suggestions">
                            <button type="submit" class="absolute right-4 top-3 text-gray-400 hover:text-red-700 transition-colors z-10">
                                <i class="fa-solid fa-search"></i>
                            </button>
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-red-500 to-red-600 opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                            <!-- Suggestions Dropdown -->
                            <ul id="search-suggestions" role="listbox" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-30 text-gray-900">
                                <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Politics</li>
                                <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Business</li>
                                <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Sports</li>
                                <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Technology</li>
                            </ul>
                        </div>
                    </form>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button class="lg:hidden text-gray-600 hover:text-red-700 transition-colors" id="mobile-menu-button">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
<?php if (isset($_SESSION['user_id'])): ?>
                    <div class="relative group">
                        <button class="flex items-center bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 focus:outline-none">
                            <i class="fa-solid fa-user-circle mr-2 text-red-700"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-3 border border-gray-100 hidden group-hover:block z-50">
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fa-solid fa-user mr-2"></i>Profile
                            </a>
                            <a href="logout.php" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fa-solid fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
<?php else: ?>
                    <a href="login.php" class="hidden md:block bg-red-700 text-white px-6 py-2 rounded-full hover:bg-red-800 transition-colors hover-lift">
                        <i class="fa-solid fa-user mr-2"></i>
                        Login
                    </a>
<?php endif; ?>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hidden lg:block border-t border-gray-100">
                <ul class="flex space-x-8 py-4">
                    <li class="dropdown relative group">
                        <a href="index.php" class="nav-link text-gray-700 hover:text-red-700 font-medium flex items-center py-2">
                            <i class="fa-solid fa-home mr-2"></i>
                            Home
                            <i class="fa-solid fa-chevron-down ml-1 text-xs transition-transform group-hover:rotate-180"></i>
                        </a>
                        <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-3 border border-gray-100">
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fa-solid fa-fire mr-2 text-red-500"></i>
                                Latest News
                            </a>
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fa-solid fa-star mr-2 text-yellow-500"></i>
                                Featured Stories
                            </a>
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fa-solid fa-chart-line mr-2 text-green-500"></i>
                                Popular Articles
                            </a>
                        </div>
                    </li>
                    <li><a href="news.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-newspaper mr-2"></i>News</a></li>
                    <li><a href="politics.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-landmark mr-2"></i>Politics</a></li>
                    <li><a href="business.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-briefcase mr-2"></i>Business</a></li>
                    <li><a href="sport.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-futbol mr-2"></i>Sports</a></li>
                    <li><a href="opinion.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-comments mr-2"></i>Opinion</a></li>
                    <li><a href="video.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-video mr-2"></i>Video</a></li>
                    <li><a href="entertainment.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-film mr-2"></i>Entertainment</a></li>
                    <li><a href="business-listings.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-building mr-2"></i>Business Listings</a></li>
                    <li><a href="contact.php" class="nav-link text-gray-700 hover:text-red-700 font-medium py-2 flex items-center">
                        <i class="fa-solid fa-envelope mr-2"></i>Contact</a></li>
                </ul>
            </nav>

            <!-- Mobile Menu -->
            <div class="lg:hidden fixed inset-0 z-50 hidden" id="mobile-menu-overlay">
                <div class="absolute inset-0 bg-black bg-opacity-50" id="mobile-menu-backdrop"></div>
                <div class="absolute left-0 top-0 h-full w-80 bg-white shadow-2xl mobile-menu" id="mobile-menu">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-newspaper text-2xl text-red-700"></i>
                                <span class="text-xl font-bold text-red-700">Borkena News</span>
                            </div>
                            <button id="mobile-menu-close" class="text-gray-500 hover:text-red-700">
                                <i class="fa-solid fa-times text-2xl"></i>
                            </button>
                        </div>
                        
                        <form action="search.php" method="GET" class="mb-6 relative">
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <i class="fa-solid fa-search"></i>
                                </span>
                                <input type="text" name="q" aria-label="Search" autocomplete="off"
                                       placeholder="Search news, topics, or categories (e.g. Politics, Business, Sports, Technology)..."
                                       class="w-full pl-12 pr-6 py-3 rounded-full border border-gray-300 focus:outline-none focus:border-red-500 bg-gray-50 relative z-20 text-gray-900 placeholder-gray-400"
                                       oninput="liveSuggest(this, 'search-suggestions-mobile')" onkeydown="suggestionKeydown(event, 'search-suggestions-mobile', this)" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="search-suggestions-mobile">
                                <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-red-700 transition-colors z-10">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                                <!-- Suggestions Dropdown -->
                                <ul id="search-suggestions-mobile" role="listbox" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-30 text-gray-900">
                                    <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Politics</li>
                                    <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Business</li>
                                    <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Sports</li>
                                    <li class="px-6 py-3 hover:bg-red-50 cursor-pointer">Technology</li>
                                </ul>
                            </div>
                        </form>
                        
                        <ul class="space-y-2">
                            <li><a href="index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-home mr-3 w-5"></i>Home</a></li>
                            <li><a href="news.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-newspaper mr-3 w-5"></i>News</a></li>
                            <li><a href="politics.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-landmark mr-3 w-5"></i>Politics</a></li>
                            <li><a href="business.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-briefcase mr-3 w-5"></i>Business</a></li>
                            <li><a href="sport.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-futbol mr-3 w-5"></i>Sports</a></li>
                            <li><a href="opinion.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-comments mr-3 w-5"></i>Opinion</a></li>
                            <li><a href="video.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-video mr-3 w-5"></i>Video</a></li>
                            <li><a href="entertainment.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-film mr-3 w-5"></i>Entertainment</a></li>
                            <li><a href="business-listings.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-building mr-3 w-5"></i>Business Listings</a></li>
                            <li><a href="contact.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-envelope mr-3 w-5"></i>Contact</a></li>
                        </ul>
                        
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <a href="login.php" class="w-full bg-red-700 text-white px-6 py-3 rounded-full hover:bg-red-800 transition-colors block text-center">
                                <i class="fa-solid fa-user mr-2"></i>
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');
        const mobileMenu = document.getElementById('mobile-menu');

        function openMobileMenu() {
            mobileMenuOverlay.classList.remove('hidden');
            setTimeout(() => {
                mobileMenu.classList.add('active');
            }, 10);
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            setTimeout(() => {
                mobileMenuOverlay.classList.add('hidden');
            }, 300);
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        mobileMenuClose.addEventListener('click', closeMobileMenu);
        mobileMenuBackdrop.addEventListener('click', closeMobileMenu);

        // Theme toggle with smooth transition
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const icon = themeToggle.querySelector('i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
            
            // Add smooth transition effect
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        });

        // Add scroll effect to header
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('shadow-xl');
                header.classList.remove('shadow-lg');
            } else {
                header.classList.remove('shadow-xl');
                header.classList.add('shadow-lg');
            }
        });

        // Add fade-in animation to page content
        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.querySelector('main');
            if (mainContent) {
                mainContent.classList.add('fade-in');
            }
        });

        // Live search suggestions (static for now)
        const SUGGESTION_KEYWORDS = [
            'Politics', 'Business', 'Sports', 'Technology', 'Society', 'Opinion', 'Video', 'Entertainment', 'Health', 'Education', 'International', 'Ethiopia', 'Africa', 'Economy', 'Market', 'Science', 'Culture', 'Breaking News', 'Trending', 'COVID-19', 'Elections'
        ];

        function highlightMatch(text, query) {
            if (!query) return text;
            const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
            return text.replace(regex, '<span class="bg-yellow-200">$1</span>');
        }

        function liveSuggest(input, suggestionId) {
            const suggestions = document.getElementById(suggestionId);
            const query = input.value.trim();
            suggestions.innerHTML = '';
            if (query.length === 0) {
                suggestions.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
                return;
            }
            const matches = SUGGESTION_KEYWORDS.filter(k => k.toLowerCase().includes(query.toLowerCase()));
            if (matches.length === 0) {
                suggestions.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
                return;
            }
            matches.forEach((match, idx) => {
                const li = document.createElement('li');
                li.className = 'px-6 py-3 hover:bg-red-50 cursor-pointer';
                li.setAttribute('role', 'option');
                li.setAttribute('tabindex', '-1');
                li.innerHTML = highlightMatch(match, query);
                li.onclick = function() {
                    input.value = match;
                    suggestions.classList.add('hidden');
                    input.setAttribute('aria-expanded', 'false');
                    input.form && input.form.submit();
                };
                suggestions.appendChild(li);
            });
            suggestions.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
        }

        // Keyboard navigation for suggestions
        function suggestionKeydown(e, suggestionId, input) {
            const suggestions = document.getElementById(suggestionId);
            const items = suggestions ? suggestions.querySelectorAll('li') : [];
            if (!items.length || suggestions.classList.contains('hidden')) return;
            let idx = Array.from(items).findIndex(li => li === document.activeElement);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (idx < items.length - 1) {
                    items[idx + 1].focus();
                } else {
                    items[0].focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (idx > 0) {
                    items[idx - 1].focus();
                } else {
                    items[items.length - 1].focus();
                }
            } else if (e.key === 'Enter') {
                // Only prevent default if a suggestion is focused
                if (document.activeElement.parentElement === suggestions) {
                    e.preventDefault();
                    document.activeElement.click();
                } // else, allow form to submit
            } else if (e.key === 'Escape') {
                suggestions.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
            }
        }

        // Hide suggestions on click outside
        document.addEventListener('click', function(e) {
            const desktopInput = document.querySelector('input[name="q"]');
            const desktopSuggestions = document.getElementById('search-suggestions');
            if (desktopSuggestions && !desktopSuggestions.contains(e.target) && desktopInput !== e.target) {
                desktopSuggestions.classList.add('hidden');
                desktopInput.setAttribute('aria-expanded', 'false');
            }
            const mobileInput = document.querySelector('#mobile-menu input[name="q"]');
            const mobileSuggestions = document.getElementById('search-suggestions-mobile');
            if (mobileSuggestions && !mobileSuggestions.contains(e.target) && mobileInput !== e.target) {
                mobileSuggestions.classList.add('hidden');
                mobileInput.setAttribute('aria-expanded', 'false');
            }
        });
    </script>

    <main class="container mx-auto px-4 py-8">
</body>
</html> 