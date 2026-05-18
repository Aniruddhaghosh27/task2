<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email এবং Password দিন।';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            header('Location: controllers/ArticleController.php?action=dashboard');
            exit;
        } else {
            $error = 'Email বা Password ভুল।';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — BlogCMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #f7f3ed;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 24px rgba(0,0,0,.1);
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 400px;
    }
    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      color: #0f0f0f;
      margin-bottom: .3rem;
      text-align: center;
    }
    .sub {
      text-align: center;
      color: #999;
      font-size: .88rem;
      margin-bottom: 1.8rem;
    }
    .error {
      background: #f9ebea;
      color: #c0392b;
      border-left: 4px solid #c0392b;
      padding: .7rem 1rem;
      border-radius: 6px;
      font-size: .88rem;
      margin-bottom: 1.2rem;
    }
    .field { margin-bottom: 1.1rem; }
    label {
      display: block;
      font-size: .85rem;
      font-weight: 600;
      color: #3a3a3a;
      margin-bottom: .4rem;
    }
    input {
      width: 100%;
      border: 1.5px solid #e0d9d0;
      border-radius: 8px;
      padding: .65rem .9rem;
      font-family: 'DM Sans', sans-serif;
      font-size: .93rem;
      outline: none;
      background: #f7f3ed;
      color: #0f0f0f;
      transition: border-color .2s;
    }
    input:focus { border-color: #c9a84c; background: #fff; }
    button {
      width: 100%;
      background: #c9a84c;
      color: #0f0f0f;
      border: none;
      border-radius: 8px;
      padding: .75rem;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: .5rem;
      transition: opacity .2s;
    }
    button:hover { opacity: .88; }
    .test-accounts {
      margin-top: 1.5rem;
      background: #f7f3ed;
      border-radius: 8px;
      padding: 1rem;
      font-size: .82rem;
      color: #555;
    }
    .test-accounts strong { display: block; margin-bottom: .5rem; color: #0f0f0f; }
    .test-accounts table { width: 100%; border-collapse: collapse; }
    .test-accounts td { padding: .2rem .4rem; }
    .test-accounts td:first-child { font-weight: 600; color: #c9a84c; }
  </style>
</head>
<body>
<div class="card">
  <h1>✦ BlogCMS</h1>
  <p class="sub">Task 2 — Article Management</p>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="আপনার ইমেইল দিন" required />
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password"
             placeholder="আপনার পাসওয়ার্ড দিন" required />
    </div>
    <button type="submit">Login করুন →</button>
  </form>

  <div class="test-accounts">
    <strong>🧪 Test Accounts (password: password123)</strong>
    <table>
      <tr><td>Admin</td><td>admin@blog.com</td></tr>
      <tr><td>Author</td><td>alice@blog.com</td></tr>
      <tr><td>Reader</td><td>bob@blog.com</td></tr>
    </table>
  </div>
</div>
</body>
</html>
