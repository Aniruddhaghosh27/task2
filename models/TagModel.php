<?php
require_once __DIR__ . '/../config/database.php';

class TagModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM tags ORDER BY name")->fetchAll();
    }

    public function create(string $name): int {
        $name = strtolower(trim($name));
        $stmt = $this->db->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
        $stmt->execute([$name]);
        $sel = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
        $sel->execute([$name]);
        return (int) $sel->fetchColumn();
    }

    public function delete(int $id): array {
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM article_tags WHERE tag_id = ?"
        );
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Tag is used by articles.'];
        }
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    }
}
