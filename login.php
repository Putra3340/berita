<?php include 'db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM user WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = [
      'id' => $user['id'],
      'username' => $user['username'],
      'role' => $user['role']
    ];
    header("Location: dashboard.php");
    exit;
  } else {
    $error = 'Invalid username or password';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link
    href="bootstrap.min.css"
    rel="stylesheet"
  />

  <style>
    /* Add subtle background and card polish */
    :root {
      --brand-primary: #0d6efd;
      --brand-foreground: #0b5ed7;
      --surface: #ffffff;
      --muted: #6c757d;
      --bg: #f5f7fb;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(180deg, var(--bg), #ffffff);
    }

    .auth-card {
      border: 0;
      border-radius: 1rem;
      overflow: hidden;
    }

    .auth-aside {
      background: linear-gradient(165deg, var(--brand-primary), var(--brand-foreground));
    }

    .form-title {
      letter-spacing: 0.3px;
    }

    .form-hint {
      color: var(--muted);
    }

    .input-group .btn {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }

    .brand-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: rgba(255,255,255,0.15);
      color: #fff;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body>
  <main class="container py-4">
    <div class="min-vh-100 d-flex align-items-center justify-content-center">
      <div class="card auth-card shadow-lg w-100" style="max-width: 980px;">
        <div class="row g-0">
          <aside class="auth-aside d-none d-md-flex col-md-5 text-white">
            <div class="p-4 p-lg-5 d-flex flex-column justify-content-between w-100">
              <div>
                <div class="brand-badge mb-4">IN</div>
                <h2 class="h3 fw-semibold mb-3">Selamat Datang</h2>
                <p class="mb-0 opacity-75">
                  Login untuk mengelola konten berita dan artikel di halaman admin.
                </p>
              </div>
              <div class="opacity-75 small">
                <span class="d-block">Beritaku Login</span>
              </div>
            </div>
          </aside>

          
          <section class="col-12 col-md-7">
            <div class="p-4 p-lg-5">
              <header class="mb-4 text-center text-md-start">
                <h1 class="h3 form-title mb-1">Login</h1>
                <p class="form-hint mb-0">Masukkan kredensial login anda untuk mengakses halaman admin</p>
              </header>

              <?php if ($error): ?>
                <div class="alert alert-danger" role="alert" aria-live="assertive">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input
                    id="username"
                    name="username"
                    class="form-control"
                    autocomplete="username"
                    required
                    autofocus
                  />
                  <div class="invalid-feedback">Please enter your username.</div>
                </div>

                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label mb-0">Password</label>

                  </div>
                  <div class="input-group">
                    <input
                      id="password"
                      name="password"
                      type="password"
                      class="form-control"
                      autocomplete="current-password"
                      required
                      aria-describedby="passwordHelp"
                    />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      id="togglePassword"
                      aria-label="Toggle password visibility"
                    >
                      Tampilkan
                    </button>
                  </div>
                  <div id="passwordHelp" class="form-text">Pastikan password anda aman.</div>
                  <div class="invalid-feedback">Please enter your password.</div>
                </div>

                <div class="d-grid mt-4">
                  <button class="btn btn-primary btn-lg" type="submit">Login</button>
                </div>
              </form>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>
<script src="bootstrap.min.js"></script>

  <script>
    (function () {
      const toggleBtn = document.getElementById('togglePassword');
      const pwdInput = document.getElementById('password');
      if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
          const isPwd = pwdInput.getAttribute('type') === 'password';
          pwdInput.setAttribute('type', isPwd ? 'text' : 'password');
          toggleBtn.textContent = isPwd ? 'Hide' : 'Show';
        });
      }

      // Bootstrap validation styling
      const form = document.querySelector('form.needs-validation');
      if (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      }
    })();
  </script>
</body>
</html>
