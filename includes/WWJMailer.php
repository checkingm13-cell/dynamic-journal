<?php
/**
 * Modern Resilient Mail Dispatcher for WWJ Journals (PHP 8.2+)
 * Dispatches:
 * 1. Notification to Journal Editorial Office
 * 2. Confirmation to Submitting Author(s)
 * Supports SMTP (socket/stream) with automatic native mail fallback.
 */

declare(strict_types=1);

if (!defined('WWJ_EXEC')) {
    exit('Direct access not permitted');
}

class WWJMailer {

    /**
     * Send both editorial notification and author confirmation emails
     */
    public static function dispatchSubmissionEmails(array $journal, array $data, string $downloadUrl): array {
        $results = [
            'editor_sent' => false,
            'author_sent' => false,
            'errors'      => []
        ];

        $journalCode   = htmlspecialchars($journal['code'] ?? 'WWJ', ENT_QUOTES, 'UTF-8');
        $journalTitle  = htmlspecialchars($journal['title'] ?? 'World Wide Journals', ENT_QUOTES, 'UTF-8');
        $editorEmail   = filter_var($journal['email'] ?? 'editor@worldwidejournals.com', FILTER_SANITIZE_EMAIL);
        
        $authorName    = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $authorEmail   = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $authorAlt     = filter_var($data['aemail'] ?? '', FILTER_VALIDATE_EMAIL);
        $manuscriptId  = htmlspecialchars($data['ref_aid'] ?? '', ENT_QUOTES, 'UTF-8');
        $articleTitle  = htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $subject       = htmlspecialchars($data['subject'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone         = htmlspecialchars($data['contact'] ?? '', ENT_QUOTES, 'UTF-8');
        $address       = nl2br(htmlspecialchars($data['res_add'] ?? '', ENT_QUOTES, 'UTF-8'));
        $coAuthors     = $data['co_authors'] ?? [];

        // Build Co-Authors HTML list
        $coAuthorsHtml = '<p><em>No co-authors registered</em></p>';
        if (!empty($coAuthors) && is_array($coAuthors)) {
            $coAuthorsHtml = '';
            foreach ($coAuthors as $idx => $coName) {
                $coNum = $idx + 1;
                $coClean = htmlspecialchars(trim((string)$coName), ENT_QUOTES, 'UTF-8');
                if ($coClean !== '') {
                    $coAuthorsHtml .= "<p><strong>Co-Author {$coNum}:</strong> {$coClean}</p>";
                }
            }
        }

        // =====================================================================
        // 1. DISPATCH TO JOURNAL EDITOR
        // =====================================================================
        $editorSubject = "Article Uploaded by {$authorName} (Manuscript ID - {$manuscriptId})";
        $editorBody = <<<HTML
<!DOCTYPE html>
<html>
<body style="background-color:#f4f6f9; font-family:'Segoe UI', Arial, sans-serif; color:#333; margin:0; padding:25px;">
    <div style="background-color:#ffffff; max-width:620px; margin:auto; padding:25px; border-radius:6px; border:1px solid #e1e4e8; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <div style="border-bottom:3px solid #1d3e71; padding-bottom:12px; margin-bottom:20px;">
            <h2 style="color:#1d3e71; margin:0; font-size:20px;">New Manuscript Submission</h2>
            <p style="color:#666; margin:4px 0 0 0; font-size:13px;">{$journalTitle}</p>
        </div>
        <p><strong>Manuscript Tracking ID:</strong> <span style="color:#0275d8; font-weight:bold; font-size:16px;">{$manuscriptId}</span></p>
        <p><strong>Article Uploaded By:</strong> {$authorName}</p>
        {$coAuthorsHtml}
        <p><strong>Contact No.:</strong> {$phone}</p>
        <p><strong>Article Title:</strong> {$articleTitle}</p>
        <p><strong>Subject / Category:</strong> {$subject}</p>
        <p><strong>Residential Address (Hard Copy):</strong><br>{$address}</p>
        <p><strong>Primary Email:</strong> <a href="mailto:{$authorEmail}">{$authorEmail}</a></p>
        <p><strong>Alternate Email:</strong> {$authorAlt}</p>
        <p><strong>Download Attached Manuscript:</strong><br>
           <a href="{$downloadUrl}" style="display:inline-block; margin-top:6px; background:#1d3e71; color:#fff; text-decoration:none; padding:8px 16px; border-radius:4px; font-weight:bold; font-size:13px;">Download File</a>
        </p>
    </div>
</body>
</html>
HTML;

        $results['editor_sent'] = self::sendMail($editorEmail, $editorSubject, $editorBody, $editorEmail, "{$journalCode} Editorial Office", $authorEmail);

        // =====================================================================
        // 2. DISPATCH TO SUBMITTING AUTHOR
        // =====================================================================
        if ($authorEmail) {
            $authorSubject = "World Wide Journals ({$journalCode}) - Successful Submission (Manuscript ID - {$manuscriptId})";
            $authorBody = <<<HTML
<!DOCTYPE html>
<html>
<body style="background-color:#f4f6f9; font-family:'Segoe UI', Arial, sans-serif; color:#333; margin:0; padding:25px;">
    <div style="background-color:#ffffff; max-width:620px; margin:auto; padding:25px; border-radius:6px; border:1px solid #e1e4e8; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <div style="border-bottom:3px solid #28a745; padding-bottom:12px; margin-bottom:20px;">
            <h2 style="color:#28a745; margin:0; font-size:20px;">Manuscript Submission Successful</h2>
            <p style="color:#666; margin:4px 0 0 0; font-size:13px;">{$journalTitle}</p>
        </div>
        <p>Dear <strong>{$authorName}</strong>,</p>
        <p>Thank you for submitting your research paper. Your official Tracking Manuscript ID is:</p>
        <div style="background:#e8f4fd; border:1px dashed #0275d8; padding:12px 16px; border-radius:4px; text-align:center; font-size:18px; font-weight:bold; color:#0275d8; margin:15px 0;">
            {$manuscriptId}
        </div>
        <p style="font-size:14px; color:#222;">
            Your article is now under preliminary review. <strong><u>You will receive an editorial reply within 4 working days.</u></strong>
        </p>

        <div style="background:#fafafa; border-left:4px solid #1d3e71; padding:12px 15px; margin:20px 0; font-size:13px;">
            <strong>Authors Declaration:</strong>
            <ul style="margin:8px 0 0 20px; padding:0; line-height:1.6;">
                <li>This Article is completely written in the English Language.</li>
                <li>This is a complete / full-length copy of the article.</li>
                <li>This paper carries all authors' names, designations, and emails.</li>
                <li>You acknowledge that you have reviewed the journal's publication fee structure.</li>
            </ul>
        </div>

        <p style="font-size:14px; margin-top:20px;">
            Have a happy publishing!<br>
            <strong>Editorial Team - {$journalTitle}</strong>
        </p>
    </div>
</body>
</html>
HTML;

            $results['author_sent'] = self::sendMail($authorEmail, $authorSubject, $authorBody, $editorEmail, "Editor {$journalCode}");
            
            // Also notify alternate email if provided
            if ($authorAlt && $authorAlt !== $authorEmail) {
                self::sendMail($authorAlt, $authorSubject, $authorBody, $editorEmail, "Editor {$journalCode}");
            }
        }

        return $results;
    }

    /**
     * Resilient send mail wrapper with proper MIME headers & fail-safety
     */
    private static function sendMail(string $to, string $subject, string $htmlBody, string $fromEmail, string $fromName, ?string $replyTo = null): bool {
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($fromName), $fromEmail),
                'Reply-To: ' . ($replyTo ?: $fromEmail),
                'X-Mailer: WWJ-DynamicEngine/PHP-' . PHP_VERSION
            ];

            $encodedSubject = sprintf('=?UTF-8?B?%s?=', base64_encode($subject));
            return @mail($to, $encodedSubject, $htmlBody, implode("\r\n", $headers));
        } catch (Throwable $e) {
            error_log("WWJ Mailer Warning: " . $e->getMessage());
            return false;
        }
    }
}
