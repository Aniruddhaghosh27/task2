/* form.js — Article form enhancements */

// ── Image Preview ─────────────────────────────────────────────────────────
const fileInput   = document.getElementById('featured_image');
const previewWrap = document.getElementById('img-preview-wrap');
const previewImg  = document.getElementById('img-preview');

if (fileInput) {
  fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) { previewWrap.style.display = 'none'; return; }

    // Client-side size check (3 MB)
    if (file.size > 3 * 1024 * 1024) {
      alert('File is too large. Maximum size is 3 MB.');
      fileInput.value = '';
      previewWrap.style.display = 'none';
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      previewImg.src         = e.target.result;
      previewWrap.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });
}

// ── Scheduled + Status hint ───────────────────────────────────────────────
const publishAtInput = document.getElementById('publish_at');
const statusRadios   = document.querySelectorAll('input[name="status"]');

function updateHint() {
  const val    = publishAtInput.value;
  const hint   = publishAtInput.nextElementSibling;
  const isDraft = [...statusRadios].find(r => r.checked)?.value === 'draft';

  if (val && isDraft) {
    const dt = new Date(val);
    hint.textContent = `⏰ Will auto-publish on ${dt.toLocaleString()}.`;
    hint.style.color = '#c9a84c';
  } else {
    hint.textContent = 'If set and status is Draft, publishes automatically at this time.';
    hint.style.color = '';
  }
}

if (publishAtInput) {
  publishAtInput.addEventListener('change', updateHint);
  statusRadios.forEach(r => r.addEventListener('change', updateHint));
}
