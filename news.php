<?php
include 'db.php';
if (!isset($_GET['slug'])) {
    die("No news selected.");
}
setlocale(LC_TIME, 'id_ID.UTF-8');
$currentSlug = $_GET['slug'];
$slug = $_GET['slug'];

// Get the news by slug
$stmt = $conn->prepare("
    SELECT n.*, c.name AS category_name
    FROM news n
    JOIN categories c ON n.category_id = c.id
    WHERE n.slug = ?
    LIMIT 1
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("News not found.");
}

$news = $result->fetch_assoc();
$catRes = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$newsRes = $conn->query("SELECT n.*, c.name AS category_name, nc.view_count 
                         FROM news n
                         JOIN categories c ON n.category_id = c.id
                            LEFT JOIN newscounter nc ON n.id = nc.news_id
                         WHERE n.is_published = 1
                         ORDER BY n.created_at DESC
                         LIMIT 10");

$newsId = (int)$news['id'];
$cookieName = "viewed_news_$newsId";

// If this browser hasn’t viewed it recently
if (!isset($_COOKIE[$cookieName])) {
    // Update DB
    $newscounter = $conn->query("SELECT * FROM `newscounter` WHERE news_id = {$news['id']}");
if ($newscounter->num_rows > 0) {
    $conn->query("UPDATE `newscounter` SET view_count = view_count + 1 WHERE news_id = {$news['id']}");
} else {
    $conn->query("INSERT INTO `newscounter` (news_id, view_count) VALUES ({$news['id']}, 1)");
}
    // Save a cookie in the browser for 1 hour
    setcookie($cookieName, '1', time() + 3600, "/");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?> - BeritaKu</title>

    <script src="./tailwind.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#164e63',
                        secondary: '#475569',
                        accent: '#a16207',
                        'light-cyan': '#ecfeff',
                        'card-bg': '#ecfeff',
                    }
                }
            }
        }
    </script>
    <style>
        .font-space {
            font-family: 'Space Grotesk', sans-serif;
        }

        .font-dm {
            font-family: 'DM Sans', sans-serif;
        }

        .article-content p {
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .article-content h2 {
            margin: 2rem 0 1rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .article-content h3 {
            margin: 1.5rem 0 0.75rem 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .pull-quote {
            border-left: 4px solid #10b981;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            font-size: 1.125rem;
            color: #374151;
        }
    </style>
    <style>
        .news-card {
            transition: all 0.3s ease;
        }

        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .category-tag {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .read-more-btn {
            transition: all 0.2s ease;
        }

        .read-more-btn:hover {
            background-color: #0f3a47;
        }

        .navbar-item {
            transition: color 0.2s ease;
        }

        .navbar-item:hover {
            color: #164e63;
        }

        .navbar-item.active {
            color: #164e63;
            font-weight: 600;
        }
    </style>
        <style>
        .icon-eye {
            width: 1em;
            height: 1em;
            vertical-align: middle;
            fill: currentColor;
        }

        .views {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.95rem;
            margin-left: 20px;
        }
    </style>
</head>

<body class="bg-white text-secondary font-opensans">
<header class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <a href="index.php">
                    <h1 class="text-2xl font-montserrat font-black text-primary">BeritaKu</h1>
                </a>
            </div>

            <nav class="hidden md:flex space-x-8">
                <?php while ($cat = $catRes->fetch_assoc()): ?>
                    <?php if ($cat['slug'] == "hot") continue; ?>
                    <a href="category.php?slug=<?= urlencode($cat['slug']) ?>"
                       class="navbar-item <?= $currentSlug === $cat['slug'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endwhile; ?>
            </nav>

            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- If logged in -->
                    <a href="dashboard.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                        Dashboard
                    </a>
                <?php else: ?>
                    <!-- If not logged in -->
                    <a href="login.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                        Login
                    </a>
                <?php endif; ?>
            </div>

            <button class="md:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>
</header>
    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-gray-100">
        <div class="px-4 py-2 space-y-2">
            <?php
            $catRes->data_seek(0); // Reset the result set pointer to the beginning
            while ($cat = $catRes->fetch_assoc()): ?>
                <?php if ($cat['slug'] == "hot") continue; ?>
                <a href="category.php?slug=<?= urlencode($cat['slug']) ?>"
                    class="block py-2 navbar-item <?= $currentSlug === $cat['slug'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>


    <!-- Article Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Article Content -->
            <article class="lg:col-span-2">
                <!-- Article Header -->
                <div class="bg-card-bg rounded-xl shadow-sm p-8 mb-6">
                    <!-- Category Tag -->
                    <div class="mb-4">
                        <span class="inline-block bg-emerald-100 text-emerald-800 text-sm font-medium px-3 py-1 rounded-full">
                            <?= htmlspecialchars($news['category_name']) ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl md:text-5xl font-space font-bold text-gray-900 mb-6 leading-tight">
                        <?= htmlspecialchars($news['title']) ?>
                    </h1>

                    <!-- Meta -->
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Dipublikasikan</p>
                        <p class="font-medium text-gray-900"><?php $bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$time = strtotime($news['created_at']);
$day = date('d', $time);
$month = $bulan[(int)date('m', $time)];
$year = date('Y', $time);

echo "$day $month $year";?></p>
                    </div>

                    <!-- Social Sharing -->
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500">Bagikan:</span>
                        <button class="flex items-center space-x-2 text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                            <span class="text-sm">Facebook</span>
                        </button>
                        <button class="flex items-center space-x-2 text-blue-400 hover:text-blue-500 transition-colors">
                            <i class="fab fa-twitter"></i>
                            <span class="text-sm">Twitter</span>
                        </button>
                        <button class="flex items-center space-x-2 text-blue-700 hover:text-blue-800 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                            <span class="text-sm">LinkedIn</span>
                        </button>
                        <button class="flex items-center space-x-2 text-gray-600 hover:text-gray-700 transition-colors">
                            <i class="fas fa-link"></i>
                            <span class="text-sm">Salin Tautan</span>
                        </button>
                    </div>
                </div>
<div class="bg-card-bg p-6">
<img src="<?= htmlspecialchars($news['cover_image']) ?>"
                    alt="<?= htmlspecialchars($news['title']) ?>"
                    class="w-full h-64 md:h-96 object-cover mb-6">


                    <div class="article-content text-gray-700 text-lg leading-relaxed">
    <?= nl2br(htmlspecialchars($news['content'])) ?>
</div>
</div>
                



            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Related Articles -->
                <div class="bg-card-bg rounded-xl p-6 shadow-md mb-6">
                    <h3 class="font-space font-semibold text-gray-900 text-lg mb-4">Berita Lainnya</h3>
                    <div class="space-y-4">
                        <?php $newsRes->data_seek(0); // Reset result pointer 
                        ?>
                        <?php while ($rel = $newsRes->fetch_assoc()): ?>
                            <article class="flex space-x-3">
                                <img src="<?= htmlspecialchars($rel['cover_image']) ?>"
                                    alt="<?= htmlspecialchars($rel['title']) ?>"
                                    class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                                <div>
                                    <a href="news.php?slug=<?= urlencode($rel['slug']) ?>">
                                        <h4 class="font-medium text-gray-900 text-sm leading-tight mb-1 hover:text-emerald-600 cursor-pointer">
                                            <?= htmlspecialchars($rel['title']) ?>
                                        </h4>
                                    </a>
                                    <p class="text-xs text-gray-500"><?= date("F j, Y", strtotime($rel['created_at'])) ?></p>
                                    <span class="views">
                                        <!-- inline eye SVG -->
                                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true" focusable="false" width="16" height="16">
                                            <title>Views</title>
                                            <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>

                                        <?php echo (int)$rel['view_count']; ?>
                                    </span>
                                </div>
                            </article>
                        <?php endwhile; ?>

                    </div>
                </div>

                <!-- Trending Topics -->
                <!-- <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="font-space font-semibold text-gray-900 text-lg mb-4">Trending Topics</h3>
                    <div class="space-y-3">
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-emerald-600 transition-colors">
                            <span class="text-sm">#ArtificialIntelligence</span>
                            <span class="text-xs text-gray-500">1.2k posts</span>
                        </a>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-emerald-600 transition-colors">
                            <span class="text-sm">#BlockchainTech</span>
                            <span class="text-xs text-gray-500">856 posts</span>
                        </a>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-emerald-600 transition-colors">
                            <span class="text-sm">#DigitalTransformation</span>
                            <span class="text-xs text-gray-500">743 posts</span>
                        </a>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-emerald-600 transition-colors">
                            <span class="text-sm">#SmartManufacturing</span>
                            <span class="text-xs text-gray-500">621 posts</span>
                        </a>
                        <a href="#" class="flex items-center justify-between text-gray-700 hover:text-emerald-600 transition-colors">
                            <span class="text-sm">#TechInnovation</span>
                            <span class="text-xs text-gray-500">589 posts</span>
                        </a>
                    </div>
                </div> -->

                <!-- Newsletter Signup -->
                <!-- <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl shadow-sm p-6 text-white">
                    <h3 class="font-space font-semibold text-lg mb-2">Stay Updated</h3>
                    <p class="text-emerald-100 text-sm mb-4">Get the latest tech news and insights delivered to your inbox.</p>
                    <div class="space-y-3">
                        <input type="email" placeholder="Enter your email" class="w-full px-4 py-2 rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-emerald-300">
                        <button class="w-full bg-white text-emerald-600 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">Subscribe</button>
                    </div>
                </div> -->
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-montserrat font-black mb-4">BeritaKu</h3>
                    <p class="text-sm opacity-90">Berita Terkini yang bisa diakses kapanpun</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kategori</h4>
                    <ul class="space-y-2 text-sm opacity-90">
                        <?php $catRes->data_seek(0); // Reset the result set pointer to the beginning ?>
                        <?php while ($cat = $catRes->fetch_assoc()): ?>
                        <?php if ($cat['slug'] == "hot") continue; ?>

                        <li><a href="category.php?slug=<?= urlencode($cat['slug']) ?>"
                            class="hover:opacity-100">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a></li>
                    <?php endwhile; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:opacity-100 opacity-90">Twitter</a>
                        <a href="#" class="hover:opacity-100 opacity-90">Facebook</a>
                        <a href="#" class="hover:opacity-100 opacity-90">LinkedIn</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white border-opacity-20 mt-8 pt-8 text-center text-sm opacity-90">
                <p>&copy; 2025 BeritaKu. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
        // Social sharing functionality
        document.addEventListener('DOMContentLoaded', function() {
            const shareButtons = document.querySelectorAll('[class*="fab fa-"]').forEach(button => {
                button.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    const platform = this.querySelector('i').classList[1].split('-')[1];
                    const url = encodeURIComponent(window.location.href);
                    const title = encodeURIComponent(document.title);

                    let shareUrl = '';
                    switch (platform) {
                        case 'facebook':
                            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                            break;
                        case 'twitter':
                            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                            break;
                        case 'linkedin':
                            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                            break;
                    }

                    if (shareUrl) {
                        window.open(shareUrl, '_blank', 'width=600,height=400');
                    }
                });
            });
        });
    </script>
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('button[class*="md:hidden"]');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Category filtering
        const filterButtons = document.querySelectorAll('.category-filter-btn');
        const newsArticles = document.querySelectorAll('[data-category]');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const category = button.dataset.category;

                // Update active button
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-primary', 'text-white');
                    btn.classList.add('bg-gray-100', 'text-secondary');
                });
                button.classList.add('active', 'bg-primary', 'text-white');
                button.classList.remove('bg-gray-100', 'text-secondary');

                // Filter articles
                newsArticles.forEach(article => {
                    if (category === 'all' || article.dataset.category === category) {
                        article.style.display = 'block';
                        setTimeout(() => {
                            article.style.opacity = '1';
                            article.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        article.style.opacity = '0';
                        article.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            article.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Navigation active state
        const navItems = document.querySelectorAll('.navbar-item');
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Read more functionality
        const readMoreButtons = document.querySelectorAll('.read-more-btn');
        readMoreButtons.forEach(button => {

        });

        // Load more articles
        const loadMoreButton = document.getElementById('load-more');
        loadMoreButton.addEventListener('click', () => {
            // Simulate loading more articles
            loadMoreButton.textContent = 'Loading...';
            loadMoreButton.disabled = true;

            setTimeout(() => {
                loadMoreButton.textContent = 'Load More Articles';
                loadMoreButton.disabled = false;
                alert('More articles would be loaded here via API call.');
            }, 1500);
        });

        // Smooth scroll for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add loading animation to news cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Initially hide articles for animation
        newsArticles.forEach(article => {
            article.style.opacity = '0';
            article.style.transform = 'translateY(20px)';
            article.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(article);
        });
    </script>
</body>

</html>