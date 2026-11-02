<?php
/**
 * Manuscript Submission Success Page (Matching live WWJ view.php?p=success)
 */

declare(strict_types=1);
if (!defined('WWJ_EXEC')) {
    define('WWJ_EXEC', true);
    require_once __DIR__ . '/../../config.php';
}

$ref_aid = htmlspecialchars($_GET['ref'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
$journal_title = htmlspecialchars($current_journal['title'] ?? 'World Wide Journals', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manuscript Submission Successful - <?= $journal_title ?></title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f6f9; color: #333; line-height: 1.6; }
        .success-container {
            max-width: 760px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-top: 6px solid #28a745;
            overflow: hidden;
        }
        .header-bar {
            background: #1d3e71;
            color: #ffffff;
            padding: 24px 30px;
            text-align: center;
        }
        .header-bar h2 { font-size: 22px; font-weight: 600; }
        .body-content { padding: 35px 40px; }
        .icon-circle {
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            background: #d4edda;
            color: #28a745;
            font-size: 34px;
            text-align: center;
            margin: 0 auto 20px;
        }
        .manuscript-badge {
            background: #e8f4fd;
            border: 1px dashed #0275d8;
            padding: 15px 20px;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
            font-size: 17px;
        }
        .manuscript-badge strong { color: #0275d8; font-size: 20px; }
        .info-list { margin: 20px 0; padding-left: 20px; }
        .info-list li { margin-bottom: 10px; font-size: 14px; }
        .action-btn {
            display: inline-block;
            background: #1d3e71;
            color: #ffffff;
            padding: 12px 28px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: background 0.3s ease;
        }
        .action-btn:hover { background: #132a4e; color: #fff; }
        .footer-note {
            background: #fafafa;
            border-top: 1px solid #eee;
            padding: 15px 40px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="header-bar">
        <h2><?= $journal_title ?></h2>
    </div>
    
    <div class="body-content">
        <div class="icon-circle">
            <i class="fa fa-check"></i>
        </div>
        
        <h3 style="text-align:center; color:#28a745; margin-bottom: 10px;">Submission Successful!</h3>
        <p style="text-align:center; color:#555;">Thank you for submitting your research paper to <strong><?= $journal_title ?></strong>.</p>
        
        <div class="manuscript-badge">
            Your Tracking Manuscript ID: <br>
            <strong><?= $ref_aid ?></strong>
        </div>

        <ul class="info-list">
            <li><strong>Under Review:</strong> Your manuscript has been submitted into our editorial system.</li>
            <li><strong>Peer Review Notice:</strong> You will receive a response regarding the review status within <strong>4 working days</strong>.</li>
            <li><strong>Record Logged:</strong> All article details and attached files have been recorded in the central editorial cloud database.</li>
        </ul>

        <div style="text-align:center;">
            <a href="/upload-your-article" class="action-btn"><i class="fa fa-arrow-left"></i> Submit Another Article</a>
            <a href="/author-guidelines" class="action-btn" style="background:#6c757d; margin-left:10px;"><i class="fa fa-book"></i> Author Guidelines</a>
        </div>
    </div>

    <div class="footer-note">
        Have a happy publishing! &bull; Editorial Team
    </div>
</div>

</body>
</html>
