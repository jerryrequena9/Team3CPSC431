<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

check_valid_user();

if (!is_valid_post($_POST)) {
    display_error_exit("required fields are missing");
}

global $db;
$player_team_id = intval($_POST['remove_player_team_id']);

$query = "
    UPDATE Player_Team
    SET end_date = CURDATE()
    WHERE player_team_id = ?
";
$stmt = prepare_with_perms($db, $query);
if (!$stmt || !$stmt->bind_param("i", $player_team_id) || !$stmt->execute()) {
    display_error_exit("failed to remove player from team");
}
if ($stmt->affected_rows == 0) {
    err_permission_denied();
}
$stmt->close();

header("Location: manage_player_team_page.php");
exit;
?>