<?php
require_once(__DIR__ . '/../scripts/StartSession.php');
require_once(__DIR__ . '/html_components.php');
require_once(__DIR__ . '/../scripts/helpers.php');

do_html_header('Permission Denied');
check_valid_user();

echo "<p style='color:red; font-weight:bold;'>
        Permission denied. You do not have access to perform this action.
      </p>";

display_user_nav();
do_html_footer();
?>
