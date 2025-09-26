<?php
include 'db.php';

$catRes = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$newsRes = $conn->query("SELECT n.*, c.name AS category_name, nc.view_count 
                         FROM news n
                         JOIN categories c ON n.category_id = c.id
                        JOIN newscounter nc ON nc.news_id = n.id
                         WHERE n.is_published = 1
                         ORDER BY n.created_at DESC
                         ");

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    die('Category not found.');
}

// Get category
$stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    die('Category not found.');
}

// Get news in category
$stmt = $conn->prepare("SELECT n.*, nc.view_count 
    FROM news n
    LEFT JOIN newscounter nc ON nc.news_id = n.id
    WHERE n.category_id = ?
    ORDER BY n.created_at DESC");
$stmt->bind_param("i", $category['id']);
$stmt->execute();
$newsRes = $stmt->get_result();
$stmt->close();

$sidebarRes = $conn->query("
SELECT n.*, c.name AS category_name, nc.view_count
FROM news n
JOIN categories c ON n.category_id = c.id
JOIN newscounter nc ON nc.news_id = n.id
WHERE n.is_published = 1
ORDER BY nc.view_count DESC
LIMIT 5
");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeritaKu</title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet"> -->
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
</head>

<body class="bg-white text-secondary font-opensans">
    <!-- Header -->
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
            <a href="category.php?slug=<?= urlencode($cat['slug']) ?>"
                class="block py-2 navbar-item <?= $currentSlug === $cat['slug'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endwhile; ?>
    </div>
</div>  

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Featured Stories Section with Sidebar -->
        <section class="mb-12">
            <h2 class="text-3xl font-montserrat font-black text-primary mb-8"><?php echo $category["name"] ?></h2>

            <div class="lg:flex lg:gap-8">
                <!-- Main Content - Row Layout -->
                <div class="lg:w-2/3">
                    <?php while ($row = $newsRes->fetch_assoc()): ?>
                        <!-- Row Article 1 -->
                        <article class="news-card bg-card-bg rounded-xl overflow-hidden shadow-md mb-6 flex flex-col sm:flex-row">
                            <div class="sm:w-1/3">
                                <img src="<?= htmlspecialchars($row['cover_image'] ?? '/placeholder.svg?height=200&width=400') ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-full h-48 sm:h-full object-cover">
                            </div>
                            <div class="sm:w-2/3 p-6">
                               

                                <h3 class="text-xl font-montserrat font-black text-primary mt-3 mb-2">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h3>

                                <p class="text-secondary mb-4 text-sm leading-relaxed">
                                    <?= htmlspecialchars(mb_strimwidth($row['content'], 0, 150, "...")) ?>
                                </p>

                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        <?= date("M d, Y H:i", strtotime($row['created_at'])) ?> • By <?= htmlspecialchars($row['author'] ?? 'Admin') ?>
                                        <span class="views">
                                        <!-- inline eye SVG -->
                                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true" focusable="false" width="16" height="16">
                                            <title>Views</title>
                                            <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>

                                        <?php echo (int)$row['view_count']; ?>
                                    </span>
                                    </span>
                                    <a href="news.php?slug=<?= urlencode($row['slug']) ?>"
                                        class="read-more-btn bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>

                        </article>
                    <?php endwhile; ?>
                    <div id="pagination" class="flex justify-center space-x-2 mt-4"></div>
                </div>

                <!-- Right Sidebar -->
                <div class="lg:w-1/3 mt-8 lg:mt-0">
                    <!-- Trending News -->
                    <div class="bg-card-bg rounded-xl p-6 shadow-md mb-6">
                        <h3 class="text-xl font-montserrat font-black text-primary mb-4">Trending Sekarang</h3>
                        <?php while ($row = $sidebarRes->fetch_assoc()): ?>
                            <article class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
                                <div class="flex gap-3">
                                    <img src="<?= htmlspecialchars($row['cover_image'] ?? '/placeholder.svg?height=80&width=80') ?>"
                                        alt="<?= htmlspecialchars($row['title']) ?>"
                                        class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                                    <div class="flex-1">
                                        <span class="category-tag bg-blue-500 text-white px-2 py-1 rounded text-xs">
                                            <?= htmlspecialchars($row['category_name']) ?>
                                        </span>
                                        <h4 class="font-poppins font-bold text-primary text-sm mt-1 mb-1 leading-tight">
                                            <a href="news.php?slug=<?= urlencode($row['slug']) ?>" class="hover:underline">
                                                <?= htmlspecialchars(mb_strimwidth($row['title'], 0, 60, "...")) ?>
                                            </a>
                                        </h4>

                                        <span class="text-xs text-gray-500">
                                            <?= date("M d, Y H:i", strtotime($row['created_at'])) ?>
                                            <span class="views">
                                        <!-- inline eye SVG -->
                                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true" focusable="false" width="16" height="16">
                                            <title>Views</title>
                                            <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>

                                        <?php echo (int)$row['view_count']; ?>
                                    </span>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- Newsletter Signup
                    <div class="bg-primary rounded-xl p-6 text-white">
                        <h3 class="text-xl font-montserrat font-black mb-2">Stay Updated</h3>
                        <p class="text-sm opacity-90 mb-4">Get the latest news delivered to your inbox daily.</p>
                        <form class="space-y-3">
                            <input type="email" placeholder="Enter your email" class="w-full px-4 py-2 rounded-lg text-secondary focus:outline-none focus:ring-2 focus:ring-white">
                            <button type="submit" class="w-full bg-white text-primary px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">Subscribe</button>
                        </form>
                    </div> -->
                </div>
            </div>
        </section>
    </main>

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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(function(){
    var itemsPerPage = 3;
    var $items = $('.lg\\:w-2\\/3 article');
    var totalItems = $items.length;
    var totalPages = Math.ceil(totalItems / itemsPerPage);
    var currentPage = 1;

    function showPage(page){
        if(page < 1) page = 1;
        if(page > totalPages) page = totalPages;
        currentPage = page;

        $items.hide();
        var start = (page - 1) * itemsPerPage;
        var end = start + itemsPerPage;
        $items.slice(start, end).show();

        // Update button states
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);

        // Update page info
        $('#page-info').text('Page ' + currentPage + ' of ' + totalPages);
    }

    // Build pagination controls
    $('#pagination').html(`
        <button id="prev-page" class="px-3 py-1 rounded bg-gray-200 mr-2">Prev</button>
        <span id="page-info" class="px-3 py-1"></span>
        <button id="next-page" class="px-3 py-1 rounded bg-gray-200 ml-2">Next</button>
    `);

    showPage(1); // initial page

    // Button events
    $('#prev-page').click(function(){ showPage(currentPage - 1); });
    $('#next-page').click(function(){ showPage(currentPage + 1); });
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