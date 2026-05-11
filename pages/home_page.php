<?php
require_once(__DIR__ . "/../scripts/StartSession.php");
require_once(__DIR__ . "/html_components.php");
require_once(__DIR__ . "/../scripts/helpers.php");

do_html_header('Home');

// Validate session
check_valid_user();
display_user_nav();

do_html_footer();
?>
