<?php
include 'db.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$user = $_SESSION['user'];
// Count total news
$newsCount = $conn->query("SELECT COUNT(*) AS total FROM news WHERE is_published = 1")->fetch_assoc()['total'];
// Count categories
$catCount = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];

// Count users
$userCount = $conn->query("SELECT COUNT(*) AS total FROM user")->fetch_assoc()['total'];
$newsdraftCount = $conn->query("SELECT COUNT(*) AS total FROM news WHERE is_published = 0")->fetch_assoc()['total'];

$newsRes = $conn->query("SELECT n.*, c.name AS category_name 
                         FROM news n
                         JOIN categories c ON n.category_id = c.id
                         WHERE n.is_published = 1
                         ORDER BY id DESC
                         LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories Management - News Admin</title>
  <script src="./tailwind.js"></script>
  <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet"> -->
  <style>
.font-montserrat {
      font-family: 'Montserrat', sans-serif;
    }

    .font-opensans {
      font-family: 'Open Sans', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-50 font-opensans">

  <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button id="sidebarToggle" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
          <h1 class="text-2xl font-montserrat font-black text-blue-600">BeritaKu Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600 font-medium">Welcome, Admin</span>
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-semibold">A</span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="flex h-screen">
    <!-- Updated sidebar to match red theme -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-blue-50 border-r border-blue-100 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out">
      <div class="flex flex-col h-full">
        <div class="px-6 py-6">
          <h2 class="text-lg font-montserrat font-bold text-gray-800 mb-6">Menu</h2>
          <nav class="space-y-2">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-white bg-blue-600 rounded-lg">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              Dashboard
            </a>
            <a href="newsmanager.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              Manajemen Berita
            </a>
            <a href="categories.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H5m14 8H5m14 4H5"></path>
              </svg>
              Kategori
            </a>
            <a href="logout.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              Logout
            </a>
          </nav>
        </div>
      </div>
    </aside>

    <!-- Completely redesigned main content area with modern Tailwind styling -->
    <main class="flex-1 overflow-auto lg:ml-0">
      <div class="p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <h2 class="text-xl font-montserrat font-bold text-white">Dashboard</h2>
              </div>
            </div>
          </div>

          <!-- Cards Section -->
          <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Card 1 -->
            <div class="bg-white border border-gray-200 rounded-xl shadow p-5 hover:shadow-md transition">
              <h3 class="text-sm font-semibold text-gray-500">Total Berita</h3>
              <p class="mt-2 text-3xl font-bold text-blue-600"><?= $newsCount ?></p>
            </div>

            <!-- Card 2 -->
            <div class="bg-white border border-gray-200 rounded-xl shadow p-5 hover:shadow-md transition">
              <h3 class="text-sm font-semibold text-gray-500">Total Kategori</h3>
              <p class="mt-2 text-3xl font-bold text-green-600"><?= $catCount ?></p>
            </div>

            <!-- Card 3 -->
            <div class="bg-white border border-gray-200 rounded-xl shadow p-5 hover:shadow-md transition">
              <h3 class="text-sm font-semibold text-gray-500">Draf Berita</h3>
              <p class="mt-2 text-3xl font-bold text-purple-600"><?= $newsdraftCount ?></p>
            </div>

          </div>
          <h4 class="text-3xl font-black text-primary p-2">Berita Terbaru</h4>

<!-- Grid Layout -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
  <?php while ($row = $newsRes->fetch_assoc()): ?>
    <!-- Article Card -->
    <article class="news-card bg-card-bg rounded-xl overflow-hidden shadow-md flex flex-col">
      <div class="w-full h-32">
        <img src="<?= htmlspecialchars($row['cover_image'] ?? '/placeholder.svg?height=200&width=400') ?>"
          alt="<?= htmlspecialchars($row['title']) ?>"
          class="w-full h-full object-cover">
      </div>
      <div class="p-6 flex flex-col flex-grow">
        <span class="category-tag bg-green-600 text-white px-3 py-1 rounded-full">
          <?= htmlspecialchars($row['category_name']) ?>
        </span>

        <h3 class="text-lg font-black text-primary mt-3 mb-2">
          <?= htmlspecialchars($row['title']) ?>
        </h3>

        <p class="text-secondary mb-4 text-sm leading-relaxed flex-grow">
          <?= htmlspecialchars(mb_strimwidth($row['content'], 0, 100, "...")) ?>
        </p>

        <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
          <span>
            <?= date("M d, Y H:i", strtotime($row['created_at'])) ?> • By <?= htmlspecialchars($row['author'] ?? 'Unknown') ?>
          </span>
          <a href="news.php?slug=<?= urlencode($row['slug']) ?>"
            class="read-more-btn bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium">
            Read More
          </a>
        </div>
      </div>
    </article>
  <?php endwhile; ?>
</div>
        </div>
          
        

    </main>

  </div>

  <!-- Added custom JavaScript for modal functionality -->
  <script>
    function openModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('bg-black')) {
        const modals = document.querySelectorAll('[id$="Modal"]');
        modals.forEach(modal => {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        });
        document.body.style.overflow = 'auto';
      }
    });

    // Sidebar toggle functionality
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('-translate-x-full');
    });
  </script>

</body>

</html>