<?php
require_once 'config/db.php';

echo "Starting database setup...\n";

// SQL to create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_users) === TRUE) {
    echo "Table 'users' created successfully or already exists.\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
    exit; // Exit if users table creation fails
}

// SQL to add user_id column to photos table
$sql_alter_photos = "ALTER TABLE photos ADD COLUMN user_id INT(11) UNSIGNED AFTER id";

// First, check if the column already exists
$result = $conn->query("SHOW COLUMNS FROM `photos` LIKE 'user_id'");
if ($result->num_rows == 0) {
    if ($conn->query($sql_alter_photos) === TRUE) {
        echo "Column 'user_id' added to 'photos' table successfully.\n";
    } else {
        echo "Error adding column to photos table: " . $conn->error . "\n";
        // Don't exit, maybe we can still add the constraint
    }
} else {
    echo "Column 'user_id' already exists in 'photos' table.\n";
}

// SQL to add foreign key constraint
$sql_fk_constraint = "ALTER TABLE photos ADD CONSTRAINT fk_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE";

// Check if the foreign key constraint already exists
$result = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'photos' AND CONSTRAINT_NAME = 'fk_user'");
if ($result->num_rows == 0) {
    if ($conn->query($sql_fk_constraint) === TRUE) {
        echo "Foreign key 'fk_user' added successfully.\n";
    } else {
        echo "Error adding foreign key: " . $conn->error . "\n";
    }
} else {
    echo "Foreign key 'fk_user' already exists.\n";
}


$conn->close();
echo "Database setup finished.\n";
?>
