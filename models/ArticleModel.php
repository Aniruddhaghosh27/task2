<?php
require_once __DIR__ . '/../config/database.php';

class ArticleModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ── Scheduled Publishing ───────────────────────────────────────────────
    public function publishScheduled(): void {
        $stmt = $this->db->prepare(
            "UPDATE articles
             SET status = 'published'
             WHERE status = 'draft'
               AND publish_at IS NOT NULL
               AND publish_at <= NOW()"
        );
        $stmt->execute();
    }

    // ── Articles ──────────────────────────────────────────────────────────
    public function getAllByAuthor(int $authorId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS category_name,
                    COUNT(DISTINCT cm.id) AS comment_count
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             LEFT JOIN comments  cm ON cm.article_id = a.id
             WHERE a.author_id = ?
             GROUP BY a.id
             ORDER BY a.created_at DESC"
        );
        $stmt->execute([$authorId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS category_name
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getTagsForArticle(int $articleId): array {
        $stmt = $this->db->prepare(
            "SELECT t.name FROM tags t
             JOIN article_tags at ON at.tag_id = t.id
             WHERE at.article_id = ?"
        );
        $stmt->execute([$articleId]);
        return array_column($stmt->fetchAll(), 'name');
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO articles
               (author_id, category_id, title, body, featured_image_path,
                status, publish_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $data['author_id'],
            $data['category_id'],
            $data['title'],
            $data['body'],
            $data['featured_image_path'],
            $data['status'],
            $data['publish_at'] ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE articles
             SET category_id = ?, title = ?, body = ?,
                 status = ?, publish_at = ?,
                 featured_image_path = COALESCE(?, featured_image_path)
             WHERE id = ? AND author_id = ?"
        );
        $stmt->execute([
            $data['category_id'],
            $data['title'],
            $data['body'],
            $data['status'],
            $data['publish_at'] ?: null,
            $data['featured_image_path'],
            $id,
            $data['author_id'],
        ]);
    }

    public function delete(int $id, int $authorId): void {
        // Remove pivot rows first
        $stmt = $this->db->prepare("DELETE FROM article_tags WHERE article_id = ?");
        $stmt->execute([$id]);

        $stmt = $this->db->prepare(
            "DELETE FROM articles WHERE id = ? AND author_id = ?"
        );
        $stmt->execute([$id, $authorId]);
    }

    public function toggleStatus(int $id, int $authorId): string {
        $article = $this->getById($id);
        if (!$article || (int)$article['author_id'] !== $authorId) {
            throw new RuntimeException('Not found or forbidden');
        }
        $newStatus = $article['status'] === 'published' ? 'draft' : 'published';
        $stmt = $this->db->prepare(
            "UPDATE articles SET status = ? WHERE id = ? AND author_id = ?"
        );
        $stmt->execute([$newStatus, $id, $authorId]);
        return $newStatus;
    }

    // ── Tags ──────────────────────────────────────────────────────────────
    public function syncTags(int $articleId, string $rawTags): void {
        // Remove existing pivot rows
        $del = $this->db->prepare("DELETE FROM article_tags WHERE article_id = ?");
        $del->execute([$articleId]);

        if (trim($rawTags) === '') return;

        $names = array_unique(
            array_filter(
                array_map(fn($t) => strtolower(trim($t)), explode(',', $rawTags))
            )
        );

        foreach ($names as $name) {
            // Insert tag if not exists
            $ins = $this->db->prepare(
                "INSERT IGNORE INTO tags (name) VALUES (?)"
            );
            $ins->execute([$name]);

            // Get tag id
            $sel = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
            $sel->execute([$name]);
            $tagId = $sel->fetchColumn();

            // Pivot
            $pivot = $this->db->prepare(
                "INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)"
            );
            $pivot->execute([$articleId, $tagId]);
        }
    }
}
