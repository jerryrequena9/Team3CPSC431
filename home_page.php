<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

do_html_header('Home');

// Validate session
check_valid_user();

// Get current role
$role = $_SESSION['UserRole'];

echo "<h4>Available Data:</h4>";

// Fan, Player, Coach, and Manager can view teams
if ($role === 'Fan' || $role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_teams($db);
}

// Player, Coach, and Manager can view recent games
if ($role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_recent_games($db);
}

// Coach and Manager can view and manipulate player game history
if ($role === 'Coach' || $role === 'Manager') {
    display_player_games($db);
}

if ($role === 'Coach' || $role === 'Manager') {
    echo "<a href='manage_player_team.php'>Manage Player Teams</a><br>";
}

display_user_nav();

do_html_footer();
?>
