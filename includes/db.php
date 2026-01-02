<?php
// Simply include the existing backend/db.php for now, or we can copy it.
// To be safe and keep single source of truth, let's require the original one.
// But we need to handle path resolution since this file might be included from root or subfolders.

$db_path = __DIR__ . '/../backend/db.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    // Fallback if structure changes - likely won't trigger if structure is constant
    die("Database configuration file not found.");
}
?>
