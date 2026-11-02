<?php
/**
 * Hardened Admin Setup & State Lock Gate
 * Clean UI for Locking, Unlocking, and Switching Journals.
 */

declare(strict_types=1);
define('WWJ_EXEC', true);

require_once __DIR__ . '/../config.php';

// Master provisioning secret
$MASTER_SETUP_KEY = 'wwj@MasterLock2026';
$lock_file = __DIR__ . '/../.journal_lock';
$is_locked = file_exists($lock_file);
$locked_journal = $is_locked ? trim(file_get_contents($lock_file)) : '';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_key = trim($_POST['setup_key'] ?? '');
    $action = $_POST['action'] ?? 'lock';

    if ($input_key !== $MASTER_SETUP_KEY) {
        $message = 'Access Denied: Invalid Master Setup Key.';
        $message_type = 'error';
    } elseif ($action === 'unlock') {
        if (file_exists($lock_file)) {
            unlink($lock_file);
        }
        $is_locked = false;
        $locked_journal = '';
        $message = 'System Unlocked! The domain is now unassigned and ready for setup.';
        $message_type = 'success';
    } elseif ($action === 'lock') {
        $selected_journal = trim($_POST['journal_choice'] ?? '');
        if (!isset($JOURNALS[$selected_journal])) {
            $message = 'Error: Invalid Journal Selected.';
            $message_type = 'error';
        } else {
            file_put_contents($lock_file, $selected_journal, LOCK_EX);
            @chmod($lock_file, 0644);
            $is_locked = true;
            $locked_journal = $selected_journal;
            $message = "Domain successfully locked to: {$JOURNALS[$selected_journal]['title']} ({$JOURNALS[$selected_journal]['code']})";
            $message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WWJ Journal Lock & Provisioning Gate</title>
    <style>
        :root { --primary: #0a4b78; --danger: #c0392b; --success: #27ae60; --bg: #f0f3f6; --card: #ffffff; }
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: var(--bg); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: var(--card); width: 100%; max-width: 520px; padding: 35px; border-radius: 8px; box-shadow: 0 4px 18px rgba(0,0,0,0.1); border: 1px solid #dce4ec; }
        h1 { font-size: 20px; color: #1a202c; margin-top: 0; text-align: center; font-weight: 700; }
        p.subtitle { font-size: 13px; color: #718096; text-align: center; margin-bottom: 22px; }
        .status-badge { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-bottom: 22px; }
        .status-locked { background: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
        .status-unlocked { background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }
        .badge-pill { font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 700; text-transform: uppercase; }
        .status-locked .badge-pill { background: #137333; color: #fff; }
        .status-unlocked .badge-pill { background: #c5221f; color: #fff; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #2d3748; }
        select, input[type="password"] { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; color: #2d3748; background: #fff; }
        select:focus, input:focus { border-color: var(--primary); outline: none; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-lock { background: var(--primary); color: #fff; margin-top: 6px; }
        .btn-unlock { background: var(--danger); color: #fff; margin-top: 10px; }
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; }
        .alert-error { background: #fce8e6; color: #a51d24; border: 1px solid #fad2cf; }
        .alert-success { background: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
        .journal-meta-box { background: #f7fafc; border: 1px dashed #cbd5e0; padding: 12px 14px; border-radius: 6px; margin-bottom: 18px; font-size: 13px; color: #4a5568; }
        .journal-meta-box strong { color: #1a202c; }
        .links { margin-top: 25px; text-align: center; display: flex; justify-content: center; gap: 20px; font-size: 13px; }
        .links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <h1>Journal Provisioning Gate</h1>
    <p class="subtitle">Dynamic state lock controller for 4 isolated journal databases</p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="status-badge <?= $is_locked ? 'status-locked' : 'status-unlocked' ?>">
        <span>
            <?= $is_locked ? 'Active: ' . htmlspecialchars($JOURNALS[$locked_journal]['title'] ?? $locked_journal) : 'No Journal Locked' ?>
        </span>
        <span class="badge-pill"><?= $is_locked ? 'LOCKED' : 'UNLOCKED' ?></span>
    </div>

    <?php if ($is_locked && isset($JOURNALS[$locked_journal])): ?>
        <div class="journal-meta-box">
            <div><strong>Active Code:</strong> <?= $JOURNALS[$locked_journal]['code'] ?></div>
            <div><strong>ISSN:</strong> <?= $JOURNALS[$locked_journal]['issn'] ?></div>
            <div><strong>Database Pool:</strong> <?= $JOURNALS[$locked_journal]['db']['name'] ?></div>
            <div><strong>Upload Route:</strong> <?= $JOURNALS[$locked_journal]['upload_dir'] ?></div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="lock">
        
        <div class="form-group">
            <label for="journal_choice">Target Journal to Bind:</label>
            <select name="journal_choice" id="journal_choice" required>
                <?php foreach ($JOURNALS as $code => $details): ?>
                    <option value="<?= $code ?>" <?= ($locked_journal === $code) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($details['title']) ?> (<?= $details['code'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="setup_key">Master Setup Key (Auth Required):</label>
            <input type="password" name="setup_key" id="setup_key" placeholder="Enter master key to confirm" required>
        </div>

        <button type="submit" class="btn btn-lock">
            <?= $is_locked ? 'Switch / Re-Lock Journal' : 'Lock & Activate Journal' ?>
        </button>
    </form>

    <?php if ($is_locked): ?>
        <form method="POST" style="margin-top: 10px;" onsubmit="return confirm('Are you sure you want to unlock? Site will pause until re-locked.');">
            <input type="hidden" name="action" value="unlock">
            <input type="hidden" name="setup_key" id="unlock_key_input">
            <button type="button" class="btn btn-unlock" onclick="
                const key = prompt('Enter Master Setup Key to UNLOCK:');
                if (key) {
                    document.getElementById('unlock_key_input').value = key;
                    this.form.submit();
                }
            ">
                Unlock System (Release Domain)
            </button>
        </form>
    <?php endif; ?>

    <div class="links">
        <a href="/page/u/upload-your-article" target="_blank">&rarr; View Upload Form</a>
        <a href="/page/p/author-guidelines" target="_blank">&rarr; View Guidelines</a>
        <a href="/page/p/downloads" target="_blank">&rarr; View Downloads</a>
    </div>
</div>

</body>
</html>
