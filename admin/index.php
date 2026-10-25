<?php
/**
 * Busia Chicken Farm — Admin Entry Shim
 * Redirects /admin to /Frontend/admin/login.php
 */
declare(strict_types=1);

header('Location: /Frontend/admin/login.php', true, 302);
exit;
