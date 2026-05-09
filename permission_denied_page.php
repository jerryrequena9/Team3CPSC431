<?php
require_once('StartSession.php');
require_once('html_components.php');
require_once('helpers.php');

do_html_header('Permission Denied');
check_valid_user();

echo "<p style='color:red; font-weight:bold;'>
        Permission denied. Your database role does not have access to perform this action.
      </p>";

echo "<p>
        This access check is enforced by MySQL permissions, not just the PHP page.
      </p>";

display_user_nav();
do_html_footer();
?>
