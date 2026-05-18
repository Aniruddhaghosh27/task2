<?php
require_once __DIR__ . '/../config/database.php';

class CategoryModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }

    public function create(string $name): int {
        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([trim($name)]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): array {
        // Block if articles reference this category
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM articles WHERE category_id = ?"
        );
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Category is used by articles.'];
        }
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    }
}
