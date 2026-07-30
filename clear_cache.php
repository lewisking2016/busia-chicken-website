<?php
/**
 * Temp Cache Reset Utility
 */
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache reset successfully!\n";
} else {
    echo "✗ OPcache reset function not available on this server.\n";
}
