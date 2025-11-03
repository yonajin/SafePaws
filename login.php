

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff6f1;
      font-family: 'Poppins', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .login-card {
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px;
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .brand-name {
      font-size: 32px;
      font-weight: 800;
      color: #a9745b;
      margin-bottom: 10px;
    }
    .brand-subtitle {
      font-size: 14px;
      color: #888;
      margin-bottom: 25px;
    }
    .login-title {
      font-weight: 700;
      margin-bottom: 25px;
      color: #333;
      text-align: center;
    }
    .form-control {
      border-radius: 10px;
      padding: 12px;
    }
    .btn-login {
      background-color: #f8a488;
      border: none;
      border-radius: 10px;
      padding: 10px 0;
      font-weight: 600;
      color: white;
      width: 100%;
    }
    .btn-login:hover {
      background-color: #e78d73;
    }
    .register-link {
      text-align: center;
      margin-top: 15px;
      color: #666;
    }
    .register-link a {
      color: #f8a488;
      text-decoration: none;
      font-weight: 600;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
    img.logo {
      width: 80px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <!-- 🐾 SafePaws Brand Header -->
    <div class="brand-name">SafePaws</div>
    <div class="brand-subtitle">Where Every Paw Finds a Home</div>

    <h3 class="login-title">Welcome Back!</h3>

    <form action="config/login_process.php" method="POST">
      <div class="mb-3 text-start">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3 text-start">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-login mt-3">Login</button>
    </form>

    <div class="register-link">
      <p>Don’t have an account? <a href="register.php">Register here</a></p>
    </div>
  </div>

</body>
</html>
