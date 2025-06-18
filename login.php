<?php
session_start();
require 'db.php';

if (isset($_SESSION['user'])) {
    // Redirect sesuai role jika sudah login
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login sukses
                $_SESSION['user'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Email tidak ditemukan.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Pengadilan Agama Simalungun</title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #002855;
      font-family: 'Merriweather', serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #f0c14b;
    }
    .login-container {
      background: rgba(255 255 255 / 0.1);
      padding: 3rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgb(0 11 52 / 0.6);
      width: 360px;
      text-align: center;
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255 255 255 / 0.15);
    }
    h2 {
      margin-bottom: 2rem;
      font-weight: 700;
      font-size: 1.75rem;
      color: #f0c14b;
      letter-spacing: 1px;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    label {
      font-weight: 700;
      font-size: 1rem;
      text-align: left;
      color: #f0c14b;
    }
    input[type="email"],
    input[type="password"] {
      height: 42px;
      padding: 0 10px;
      font-size: 1rem;
      border-radius: 8px;
      border: none;
      outline: none;
      color: #002855;
    }
    input[type="email"]::placeholder,
    input[type="password"]::placeholder {
      color: #666;
    }
    button {
      height: 42px;
      background-color: #f0c14b;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
      color: #002855;
    }
    button:hover,
    button:focus {
      background-color: #ddb03b;
    }
    .error {
      margin-top: 1rem;
      color: #ff8c8c;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="login-container" role="main" aria-label="Form login Pengadilan Agama Simalungun">
    <h2>Login</h2>
    <?php if($error): ?>
      <p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="" novalidate>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Masukkan email" required autocomplete="username" aria-required="true" />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password" aria-required="true" />

      <button type="submit" name="login">Masuk</button>
    </form>
  </div>
</body>
</html>
