<?php
include 'db.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$user = $_SESSION['user'];
// ADD
if (isset($_POST['addCategory'])) {
  $name = $conn->real_escape_string($_POST['name']);
  $slug = $conn->real_escape_string($_POST['slug']);
  $conn->query("INSERT INTO categories (name, slug) VALUES ('$name', '$slug')");
  header("Location: categories.php");
  exit;
}

// EDIT
if (isset($_POST['editCategory'])) {
  $id = intval($_POST['id']);
  $name = $conn->real_escape_string($_POST['name']);
  $slug = $conn->real_escape_string($_POST['slug']);
  $conn->query("UPDATE categories SET name='$name', slug='$slug' WHERE id=$id");
  header("Location: categories.php");
  exit;
}

// DELETE
if (isset($_POST['deleteCategory'])) {
  $id = intval($_POST['id']);
  $conn->query("DELETE FROM categories WHERE id=$id");
  header("Location: categories.php");
  exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
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
    .font-montserrat { font-family: 'Montserrat', sans-serif; }
    .font-opensans { font-family: 'Open Sans', sans-serif; }
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
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors">
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
            <a href="categories.php" class="flex items-center px-4 py-3 text-white bg-blue-600 rounded-lg">
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
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
              </div>
              <h2 class="text-xl font-montserrat font-bold text-white">Manajemen Kategori</h2>
            </div>
            <button onclick="openModal('addModal')" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <span>Add Category</span>
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php
              $modalHTML = '';
              while ($row = $categories->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= $row['id'] ?></td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($row['name']) ?></div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      <?= htmlspecialchars($row['slug']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                      <button onclick="openModal('editModal<?= $row['id'] ?>')" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Edit</span>
                      </button>
                      <button onclick="openModal('deleteModal<?= $row['id'] ?>')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>Delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php
                $modalHTML .= '
                  <!-- Edit Modal -->
                  <div id="editModal' . $row['id'] . '" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                      <form method="POST">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 rounded-t-xl">
                          <div class="flex items-center justify-between">
                            <h3 class="text-lg font-montserrat font-bold text-white">Edit Category</h3>
                            <button type="button" onclick="closeModal(\'editModal' . $row['id'] . '\')" class="text-white hover:text-amber-200 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                              </svg>
                            </button>
                          </div>
                        </div>
                        <div class="p-6 space-y-4">
                          <input type="hidden" name="id" value="' . $row['id'] . '">
                          <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category Name</label>
                            <input type="text" name="name" value="' . htmlspecialchars($row['name']) . '" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" required>
                          </div>
                          <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
                            <input type="text" name="slug" value="' . htmlspecialchars($row['slug']) . '" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" required>
                          </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                          <button type="button" onclick="closeModal(\'editModal' . $row['id'] . '\')" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</button>
                          <button type="submit" name="editCategory" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- Delete Modal -->
                  <div id="deleteModal' . $row['id'] . '" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                      <form method="POST">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-xl">
                          <div class="flex items-center justify-between">
                            <h3 class="text-lg font-montserrat font-bold text-white">Delete Category</h3>
                            <button type="button" onclick="closeModal(\'deleteModal' . $row['id'] . '\')" class="text-white hover:text-blue-200 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                              </svg>
                            </button>
                          </div>
                        </div>
                        <div class="p-6">
                          <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                              </svg>
                            </div>
                            <div>
                              <h4 class="text-lg font-semibold text-gray-900">Are you sure?</h4>
                              <p class="text-gray-600">This will permanently delete the category <strong>' . htmlspecialchars($row['name']) . '</strong>. This action cannot be undone.</p>
                            </div>
                          </div>
                          <input type="hidden" name="id" value="' . $row['id'] . '">
                        </div>
                        <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                          <button type="button" onclick="closeModal(\'deleteModal' . $row['id'] . '\')" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</button>
                          <button type="submit" name="deleteCategory" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">Delete Category</button>
                        </div>
                      </form>
                    </div>
                  </div>';
                ?>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?= $modalHTML ?>

    <!-- Redesigned Add Modal with modern styling -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
        <form method="POST">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-montserrat font-bold text-white">Add New Category</h3>
              <button type="button" onclick="closeModal('addModal')" class="text-white hover:text-blue-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Category Name</label>
              <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
              <input type="text" name="slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
            <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</button>
            <button type="submit" name="addCategory" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">Add Category</button>
          </div>
        </form>
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
