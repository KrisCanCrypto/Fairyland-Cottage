<?php
// File: ipn_listener.php
// Purpose: Handle PayPal IPN, verify payment, and email Google Drive download links

// Read POST data from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = [];
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Verify IPN with PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);
$res = curl_exec($ch);
curl_close($ch);

// Check if payment is verified and completed
// Allow a local test mode: if caller includes test=1 we skip PayPal verification
$is_test = isset($myPost['test']) && $myPost['test'] === '1';
if (($is_test) || (strcmp($res, 'VERIFIED') == 0 && isset($myPost['payment_status']) && $myPost['payment_status'] == 'Completed')) {
    $buyer_email = $myPost['payer_email'] ?? 'unknown@example.com';
    $item_name = $myPost['item_name'] ?? 'Purchase';

    // Generate a unique token and expiry (24 hours)
    $token = bin2hex(random_bytes(16));
    $expiry = time() + (24 * 3600);

    // Filenames stored in private_downloads (adjust if you renamed files)
    $pdf_filename = 'My Journey to Simple, Sustainable Living -  ebook.pdf';
    $audio_filename = 'My Journey to Simple, Sustainable Living - Audiobook.wav';

    // Store token, email, expiry and allowed files with per-file download counts
    $downloads_per_file = 3; // allow 3 downloads per file by default
    $files_map = [
        $pdf_filename => $downloads_per_file,
        $audio_filename => $downloads_per_file
    ];

    // Prefer SQLite storage if lib/db.php exists
    if (file_exists(__DIR__ . '/lib/db.php')) {
        require_once __DIR__ . '/lib/db.php';
        init_db();
        insert_token_with_files($token, $buyer_email, $expiry, $files_map);
    } else {
        // Fallback to tokens.txt legacy format
        $files_field = $pdf_filename . ':' . $downloads_per_file . ',' . $audio_filename . ':' . $downloads_per_file;
        $token_data = "$token|$buyer_email|$expiry|$files_field\n";
        file_put_contents('tokens.txt', $token_data, FILE_APPEND | LOCK_EX);
    }

    // Create per-file download URLs
    $host = $_SERVER['HTTP_HOST'] ?? 'fairylandcottage.com';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . $host;
    $pdf_url = $base . '/download.php?token=' . urlencode($token) . '&file=' . urlencode($pdf_filename);
    $audio_url = $base . '/download.php?token=' . urlencode($token) . '&file=' . urlencode($audio_filename);

    // Send email with download links (plain text)
    $subject = "Your Fairyland Cottage Purchase";
    $message = "Thank you for purchasing '$item_name'!\n\n";
    $message .= "Download your files (links expire in 24 hours):\n";
    $message .= "- Ebook (PDF): $pdf_url\n";
    $message .= "- Audiobook (WAV): $audio_url\n\n";
    $message .= "If you have issues, contact info@fairylandcottage.com\n";

    // Ensure logs directory exists and write the raw email to a log for local testing
    if (!is_dir('logs')) mkdir('logs', 0755, true);
    $from = 'info@fairylandcottage.com';
    $log_entry = "[" . date('c') . "] To: $buyer_email\nSubject: $subject\nFrom: $from\n\n$message\n----\n";
    file_put_contents('logs/sent_emails.log', $log_entry, FILE_APPEND | LOCK_EX);

    // If PHPMailer is available and SMTP env vars are set, send via SMTP
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $smtp_host = getenv('SMTP_HOST');
            $smtp_user = getenv('SMTP_USER');
            $smtp_pass = getenv('SMTP_PASS');
            $smtp_port = getenv('SMTP_PORT') ?: 587;
            $smtp_secure = getenv('SMTP_SECURE') ?: 'tls';

            if ($smtp_host && $smtp_user && $smtp_pass) {
                $mail->isSMTP();
                $mail->Host = $smtp_host;
                $mail->SMTPAuth = true;
                $mail->Username = $smtp_user;
                $mail->Password = $smtp_pass;
                $mail->SMTPSecure = $smtp_secure;
                $mail->Port = (int)$smtp_port;
            }

            $mail->setFrom($from, 'Fairyland Cottage');
            $mail->addAddress($buyer_email);
            $mail->addBCC($from);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();
        } catch (Exception $e) {
            // Log the PHPMailer error but continue (we already logged email)
            file_put_contents('logs/sent_emails.log', "[".date('c')."] PHPMailer error: " . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
        }
    } else {
        // Fallback to PHP mail() if available
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "Bcc: $from\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        if (function_exists('mail')) {
            @mail($buyer_email, $subject, $message, $headers);
        }
    }
}
?>