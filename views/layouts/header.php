<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle ?? 'Author Dashboard') ?> — BlogCMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/style.css" />
</head>
<body>

<nav class="navbar">
  <a class="brand" href="ArticleController.php?action=dashboard">✦ BlogCMS</a>
  <div class="nav-links">
    <a href="ArticleController.php?action=dashboard">Dashboard</a>
    <a href="ArticleController.php?action=create" class="btn-nav">+ New Article</a>
    <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['name'] ?? 'Author') ?></span>
  </div>
</nav>

<main class="main-wrap">
