<?php
declare(strict_types=1);

define('PAYPAL_CLIENT_ID', 'AbNCn--Cmvb40BSzIcnAB0Kfw0HDoqVMsG7cHfgk6HQ-31CNiVhW7BfEvq8iCU3mRiq3w2rvSbTDVt-_');
define('PAYPAL_SECRET', 'EHWpSe-3LS7lWAgKvFSHG34f7AGu8wkL7lBfhf_ACjT_-pgURDopicCzX9zhs6Cej2RSeGmm-PzOSRa4');
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');
define('DOWNLOAD_SECRET', 'change-this-to-a-long-random-secret-string');
define('PRICE', '9.99');
define('CURRENCY', 'EUR');
define('PRODUCT_NAME', 'Fairyland Cottage Book Bundle');
define('SUCCESS_URL', 'https://fairylandcottage.com/success.php');
define('CANCEL_URL', 'https://fairylandcottage.com/shop');
define('PRIVATE_DOWNLOADS_DIR', '/home/YOUR_SERVER_USER/private_downloads');
define('FILE_PATHS', [
    'book' => PRIVATE_DOWNLOADS_DIR . '/fairyland-book.pdf',
    'audio' => PRIVATE_DOWNLOADS_DIR . '/fairyland-audiobook.wav',
]);