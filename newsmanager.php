<?php
include 'db.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$user = $_SESSION['user'];

// Handle Create
if (isset($_POST['add_news'])) {
  $title = $_POST['title'];
  $slug = $_POST['slug'];
  $content = $_POST['content'];
  $category_id = $_POST['category_id'];
  $tags = $_POST['tags'];
  $is_published = isset($_POST['is_published']) ? 1 : 0;
  $is_hotnews = isset($_POST['is_hotnews']) ? 1 : 0;

  // Handle file upload
  $cover_image = null;
  if (!empty($_FILES['cover_image']['name'])) {
    $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . "." . strtolower($ext);
    move_uploaded_file($_FILES['cover_image']['tmp_name'], __DIR__ . "/media/thumbnail/" . $filename);
    $cover_image = "media/thumbnail/" . $filename;
  }

  $stmt = $conn->prepare("
    INSERT INTO news (user_id, category_id, title, slug, content, cover_image, tags, is_published,is_hotnews) 
    VALUES (1, ?, ?, ?, ?, ?, ?, ?,?)
");
  $stmt->bind_param("isssssii", $category_id, $title, $slug, $content, $cover_image, $tags, $is_published,$is_hotnews);
  $stmt->execute();

  // get inserted news_id
  $news_id = $conn->insert_id;

  // insert into counter
  $stmt2 = $conn->prepare("INSERT INTO newscounter (news_id, view_count) VALUES (?, 1)");
  $stmt2->bind_param("i", $news_id);
  $stmt2->execute();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM news WHERE id = $id");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// Handle Update
if (isset($_POST['edit_news'])) {
  $id = $_POST['id'];
  $title = $_POST['title'];
  $slug = $_POST['slug'];
  $content = $_POST['content'];
  $category_id = $_POST['category_id'];
  $tags = $_POST['tags'];
  $is_published = isset($_POST['is_published']) ? 1 : 0;
  $is_hotnews = isset($_POST['is_hotnews']) ? 1 : 0;

  // Keep old image
  $cover_image = $_POST['old_cover'];

  if (!empty($_FILES['cover_image']['name'])) {
    $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . "." . strtolower($ext);
    move_uploaded_file($_FILES['cover_image']['tmp_name'], __DIR__ . "/media/thumbnail/" . $filename);
    $cover_image = "media/thumbnail/" . $filename;
  }

  $stmt = $conn->prepare("UPDATE news SET category_id=?, title=?, slug=?, content=?, cover_image=?, tags=?, is_published=?, is_hotnews=? WHERE id=?");
  $stmt->bind_param("isssssiii", $category_id, $title, $slug, $content, $cover_image, $tags, $is_published,$is_hotnews, $id);
  $stmt->execute();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$news = $conn->query("SELECT n.*, c.name AS category_name FROM news n JOIN categories c ON n.category_id = c.id ORDER BY n.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - News Management</title>
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
          <button id="sidebarToggle" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
          <h1 class="text-2xl font-montserrat font-black text-blue-600">BeritaKu Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600 font-medium">Welcome, admin</span>
          <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-semibold">A</span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="flex h-screen">
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
            <a href="newsmanager.php" class="flex items-center px-4 py-3 text-white bg-blue-600 rounded-lg">
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


    <main class="flex-1 overflow-auto lg:ml-0">
      <div class="p-6 max-w-7xl mx-auto">
        <div class="mb-8">
          <h2 class="text-3xl font-montserrat font-black text-gray-900 mb-2">Manajemen Berita</h2>
          <p class="text-gray-600">Buat, edit, dan kelola artikel berita Anda</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
          <div class="bg-blue-600 text-white px-6 py-4 rounded-t-xl">
            <h3 class="text-lg font-montserrat font-bold flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Tambahkan Berita Baru
            </h3>
          </div>
          <div class="p-6">
            <form method="post" enctype="multipart/form-data" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                  <input type="text" name="title" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Slug (URL)</label>
                  <input type="text" name="slug" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Konten</label>
                <textarea name="content" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Thumbnail</label>
                  <input type="file" name="cover_image" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                  <select name="category_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tags (dipisahkan koma & opsional)</label>
                  <input type="text" name="tags" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div> -->
                <div class="flex items-center pt-8">
                <label class="relative inline-flex items-center cursor-pointer m-5">
  <input type="checkbox" name="is_published" class="sr-only peer">
  <div
    class="w-14 h-8 bg-gray-300 rounded-full peer peer-checked:bg-green-600 transition-colors duration-300">
  </div>
  <div
    class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full shadow
           peer-checked:translate-x-6 transform transition-transform duration-300">
  </div>
  <span class="ml-4 text-sm font-semibold text-gray-700">Publikasikan</span>
</label>


                  <label class="relative inline-flex items-center cursor-pointer m-5">
  <input type="checkbox" name="is_hotnews" class="sr-only peer">
  <div
    class="w-14 h-8 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-300">
  </div>
  <div
    class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full shadow
           peer-checked:translate-x-6 transform transition-transform duration-300">
  </div>
  <span class="ml-4 text-sm font-semibold text-gray-700">Hot&nbsp;News</span>
</label>


                </div>

              </div>

              <div class="pt-4">
                <button type="submit" name="add_news" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Tambahkan Berita
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
          <div class="bg-gray-900 text-white px-6 py-4 rounded-t-xl">
            <h3 class="text-lg font-montserrat font-bold">List Berita</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Judul</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Kategori</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 w-32">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php while ($n = $news->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors" id="berita_list">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900"><?= htmlspecialchars($n['title']) ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                        <?= htmlspecialchars($n['category_name']) ?>
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <?= $n['is_published'] ?
                        '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Dipublikasikan</span>' :
                        '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>' ?>
                        <?= $n['is_hotnews'] ?
                        '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-green-800">Hot News</span>' :
                        '' ?>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex space-x-2">
                        <button onclick="openEditModal(<?= $n['id'] ?>)" class="bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-lg transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                          </svg>
                        </button>
                        <a href="?delete=<?= $n['id'] ?>" onclick="return confirm('Anda yakin mau menghapus artikel ini?')" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                        </a>
                      </div>
                    </td>
                  </tr>

                  <div id="editModal<?= $n['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                      <form method="post" enctype="multipart/form-data">
                        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-xl flex items-center justify-between">
                          <h3 class="text-lg font-montserrat font-bold">Edit Article</h3>
                          <button type="button" onclick="closeEditModal(<?= $n['id'] ?>)" class="text-white hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                          </button>
                        </div>
                        <div class="p-6 space-y-6">
                          <input type="hidden" name="id" value="<?= $n['id'] ?>">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                              <input type="text" name="title" value="<?= htmlspecialchars($n['title']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                            </div>
                            <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Slug (URL)</label>
                              <input type="text" name="slug" value="<?= htmlspecialchars($n['slug']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                            </div>
                          </div>

                          <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Isi Berita</label>
                            <textarea name="content" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"><?= htmlspecialchars($n['content']) ?></textarea>
                          </div>

                          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Thumbnail Sekarang</label>
                              <?php if (!empty($n['cover_image'])): ?>
                                <img src="<?= htmlspecialchars($n['cover_image']) ?>" class="w-24 h-24 object-cover rounded-lg border border-gray-200 mb-2">
                              <?php else: ?>
                                <div class="w-24 h-24 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center mb-2">
                                  <span class="text-gray-400 text-sm">No image</span>
                                </div>
                              <?php endif; ?>
                              <input type="hidden" name="old_cover" value="<?= htmlspecialchars($n['cover_image']) ?>">
                            </div>
                            <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Ubah Gambar</label>
                              <input type="file" name="cover_image" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                          </div>

                          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                              <select name="category_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                                <?php
                                $cats2 = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                                while ($cat2 = $cats2->fetch_assoc()):
                                ?>
                                  <option value="<?= $cat2['id'] ?>" <?= ($cat2['id'] == $n['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat2['name']) ?>
                                  </option>
                                <?php endwhile; ?>
                              </select>
                            </div>
                            <!-- <div>
                              <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                              <input type="text" name="tags" value="<?= htmlspecialchars($n['tags']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div> -->
                          </div>

                          <div>
                            <label class="flex items-center cursor-pointer">
                              <input type="checkbox" name="is_published" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" <?= $n['is_published'] ? 'checked' : '' ?>>
                              <span class="ml-3 text-sm font-semibold text-gray-700">Publikasikan</span>
                            </label>
                          </div>
                          <div>
                            <label class="flex items-center cursor-pointer">
                              <input type="checkbox" name="is_hotnews" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" <?= $n['is_hotnews'] ? 'checked' : '' ?>>
                              <span class="ml-3 text-sm font-semibold text-gray-700">Hot News</span>
                            </label>
                          </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                          <button type="button" onclick="closeEditModal(<?= $n['id'] ?>)" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                          <button type="submit" name="edit_news" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Simpan Perubahan
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endwhile; ?>
              </tbody>
            </table>
            <div id="pagination" class="mt-4 flex items-center justify-center"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="./jquery.min.js"></script>
  <script>
    $(function() {
      var itemsPerPage = 10; // how many rows per page
      var $items = $('table tbody tr'); // target rows
      var totalItems = $items.length;
      var totalPages = Math.ceil(totalItems / itemsPerPage);
      var currentPage = 1;

      function showPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;

        $items.hide();
        var start = (page - 1) * itemsPerPage;
        var end = start + itemsPerPage;
        $items.slice(start, end).show();

        $('#pagination .page-number')
          .text('Halaman ' + currentPage + ' of ' + totalPages);
      }

      // Build pagination controls
      $('#pagination').html(`
        <button id="prev-page" class="px-3 py-1 rounded bg-gray-200 mr-2">Sebelumnya</button>
        <span class="page-number px-3 py-1"></span>
        <button id="next-page" class="px-3 py-1 rounded bg-gray-200 ml-2">Selanjutnya</button>
    `);

      showPage(1); // first page

      // Button events
      $('#pagination').on('click', '#prev-page', function() {
        showPage(currentPage - 1);
      });
      $('#pagination').on('click', '#next-page', function() {
        showPage(currentPage + 1);
      });
    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Auto-generate slug from title
      const titleInput = document.querySelector('input[name="title"]');
      const slugInput = document.querySelector('input[name="slug"]');

      if (titleInput && slugInput) {
        titleInput.addEventListener("input", function() {
          let slug = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
          slugInput.value = slug;
        });
      }

      // Mobile sidebar toggle
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');

      if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
          sidebar.classList.toggle('-translate-x-full');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
          if (window.innerWidth < 1024) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
              sidebar.classList.add('-translate-x-full');
            }
          }
        });
      }
    });

    // Modal functions
    function openEditModal(id) {
      document.getElementById('editModal' + id).classList.remove('hidden');
      document.getElementById('editModal' + id).classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeEditModal(id) {
      document.getElementById('editModal' + id).classList.add('hidden');
      document.getElementById('editModal' + id).classList.remove('flex');
      document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('bg-black') && event.target.classList.contains('bg-opacity-50')) {
        const modals = document.querySelectorAll('[id^="editModal"]');
        modals.forEach(modal => {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        });
        document.body.style.overflow = 'auto';
      }
    });
  </script>

</body>

</html>