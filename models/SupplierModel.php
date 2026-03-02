<?php
// -----------------------------------------------
// SupplierModel.php — all DB logic for suppliers
// -----------------------------------------------

require_once __DIR__ . '/../config/database.php';

class SupplierModel {

    // Grab all suppliers (newest first)
    public static function getAll(): array {
        $db = getDB();
        $result = $db->query("SELECT * FROM suppliers ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Single supplier by ID
    public static function getById(int $id): ?array {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // Count of all suppliers
    public static function count(): int {
        $db = getDB();
        $row = $db->query("SELECT COUNT(*) AS cnt FROM suppliers")->fetch_assoc();
        return (int) $row['cnt'];
    }

    // Create a new supplier — uses prepared statement to prevent SQL injection
    public static function create(array $data): int {
        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO suppliers (supplier_name, phone_number, village_location, cooperative_name)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssss',
            $data['supplier_name'],
            $data['phone_number'],
            $data['village_location'],
            $data['cooperative_name']
        );
        $stmt->execute();
        return $db->insert_id;
    }

    // Update existing supplier
    public static function update(int $id, array $data): bool {
        $db   = getDB();
        $stmt = $db->prepare(
            "UPDATE suppliers
             SET supplier_name = ?, phone_number = ?, village_location = ?, cooperative_name = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            'ssssi',
            $data['supplier_name'],
            $data['phone_number'],
            $data['village_location'],
            $data['cooperative_name'],
            $id
        );
        return $stmt->execute();
    }

    // Delete supplier (only if they have no orders)
    public static function delete(int $id): bool|string {
        $db   = getDB();
        // Check for linked orders first
        $chk  = $db->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE supplier_id = ?");
        $chk->bind_param('i', $id);
        $chk->execute();
        $linked = (int) $chk->get_result()->fetch_assoc()['cnt'];

        if ($linked > 0) {
            return "Cannot delete: this supplier has {$linked} order(s) on record.";
        }

        $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
