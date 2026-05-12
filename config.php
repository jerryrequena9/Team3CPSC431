<?php
    // Edit base url accordingly
    define('BASE_URL', '/final');

    // These are used for resetting password via email
    // Set the environment variables in .httpd.conf
    // If you are simply testing, you can hardcode them here (WARNING: don't share or push)
    define('EMAIL_USER', getenv('EMAIL_USER') ?: null);
    define('EMAIL_APP_PASSWORD', getenv('EMAIL_APP_PASSWORD') ?: null);
    define('EMAIL_HOST', 'ssl://smtp.gmail.com');
?>