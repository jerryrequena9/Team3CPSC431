<?php
require_once(__DIR__ . "/../StartSession.php");
require_once(__DIR__ . "/../../pages/html_components.php");
require_once(__DIR__ . "/../helpers.php");

check_valid_user();

if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/team_page.php");
}

global $db;
$player_team_id = intval($_POST['remove_player_team_id']);

$query = "
    UPDATE Player_Team
    SET end_date = CURDATE()
    WHERE player_team_id = ?
";
$stmt = prepare_with_perms($db, $query);
$stmt->bind_param("i", $player_team_id);
try {
    $stmt->execute();
    if ($stmt->affected_rows == 0) {
        error("Player not removed from team", "../../pages/team_page.php");
    }
} catch (mysqli_sql_exception $e) {
    error("Player not removed from team", "../../pages/team_page.php");
} finally {
    $stmt->close();
}
success("Player removed from team", "../../pages/team_page.php");
?>
