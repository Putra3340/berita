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
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h2 class="text-center">Login</h2>
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
              </div>
              <button class="btn btn-primary">Login</button>
              <!-- <a href="register.php" class="btn btn-link">Register</a> -->
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>