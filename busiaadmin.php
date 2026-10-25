<?php
/**
 * Busia Chicken Farm — Admin Entry Shim
 * Redirects to the admin login portal
 */
declare(strict_types=1);

header('Location: /Frontend/admin/login.php', true, 302);
exit;
