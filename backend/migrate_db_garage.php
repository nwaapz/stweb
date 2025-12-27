<?php
require_once 'config/database.php';

if (setupDatabase()) {
    echo "Database setup completed successfully.";
} else {
    echo "Database setup failed.";
}
