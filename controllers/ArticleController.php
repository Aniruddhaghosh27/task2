<?php
session_start();
require_once __DIR__ . '/../models/ArticleModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/TagModel.php';

// ── Auth Guard ────────────────────────────────────────────────────────────
function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireAuthorOrAdmin(): void {
    requireAuth();
    if (!in_array($_SESSION['role'] ?? '', ['author', 'admin'])) {
        http_response_code(403);
        die('Forbidden');
    }
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── Image Upload Helper ───────────────────────────────────────────────────
function handleImageUpload(string $fieldName, string $subdir, int $maxBytes): ?string {
    if (empty($_FILES[$fieldName]['tmp_name'])) return null;

    $file     = $_FILES[$fieldName];
    $allowed  = ['image/jpeg', 'image/png'];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed)) {
        throw new RuntimeException('Only JPEG/PNG images are allowed.');
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds size limit.');
    }

    $ext      = $mime === 'image/png' ? 'png' : 'jpg';
    $filename = uniqid('img_', true) . '.' . $ext;
    $dest     = __DIR__ . "/../public/uploads/{$subdir}/{$filename}";

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Failed to save uploaded file.');
    }
    return "public/uploads/{$subdir}/{$filename}";
}

// ── Validate Article Input ────────────────────────────────────────────────
function validateArticleInput(array $post): array {
    $errors = [];
    if (empty(trim($post['title'] ?? '')))       $errors[] = 'Title is required.';
    if (empty(trim($post['body'] ?? '')))         $errors[] = 'Body is required.';
    if (empty($post['category_id']))              $errors[] = 'Category is required.';
    if (!in_array($post['status'] ?? '', ['draft', 'published']))
                                                  $errors[] = 'Invalid status.';
    return $errors;
}

// ── Router ────────────────────────────────────────────────────────────────
$articleModel  = new ArticleModel();
$categoryModel = new CategoryModel();
$tagModel      = new TagModel();

$action = $_GET['action'] ?? 'dashboard';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── AJAX: Toggle Status ───────────────────────────────────────────────────
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    try {
        $newStatus = $articleModel->toggleStatus($id, (int)$_SESSION['user_id']);
        jsonResponse(['success' => true, 'status' => $newStatus]);
    } catch (RuntimeException $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 403);
    }
}

// ── AJAX: Delete Article ──────────────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    $articleModel->delete($id, (int)$_SESSION['user_id']);
    jsonResponse(['success' => true]);
}

// ── Dashboard ─────────────────────────────────────────────────────────────
if ($action === 'dashboard') {
    requireAuthorOrAdmin();
    $articleModel->publishScheduled();
    $articles   = $articleModel->getAllByAuthor((int)$_SESSION['user_id']);
    $categories = $categoryModel->getAll();
    $tags       = $tagModel->getAll();
    require __DIR__ . '/../views/articles/dashboard.php';
    exit;
}

// ── Create Article ────────────────────────────────────────────────────────
if ($action === 'create') {
    requireAuthorOrAdmin();
    $errors     = [];
    $categories = $categoryModel->getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = validateArticleInput($_POST);
        $imagePath = null;
        if (empty($errors)) {
            try {
                $imagePath = handleImageUpload('featured_image', 'articles', 3 * 1024 * 1024);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $articleId = $articleModel->create([
                'author_id'           => $_SESSION['user_id'],
                'category_id'         => (int)$_POST['category_id'],
                'title'               => trim($_POST['title']),
                'body'                => trim($_POST['body']),
                'featured_image_path' => $imagePath,
                'status'              => $_POST['status'],
                'publish_at'          => $_POST['publish_at'] ?? null,
            ]);
            $articleModel->syncTags($articleId, $_POST['tags'] ?? '');
            header('Location: ArticleController.php?action=dashboard&saved=1');
            exit;
        }
    }
    require __DIR__ . '/../views/articles/form.php';
    exit;
}

// ── Edit Article ──────────────────────────────────────────────────────────
if ($action === 'edit') {
    requireAuthorOrAdmin();
    $errors     = [];
    $categories = $categoryModel->getAll();
    $article    = $articleModel->getById($id);

    if (!$article || (int)$article['author_id'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        die('Forbidden or article not found.');
    }

    $existingTags = implode(', ', $articleModel->getTagsForArticle($id));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors    = validateArticleInput($_POST);
        $imagePath = null;

        if (empty($errors) && !empty($_FILES['featured_image']['tmp_name'])) {
            try {
                $imagePath = handleImageUpload('featured_image', 'articles', 3 * 1024 * 1024);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            $articleModel->update($id, [
                'author_id'           => $_SESSION['user_id'],
                'category_id'         => (int)$_POST['category_id'],
                'title'               => trim($_POST['title']),
                'body'                => trim($_POST['body']),
                'featured_image_path' => $imagePath,
                'status'              => $_POST['status'],
                'publish_at'          => $_POST['publish_at'] ?? null,
            ]);
            $articleModel->syncTags($id, $_POST['tags'] ?? '');
            header('Location: ArticleController.php?action=dashboard&saved=1');
            exit;
        }
        // Re-fetch article for form repopulation
        $article = $articleModel->getById($id);
    }
    require __DIR__ . '/../views/articles/form.php';
    exit;
}

// ── Category CRUD (AJAX) ──────────────────────────────────────────────────
if ($action === 'create_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') jsonResponse(['success' => false, 'message' => 'Name required.'], 422);
    $newId = $categoryModel->create($name);
    jsonResponse(['success' => true, 'id' => $newId, 'name' => $name]);
}

if ($action === 'delete_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    jsonResponse($categoryModel->delete($id));
}

// ── Tag CRUD (AJAX) ───────────────────────────────────────────────────────
if ($action === 'create_tag' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') jsonResponse(['success' => false, 'message' => 'Name required.'], 422);
    $newId = $tagModel->create($name);
    jsonResponse(['success' => true, 'id' => $newId, 'name' => strtolower($name)]);
}

if ($action === 'delete_tag' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuthorOrAdmin();
    jsonResponse($tagModel->delete($id));
}

// Fallback
http_response_code(404);
echo 'Action not found.';
