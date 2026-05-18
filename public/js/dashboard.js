/* dashboard.js — Task 2 AJAX interactions */

const BASE = 'ArticleController.php';

// ── Helper: POST via fetch ────────────────────────────────────────────────
async function post(action, params = {}) {
  const body = new FormData();
  for (const [k, v] of Object.entries(params)) body.append(k, v);
  const res = await fetch(`${BASE}?action=${action}`, { method: 'POST', body });
  return res.json();
}

// ── Toggle Publish / Draft ────────────────────────────────────────────────
document.getElementById('articles-tbody').addEventListener('click', async (e) => {

  // Toggle
  if (e.target.classList.contains('btn-toggle')) {
    const btn    = e.target;
    const id     = btn.dataset.id;
    const badge  = document.getElementById(`badge-${id}`);
    btn.disabled = true;

    const data = await post(`toggle&id=${id}`);

    if (data.success) {
      const newStatus = data.status;
      badge.textContent        = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
      badge.className          = `status-badge status-${newStatus}`;
      btn.textContent          = newStatus === 'published' ? 'Unpublish' : 'Publish';
      btn.dataset.status       = newStatus;
    } else {
      alert(data.message || 'Toggle failed.');
    }
    btn.disabled = false;
  }

  // Delete
  if (e.target.classList.contains('del-article')) {
    const id  = e.target.dataset.id;
    if (!confirm('Delete this article permanently?')) return;

    const data = await post(`delete&id=${id}`);
    if (data.success) {
      const row = document.getElementById(`row-${id}`);
      row.classList.add('row-fade-out');
      setTimeout(() => row.remove(), 380);
    } else {
      alert(data.message || 'Delete failed.');
    }
  }
});

// ── Category CRUD ─────────────────────────────────────────────────────────
document.getElementById('cat-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = document.getElementById('cat-name');
  const name  = input.value.trim();
  if (!name) return;

  const data = await post('create_category', { name });
  if (data.success) {
    appendTaxonomyItem('cat-list', data.id, data.name, 'cat');
    input.value = '';
  } else {
    alert(data.message);
  }
});

document.getElementById('cat-list').addEventListener('click', async (e) => {
  if (!e.target.classList.contains('del-cat')) return;
  const id = e.target.dataset.id;
  const data = await post(`delete_category&id=${id}`);
  if (data.success) {
    e.target.closest('li').remove();
  } else {
    alert(data.message);
  }
});

// ── Tag CRUD ──────────────────────────────────────────────────────────────
document.getElementById('tag-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = document.getElementById('tag-name');
  const name  = input.value.trim();
  if (!name) return;

  const data = await post('create_tag', { name });
  if (data.success) {
    appendTaxonomyItem('tag-list', data.id, data.name, 'tag', true);
    input.value = '';
  } else {
    alert(data.message);
  }
});

document.getElementById('tag-list').addEventListener('click', async (e) => {
  if (!e.target.classList.contains('del-tag')) return;
  const id = e.target.dataset.id;
  const data = await post(`delete_tag&id=${id}`);
  if (data.success) {
    e.target.closest('li').remove();
  } else {
    alert(data.message);
  }
});

// ── Util: Append Taxonomy Item ────────────────────────────────────────────
function appendTaxonomyItem(listId, id, name, type, isPill = false) {
  const list = document.getElementById(listId);
  const li   = document.createElement('li');
  li.dataset.id = id;

  const spanClass = isPill ? 'tag-pill' : '';
  li.innerHTML = `
    <span class="${spanClass}">${escHtml(name)}</span>
    <button class="del-btn del-${type}" data-id="${id}">✕</button>
  `;
  list.prepend(li);
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
