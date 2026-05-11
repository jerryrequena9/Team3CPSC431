<?php
require_once(__DIR__ . "/../StartSession.php");
require_once(__DIR__ . "/../../pages/html_components.php");
require_once(__DIR__ . "/../helpers.php");

check_valid_user();

if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/team_page.php");
}

global $db;
$player_id = intval($_POST['add_player_id']);
$team_id = intval($_POST['add_team_id']);
$query = "
    INSERT INTO Player_Team (player_id, team_id, start_date, end_date)
    VALUES (?, ?, CURDATE(), NULL)
";
$stmt = prepare_with_perms($db, $query);
$stmt->bind_param("ii", $player_id, $team_id);
try {
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 4025) {
        if (strpos($stmt->error, 'uniq_active_team') !== false) {
            error("Player can only be on one team at a time", "../../pages/team_page.php");
        }
    }
    error("Player not added to team", "../../pages/team_page.php");
} finally {
    $stmt->close();
}
success("Player added to team", "../../pages/team_page.php");
?>
