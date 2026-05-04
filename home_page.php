<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

do_html_header('Home');

// Validate session
check_valid_user();
display_user_nav();

// display_teams($db);

// Player, Coach, and Manager can view recent games
// if ($role === 'Player' || $role === 'Coach' || $role === 'Manager') {
//     display_recent_games($db);
// }


do_html_footer();
?>
