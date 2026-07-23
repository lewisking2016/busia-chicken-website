<?php
/**
 * Root entry point - serves the Frontend homepage directly.
 * On cPanel with Apache, .htaccess handles routing.
 * On PHP built-in dev server, this file includes Frontend/index.php.
 */
declare(strict_types=1);

include __DIR__ . '/Frontend/index.php';
