<?php
require("StartSession.php");
require("html_components.php");
require("helpers.php");

do_html_header('Home');

// Validate session
check_valid_user();

// Get current role
$role = $_SESSION['UserRole'];

// Display user info
echo "<h3>Welcome, " . htmlspecialchars($_SESSION['UserName']) . "</h3>";
echo "<p>Your role: <b>" . htmlspecialchars($role) . "</b></p><br>";

echo "<h4>Available Data:</h4>";

// Fan, Player, Coach, and Manager can view teams
if ($role === 'Fan' || $role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_teams($db);
}

// Player, Coach, and Manager can view recent games
if ($role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_recent_games($db);
}

// Coach and Manager can view player game history
if ($role === 'Coach' || $role === 'Manager') {
    display_player_games($db);
}

echo "<br>";
display_user_nav();

do_html_footer();
?>
