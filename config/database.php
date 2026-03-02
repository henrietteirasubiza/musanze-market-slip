<?php
// -----------------------------------------------
// database.php — handles our MySQL connection
// Change these values to match your hosting setup
// -----------------------------------------------

// BASE_PATH for subdirectory deployments (e.g., /oms)
define('BASE_PATH', '/oms');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'order_mgmt'); // your database name

function getDB() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            // In production you'd log this, not display it
            die("Database connection failed: " . $conn->connect_error);
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}
