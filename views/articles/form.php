<?php
$isEdit    = ($action === 'edit');
$pageTitle = $isEdit ? 'Edit Article' : 'New Article';
?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
  <div>
    <h1 class="page-title"><?= $isEdit ? 'Edit Article' : 'Create Article' ?></h1>
    <a href="ArticleController.php?action=dashboard" class="back-link">← Back to Dashboard</a>
  </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
  <strong>Please fix the following:</strong>
  <ul>
    <?php foreach ($errors as $err): ?>
      <li><?= htmlspecialchars($err) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form class="article-form"
      method="POST"
      enctype="multipart/form-data"
      action="ArticleController.php?action=<?= $isEdit ? 'edit&id='.$id : 'create' ?>">

  <div class="form-grid">

    <!-- LEFT: Main content -->
    <div class="form-main">

      <div class="field">
        <label for="title">Title <span class="req">*</span></label>
        <input type="text" id="title" name="title"
               value="<?= htmlspecialchars($article['title'] ?? $_POST['title'] ?? '') ?>"
               placeholder="Enter article title…" required />
      </div>

      <div class="field">
        <label for="body">Body <span class="req">*</span></label>
        <textarea id="body" name="body" rows="14" placeholder="Write your article here…" required><?= htmlspecialchars($article['body'] ?? $_POST['body'] ?? '') ?></textarea>
      </div>

      <div class="field">
        <label for="tags">Tags <span class="hint">(comma-separated, e.g. php, tutorial, web)</span></label>
        <input type="text" id="tags" name="tags"
               value="<?= htmlspecialchars($existingTags ?? $_POST['tags'] ?? '') ?>"
               placeholder="php, tutorial, web" />
      </div>

    </div>

    <!-- RIGHT: Meta -->
    <div class="form-sidebar">

      <div class="sidebar-card">
        <h3 class="sidebar-title">Publish</h3>

        <div class="field">
          <label>Status <span class="req">*</span></label>
          <div class="radio-group">
            <?php foreach (['draft' => 'Draft', 'published' => 'Published'] as $val => $label): ?>
            <label class="radio-label">
              <input type="radio" name="status" value="<?= $val ?>"
                <?= (($article['status'] ?? $_POST['status'] ?? 'draft') === $val) ? 'checked' : '' ?> />
              <?= $label ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label for="publish_at">Schedule (optional)</label>
          <input type="datetime-local" id="publish_at" name="publish_at"
                 value="<?= htmlspecialchars(
                     isset($article['publish_at']) && $article['publish_at']
                     ? date('Y-m-d\TH:i', strtotime($article['publish_at']))
                     : ($_POST['publish_at'] ?? '')
                 ) ?>" />
          <small class="field-hint">If set and status is Draft, publishes automatically at this time.</small>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          <?= $isEdit ? '💾 Save Changes' : '🚀 Publish Article' ?>
        </button>
      </div>

      <div class="sidebar-card">
        <h3 class="sidebar-title">Category <span class="req">*</span></h3>
        <select name="category_id" id="category_id" required>
          <option value="">— Select Category —</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"
            <?= (($article['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="sidebar-card">
        <h3 class="sidebar-title">Featured Image</h3>
        <?php if ($isEdit && !empty($article['featured_image_path'])): ?>
        <div class="current-image">
          <img src="/<?= htmlspecialchars($article['featured_image_path']) ?>" alt="Current image" />
          <small>Upload a new image to replace.</small>
        </div>
        <?php endif; ?>
        <input type="file" name="featured_image" id="featured_image" accept="image/jpeg,image/png" />
        <small class="field-hint">JPEG or PNG, max 3 MB.</small>
        <div class="image-preview-wrap" id="img-preview-wrap" style="display:none">
          <img id="img-preview" src="" alt="Preview" />
        </div>
      </div>

    </div>
  </div>
</form>

<script src="/public/js/form.js"></script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
