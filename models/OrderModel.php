<?php
// -----------------------------------------------
// OrderModel.php — all DB logic for orders
// -----------------------------------------------

require_once __DIR__ . '/../config/database.php';

class OrderModel {

    // All orders joined with supplier name
    public static function getAll(): array {
        $db = getDB();
        $sql = "SELECT o.*, s.supplier_name, s.village_location AS supplier_village
                FROM orders o
                JOIN suppliers s ON s.id = o.supplier_id
                ORDER BY o.created_at DESC";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // Single order with supplier info
    public static function getById(int $id): ?array {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT o.*, s.supplier_name, s.phone_number, s.cooperative_name,
                    s.village_location AS supplier_village
             FROM orders o
             JOIN suppliers s ON s.id = o.supplier_id
             WHERE o.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // Today's stats for the dashboard
    public static function getTodayStats(): array {
        $db  = getDB();
        $row = $db->query(
            "SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_value
             FROM orders
             WHERE DATE(created_at) = CURDATE()"
        )->fetch_assoc();
        return $row;
    }

    // 5 most recent orders for dashboard
    public static function getRecent(int $limit = 5): array {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT o.*, s.supplier_name
             FROM orders o
             JOIN suppliers s ON s.id = o.supplier_id
             ORDER BY o.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Create order — total_amount is auto-computed by MySQL generated column
    public static function create(array $data, int $userId): int {
        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO orders (supplier_id, quantity, unit, unit_price, pickup_location, created_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'idsdsi',
            $data['supplier_id'],
            $data['quantity'],
            $data['unit'],
            $data['unit_price'],
            $data['pickup_location'],
            $userId
        );
        $stmt->execute();
        return $db->insert_id;
    }

    // Update order
    public static function update(int $id, array $data): bool {
        $db   = getDB();
        $stmt = $db->prepare(
            "UPDATE orders
             SET supplier_id = ?, quantity = ?, unit = ?, unit_price = ?, pickup_location = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            'idsdsi',
            $data['supplier_id'],
            $data['quantity'],
            $data['unit'],
            $data['unit_price'],
            $data['pickup_location'],
            $id
        );
        return $stmt->execute();
    }

    // Delete order
    public static function delete(int $id): bool {
        $db   = getDB();
        $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
