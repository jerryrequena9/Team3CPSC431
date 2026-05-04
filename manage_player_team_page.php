<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

do_html_header("Manage Player Teams");
check_valid_user();
display_user_nav();

echo "<h3>Coach/Manager Use Case: Add or Remove Players from Teams</h3>";

display_add_player_form();
display_current_assignments();

do_html_footer();

function display_add_player_form() {
    global $db;

    $query = "
        SELECT player_id, first_name, last_name, position
        FROM Player
        ORDER BY last_name, first_name
    ";
    $players = query_with_perms($db, $query);

    $query = "
        SELECT team_id, name, city
        FROM Team
        ORDER BY city, name
    ";
    $teams = query_with_perms($db, $query);

    echo "<h4>Add Player to Team</h4>";
    echo "<form method='post' action='add_player_team.php'>";

    echo "<label>Select Player:</label><br>";
    echo "<select name='add_player_id' required>";
    echo "<option value=''>-- Select Player --</option>";
    while ($player = $players->fetch_assoc()) {
        echo "<option value='" . sanitize_str($player['player_id']) . "'>";
        echo sanitize_str($player['first_name'] . " " . $player['last_name'] . " (" . $player['position'] . ")");
        echo "</option>";
    }
    echo "</select><br><br>";

    echo "<label>Select Team:</label><br>";
    echo "<select name='add_team_id' required>";
    echo "<option value=''>-- Select Team --</option>";
    while ($team = $teams->fetch_assoc()) {
        echo "<option value='" . sanitize_str($team['team_id']) . "'>";
        echo sanitize_str($team['city'] . " " . $team['name']);
        echo "</option>";
    }
    echo "</select><br><br>";

    echo "<input type='submit' value='Add Player to Team'>";
    echo "</form><br>";
}

function display_current_assignments() {
    global $db;

    $query = "
        SELECT
            pt.player_team_id,
            p.first_name,
            p.last_name,
            p.position,
            t.name AS team_name,
            t.city AS team_city,
            pt.start_date,
            pt.end_date
        FROM Player_Team pt
        JOIN Player p ON pt.player_id = p.player_id
        JOIN Team t ON pt.team_id = t.team_id
        ORDER BY t.city, t.name, p.last_name
    ";
    $result = query_with_perms($db, $query);

    if (!$result) {
        echo "Error loading player team data: " . sanitize_str($db->error);
        return;
    }

    echo "<h4>Current Player Team Assignments</h4>";
    echo "<table>";
    echo "<tr>
            <th>Player</th>
            <th>Position</th>
            <th>Team</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Action</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . sanitize_str($row['first_name'] . " " . $row['last_name']) . "</td>";
        echo "<td>" . sanitize_str($row['position']) . "</td>";
        echo "<td>" . sanitize_str($row['team_name']) . "</td>";
        echo "<td>" . sanitize_str($row['start_date']) . "</td>";
        echo "<td>" . sanitize_str($row['end_date'] ?? 'Active') . "</td>";
        echo "<td>";
        if ($row['end_date'] === null) {
            echo "<form method='post' action='remove_player_team.php'>";
            echo "<input type='hidden' name='remove_player_team_id' value='" . sanitize_str($row['player_team_id']) . "'>";
            echo "<input type='submit' value='Remove from Team'>";
            echo "</form>";
        } else {
            echo "Removed";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>