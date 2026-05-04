<?php
require("html_components.php");
require("StartSession.php");
require("helpers.php");

do_html_header('Home');

// Validate session
check_valid_user();

// Get role
$role = $_SESSION['UserRole'];

// Display user info
echo "<h3>Welcome, " . $_SESSION['UserName'] . "</h3>";
echo "<p>Your role: <b>$role</b></p><br>";

echo "<h4>Available Data:</h4>";

// Fan feature
if ($role === 'Fan' || $role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_teams($db);
}

// Player feature
if ($role === 'Player' || $role === 'Coach' || $role === 'Manager') {
    display_recent_games($db);
}

// Coach feature
if ($role === 'Coach' || $role === 'Manager') {
    display_player_games($db);
}

// Navigation
echo "<br>";
display_user_nav();

do_html_footer();
?>
