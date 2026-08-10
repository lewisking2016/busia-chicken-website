<?php
/**
 * EXAMPLE database override — copy to database.local.php and fill in real values.
 *
 * database.local.php is gitignored, so your production credentials stay out of
 * the repository. database.php loads it automatically (if present) before
 * falling back to environment variables or local development defaults.
 *
 * On cPanel you can alternatively set the DB_HOST / DB_NAME / DB_USER / DB_PASS
 * environment variables instead of using this file.
 */
declare(strict_types=1);

$DB_HOST = 'localhost';
$DB_NAME = 'your_cpanel_db_name';
$DB_USER = 'your_cpanel_db_user';
$DB_PASS = 'your_cpanel_db_password';
