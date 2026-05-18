<?php $pageTitle = 'Author Dashboard'; ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
  <div>
    <h1 class="page-title">My Articles</h1>
    <p class="page-sub">Manage your drafts and published articles</p>
  </div>
  <a href="ArticleController.php?action=create" class="btn btn-primary">+ New Article</a>
</div>

<?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success">✓ Article saved successfully.</div>
<?php endif; ?>

<!-- ── Category & Tag Manager ─────────────────────────────────────────── -->
<div class="taxonomy-grid">

  <!-- Categories -->
  <div class="taxonomy-card">
    <h2 class="taxonomy-title">Categories</h2>
    <form class="taxonomy-form" id="cat-form">
      <input type="text" id="cat-name" placeholder="New category name…" required />
      <button type="submit" class="btn btn-sm">Add</button>
    </form>
    <ul class="taxonomy-list" id="cat-list">
      <?php foreach ($categories as $cat): ?>
      <li data-id="<?= $cat['id'] ?>">
        <span><?= htmlspecialchars($cat['name']) ?></span>
        <button class="del-btn del-cat" data-id="<?= $cat['id'] ?>">✕</button>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Tags -->
  <div class="taxonomy-card">
    <h2 class="taxonomy-title">Tags</h2>
    <form class="taxonomy-form" id="tag-form">
      <input type="text" id="tag-name" placeholder="New tag name…" required />
      <button type="submit" class="btn btn-sm">Add</button>
    </form>
    <ul class="taxonomy-list" id="tag-list">
      <?php foreach ($tags as $tag): ?>
      <li data-id="<?= $tag['id'] ?>">
        <span class="tag-pill"><?= htmlspecialchars($tag['name']) ?></span>
        <button class="del-btn del-tag" data-id="<?= $tag['id'] ?>">✕</button>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>

<!-- ── Articles Table ─────────────────────────────────────────────────── -->
<div class="table-card">
  <table class="articles-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Category</th>
        <th>Status</th>
        <th>Views</th>
        <th>Comments</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="articles-tbody">
      <?php if (empty($articles)): ?>
      <tr><td colspan="6" class="empty-row">No articles yet. <a href="ArticleController.php?action=create">Create one!</a></td></tr>
      <?php else: ?>
      <?php foreach ($articles as $a): ?>
      <tr id="row-<?= $a['id'] ?>">
        <td class="article-title-cell"><?= htmlspecialchars($a['title']) ?></td>
        <td><?= htmlspecialchars($a['category_name'] ?? '—') ?></td>
        <td>
          <span class="status-badge status-<?= $a['status'] ?>" id="badge-<?= $a['id'] ?>">
            <?= ucfirst($a['status']) ?>
          </span>
        </td>
        <td><?= (int)$a['view_count'] ?></td>
        <td><?= (int)$a['comment_count'] ?></td>
        <td class="action-cell">
          <a href="ArticleController.php?action=edit&id=<?= $a['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
          <button class="btn btn-sm btn-toggle"
                  id="toggle-<?= $a['id'] ?>"
                  data-id="<?= $a['id'] ?>"
                  data-status="<?= $a['status'] ?>">
            <?= $a['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
          </button>
          <button class="btn btn-sm btn-danger del-article" data-id="<?= $a['id'] ?>">Delete</button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="/public/js/dashboard.js"></script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
