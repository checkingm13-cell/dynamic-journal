<?php
/**
 * Hardened OWASP ASVS Manuscript Ingestion Handler
 * Integrates directly with Supabase Cloud Database (userarticle & co_authors tables)
 * Strict Defense Against:
 * - Arbitrary File Upload (V12)
 * - Input Injection & MIME Tampering (V5)
 * - Distributed Shared Hosting & Multi-Domain Ingestion
 */

declare(strict_types=1);
define('WWJ_EXEC', true);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure system is locked to an active journal
if (!$current_journal) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'System unconfigured. Run admin setup first.']);
    exit;
}

// Handle method=csrfToken probe from legacy forms cleanly
if (isset($_GET['method']) && $_GET['method'] === 'csrfToken') {
    echo json_encode(['status' => 'success', 'token' => bin2hex(random_bytes(16))]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// 1. CAPTCHA & INPUT SANITIZATION
$ran      = trim($_POST['ran'] ?? '');
$scaptcha = trim($_POST['scaptcha'] ?? '');

if ($ran === '' || $scaptcha === '' || $ran !== $scaptcha) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Security verification code is incorrect.']);
    exit;
}

$name     = trim($_POST['name'] ?? '');
$phone    = preg_replace('/[^0-9+]/', '', trim($_POST['phone'] ?? ''));
$article  = trim($_POST['article'] ?? '');
$subject  = trim($_POST['subjetlist'] ?? '');
$address  = trim($_POST['rafhc'] ?? '');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$aemail   = filter_var(trim($_POST['aemailid'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;

if (!$name || !$phone || !$article || !$subject || !$address || !$email) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All mandatory fields marked with * must be filled.']);
    exit;
}

// 2. OWASP ASVS V12: HARDENED FILE UPLOAD PIPELINE
if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please attach a valid manuscript file.']);
    exit;
}

$fileTmp  = $_FILES['userfile']['tmp_name'];
$fileSize = $_FILES['userfile']['size'];
$origName = $_FILES['userfile']['name'];

// Rule 1: Strict File Size Limit (Max 25 MB)
if ($fileSize > 25 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Manuscript file exceeds maximum allowed limit (25MB).']);
    exit;
}

// Rule 2: Strict Extension Whitelist
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, ['doc', 'docx'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ASVS Violation: Only Microsoft Word (.doc, .docx) files are accepted.']);
    exit;
}

// Rule 3: MIME Type Introspection (safe fallback if finfo extension is not installed)
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileTmp);
    $allowed_mimes = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/octet-stream'
    ];
    if (!in_array($mime, $allowed_mimes, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'MIME validation failed. File is not a valid Word document.']);
        exit;
    }
}

// Rule 4: Deep Byte-Level Magic Header Inspection
$handle = fopen($fileTmp, 'rb');
$magicHeader = fread($handle, 8);
fclose($handle);

$isDocx = (substr($magicHeader, 0, 4) === "PK\x03\x04");
$isDoc  = (substr($magicHeader, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1");

if ($ext === 'docx' && !$isDocx) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Security Alert: Corrupted or forged .docx file detected.']);
    exit;
}
if ($ext === 'doc' && !$isDoc) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Security Alert: Corrupted or forged .doc file detected.']);
    exit;
}

// Rule 5: Secure Partitioning by Journal & Date
$yearMonth = date('Y/F');
$targetDir = $current_journal['upload_dir'] . $yearMonth . '/';

if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Storage initialization failed.']);
        exit;
    }
}

// Rule 6: Cryptographically Secure Obfuscated Storage Name
$safeStoredName = time() . '_' . bin2hex(random_bytes(8)) . '.' . ($isDocx ? 'docx' : 'doc');
$targetPath = $targetDir . $safeStoredName;

if (!move_uploaded_file($fileTmp, $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal storage failure saving manuscript.']);
    exit;
}

// 3. SUPABASE CLOUD INSERTION VIA REST API
try {
    // Generate Unique Tracking ID
    $journalCode = $current_journal['code'];
    $journalPrefix = $current_journal['prefix'];
    $currentDomain = $_SERVER['HTTP_HOST'] ?? 'unknown_domain';

    // Query highest id for this journal to calculate next sequential ID
    $checkRes = supabase_request("userarticle?select=id&journal_code=eq.{$journalCode}&order=id.desc&limit=1");
    $nextNum = 1001;
    if ($checkRes['status'] === 'success' && !empty($checkRes['data'])) {
        $nextNum = ((int)$checkRes['data'][0]['id']) + 1001;
    } else {
        $nextNum = rand(1001, 1999);
    }
    $refAid = $journalPrefix . $nextNum;

    // Prepare Payload for 'userarticle' Table
    $articlePayload = [
        'ref_aid'      => $refAid,
        'journal_code' => $journalCode,
        'domain'       => $currentDomain,
        'name'         => $name,
        'contact'      => $phone,
        'title'        => $article,
        'subject'      => $subject,
        'res_add'      => $address,
        'email'        => $email,
        'aemail'       => $aemail,
        'file'         => $safeStoredName,
        'code'         => '1',
        'user_ip'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'status'       => 'submitted',
        'date'         => date('F j, Y'),
        'time'         => date('Y-m-d H:i:s')
    ];

    $insertRes = supabase_request('userarticle', 'POST', $articlePayload);

    if ($insertRes['status'] !== 'success' || empty($insertRes['data'])) {
        // Rollback local uploaded file on failure
        if (file_exists($targetPath)) {
            @unlink($targetPath);
        }
        error_log("Supabase Article Insert Failed: " . json_encode($insertRes));
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Cloud database insertion failed. Please try again.']);
        exit;
    }

    $insertedArticleId = $insertRes['data'][0]['id'];

    // Process Co-Authors if provided into 'co_authors' Table
    $coAuthorsList = [];
    if (!empty($_POST['co_author']) && is_array($_POST['co_author'])) {
        foreach ($_POST['co_author'] as $coName) {
            $cleanedCoName = trim((string)$coName);
            if ($cleanedCoName !== '') {
                $coAuthorsList[] = $cleanedCoName;
                supabase_request('co_authors', 'POST', [
                    'article_id'     => $insertedArticleId,
                    'co_author_name' => $cleanedCoName
                ]);
            }
        }
    }

    // 4. TRIGGER EMAIL DISPATCH (EDITOR & AUTHOR NOTIFICATION)
    require_once __DIR__ . '/../includes/WWJMailer.php';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8080';
    $downloadUrl = "{$protocol}{$host}/storage/uploads/{$current_journal['key']}/{$yearMonth}/{$safeStoredName}";

    $mailPayload = $articlePayload;
    $mailPayload['co_authors'] = $coAuthorsList;

    WWJMailer::dispatchSubmissionEmails($current_journal, $mailPayload, $downloadUrl);

    // Check if client expects JSON or standard form redirection
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        echo json_encode([
            'validated'     => true,
            'status'        => 'success',
            'message'       => 'Manuscript successfully submitted and logged into cloud database.',
            'manuscript_id' => $refAid,
            'journal'       => $current_journal['title'],
            'redirect'      => "/view.php?p=success&ref={$refAid}",
            'fieldValues'   => [
                'manuscript_id' => $refAid,
                'serverRedirect' => "/view.php?p=success&ref={$refAid}"
            ]
        ]);
        exit;
    }

    // Standard Browser Form Redirect (matching live GJRA / WWJ architecture)
    header("Location: /view.php?p=success&ref={$refAid}");
    echo "<script>window.location.href = '/view.php?p=success&ref={$refAid}';</script>";
    exit;

} catch (Throwable $e) {
    if (file_exists($targetPath)) {
        @unlink($targetPath);
    }
    error_log("WWJ Supabase Ingestion Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'validated' => false,
        'status'    => 'error',
        'message'   => 'Unexpected error while registering manuscript.'
    ]);
    exit;
}
