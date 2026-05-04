<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

check_valid_user();

if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
}

global $db;
$player_id = intval($_POST['add_player_id']);
$team_id = intval($_POST['add_team_id']);

$check_query = "
    SELECT player_team_id
    FROM Player_Team
    WHERE player_id = ?
    AND end_date IS NULL
";
$stmt = prepare_with_perms($db, $check_query);
if (!$stmt || !$stmt->bind_param("i", $player_id) || !$stmt->execute()) {
    display_error_exit("failed to check player team");
}
$stmt->bind_result($existing_id);
$stmt->fetch();
$stmt->free_result();
$stmt->close();

if ($existing_id) {
    display_error_exit("player is already on a team");
}

$query = "
    INSERT INTO Player_Team (player_id, team_id, start_date, end_date)
    VALUES (?, ?, CURDATE(), NULL)
";
$stmt = prepare_with_perms($db, $query);
if (!$stmt || !$stmt->bind_param("ii", $player_id, $team_id) || !$stmt->execute()) {
    display_error_exit("failed to add player to team");
}
$stmt->close();

header("Location: manage_player_team_page.php");
exit;
?>