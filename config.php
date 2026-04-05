<?php
declare(strict_types=1);

define('PAYPAL_CLIENT_ID', 'AbNCn--Cmvb40BSzIcnAB0Kfw0HDoqVMsG7cHfgk6HQ-31CNiVhW7BfEvq8iCU3mRiq3w2rvSbTDVt-_');
define('PAYPAL_SECRET', 'EHWpSe-3LS7lWAgKvFSHG34f7AGu8wkL7lBfhf_ACjT_-pgURDopicCzX9zhs6Cej2RSeGmm-PzOSRa4');
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');
define('DOWNLOAD_SECRET', 'change-this-to-a-long-random-secret-string');
define('PRICE', '9.99');
define('CURRENCY', 'EUR');
define('PRODUCT_NAME', 'Fairyland Cottage Book Bundle');

$detectedScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$detectedHost = $_SERVER['HTTP_HOST'] ?? 'fairylandcottage.com';
$baseUrl = getenv('SITE_BASE_URL') ?: ($detectedScheme . '://' . $detectedHost);

define('SUCCESS_URL', $baseUrl . '/success.php');
define('CANCEL_URL', $baseUrl . '/shop.html');

$configuredPrivateDir = getenv('PRIVATE_DOWNLOADS_DIR') ?: '/home/YOUR_SERVER_USER/private_downloads';
$localPrivateDir = __DIR__ . '/private_downloads';
$privateDownloadsDir = is_dir($configuredPrivateDir) ? $configuredPrivateDir : $localPrivateDir;

$bookPrimary = $privateDownloadsDir . '/fairyland-book.pdf';
$audioPrimary = $privateDownloadsDir . '/fairyland-audiobook.wav';

// Local fallback names currently present in this repository.
$bookFallback = $privateDownloadsDir . '/My Journey to Simple, Sustainable Living -  ebook.pdf.pdf';
$audioFallback = $privateDownloadsDir . '/My Journey to Simple, Sustainable Living - Audiobook.wav';

define('PRIVATE_DOWNLOADS_DIR', $privateDownloadsDir);
define('FILE_PATHS', [
    'book' => file_exists($bookPrimary) ? $bookPrimary : $bookFallback,
    'audio' => file_exists($audioPrimary) ? $audioPrimary : $audioFallback,
]);