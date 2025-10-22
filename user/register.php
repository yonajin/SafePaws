<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #fff6f1;
      font-family: 'Poppins', sans-serif;
    }
    .register-container {
      max-width: 450px;
      margin: 80px auto;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px;
    }
    .btn-register {
      background-color: #f8a488;
      border: none;
      color: #fff;
    }
    .btn-register:hover {
      background-color: #e78d73;
    }
    a {
      color: #f8a488;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="register-container">
    <h3 class="text-center mb-4">Create an Account</h3>
    <form action="register_process.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-register">Register</button>
      </div>
    </form>
    <p class="text-center mt-3 mb-0">
      Already have an account? <a href="login.php">Login here</a>
    </p>
  </div>

</body>
</html>
