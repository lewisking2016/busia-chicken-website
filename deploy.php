<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('SECRET_KEY',  'BusiaFarm2026');
define('REPO_BRANCH', 'main');
define('GIT_BIN',     'git');

$repoPath = __DIR__;

header('Content-Type: text/html; charset=utf-8');

$key = isset($_GET['key']) ? $_GET['key'] : '';
if (!hash_equals(SECRET_KEY, $key)) {
    http_response_code(403);
    echo page_start('Unauthorized', '#ef4444');
    echo '<p style="color:#fca5a5;">Wrong or missing key. Access denied.</p>';
    echo page_end();
    exit;
}

$steps   = [];
$success = true;

// Step 1: Check git
$gitVersion = shell_exec(GIT_BIN . ' --version 2>&1');
if (!$gitVersion) {
    echo page_start('Git Not Found', '#ef4444');
    echo '<p style="color:#fca5a5;">git is not available via shell_exec on this server.</p>';
    echo page_end();
    exit;
}
$steps[] = array('Git found', trim($gitVersion), true);

// Step 2: Find .git folder
if (!is_dir($repoPath . '/.git')) {
    $parent = dirname($repoPath);
    if (is_dir($parent . '/.git')) {
        $repoPath = $parent;
    } else {
        $success = false;
        $steps[] = array('.git not found', 'Looked in: ' . $repoPath . ' and ' . $parent, false);
    }
}
if ($success) {
    $steps[] = array('Repo found', $repoPath, true);
}

// Step 3: git fetch
if ($success) {
    $fetch = shell_exec('cd ' . escapeshellarg($repoPath) . ' && ' . GIT_BIN . ' fetch origin 2>&1');
    $steps[] = array('git fetch', trim($fetch) ? trim($fetch) : '(no output — normal)', true);
}

// Step 4: git pull
if ($success) {
    $pull = shell_exec(
        'cd ' . escapeshellarg($repoPath) .
        ' && ' . GIT_BIN . ' pull origin ' . escapeshellarg(REPO_BRANCH) . ' 2>&1'
    );
    $pullTrimmed = trim($pull);
    $alreadyUpToDate = stripos($pullTrimmed, 'Already up to date') !== false;
    $hasError = stripos($pullTrimmed, 'error') !== false || stripos($pullTrimmed, 'fatal') !== false;

    if ($hasError) {
        $success = false;
        $steps[] = array('git pull FAILED', $pullTrimmed, false);
    } elseif ($alreadyUpToDate) {
        $steps[] = array('git pull', 'Already up to date — nothing new to pull.', true);
    } else {
        $steps[] = array('git pull', $pullTrimmed, true);
    }
}

// Step 5: Show recent commits
if ($success) {
    $commit = shell_exec('cd ' . escapeshellarg($repoPath) . ' && ' . GIT_BIN . ' log --oneline -5 2>&1');
    $steps[] = array('Latest commits', trim($commit), true);
}

$titleText = $success ? 'Deploy Complete' : 'Deploy Failed';
$titleColor = $success ? '#22c55e' : '#ef4444';

echo page_start($titleText, $titleColor);

echo '<table style="width:100%;border-collapse:collapse;">';
foreach ($steps as $step) {
    $label  = $step[0];
    $output = $step[1];
    $ok     = $step[2];
    $icon   = $ok ? '✅' : '❌';
    echo '<tr style="border-top:1px solid #334155;">';
    echo '<td style="padding:10px 8px;white-space:nowrap;width:190px;color:#94a3b8;font-size:13px;vertical-align:top;">' . $icon . ' ' . htmlspecialchars($label) . '</td>';
    echo '<td style="padding:10px 8px;vertical-align:top;"><pre style="background:#0f172a;padding:8px 12px;border-radius:6px;font-size:12px;color:#a3e635;white-space:pre-wrap;word-break:break-all;margin:0;">' . htmlspecialchars($output) . '</pre></td>';
    echo '</tr>';
}
echo '</table>';

if ($success) {
    echo '<div style="margin-top:20px;padding:14px;background:#78350f44;border:1px solid #f59e0b;border-radius:8px;color:#fcd34d;font-size:13px;">';
    echo '<strong>⚠️ IMPORTANT:</strong> Delete <code style="background:#0f172a;padding:2px 6px;border-radius:4px;">deploy.php</code> from your server via cPanel File Manager immediately!';
    echo '</div>';
} else {
    echo '<div style="margin-top:20px;padding:14px;background:#1e3a5f44;border:1px solid #3b82f6;border-radius:8px;color:#93c5fd;font-size:13px;">';
    echo '<strong>💡 Tip:</strong> If git pull failed with authentication error, your server cannot reach GitHub via HTTPS. You may need to set up an SSH deploy key.';
    echo '</div>';
}

echo page_end();

function page_start($title, $color) {
    $icon = ($color === '#22c55e') ? '✅' : '❌';
    $out  = '<!DOCTYPE html><html lang="en"><head>';
    $out .= '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    $out .= '<title>' . $icon . ' ' . htmlspecialchars($title) . '</title>';
    $out .= '<style>';
    $out .= '*{box-sizing:border-box;margin:0;padding:0}';
    $out .= 'body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:2rem}';
    $out .= '.card{max-width:820px;margin:0 auto;background:#1e293b;border-radius:12px;border:1px solid #334155;overflow:hidden}';
    $out .= '.hdr{padding:20px 24px;border-bottom:2px solid ' . $color . ';background:' . $color . '22}';
    $out .= '.hdr h1{font-size:1.2rem;color:' . $color . '}';
    $out .= '.hdr p{font-size:12px;color:#94a3b8;margin-top:4px}';
    $out .= '.body{padding:20px 24px}';
    $out .= '</style></head><body><div class="card">';
    $out .= '<div class="hdr"><h1>' . $icon . ' ' . htmlspecialchars($title) . '</h1>';
    $out .= '<p>Busia Chicken Farm &mdash; Git Deploy</p></div>';
    $out .= '<div class="body">';
    return $out;
}

function page_end() {
    return '</div></div></body></html>';
}
