<?php include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = 'user';

  $stmt = $conn->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $username, $email, $password, $role);
  $stmt->execute();
  header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-body">
          <h2 class="text-center">Register</h2>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input name="email" type="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" required>
            </div>
            <button class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-link">Login</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>