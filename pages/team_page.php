<?php
    require_once(__DIR__ . "/../scripts/StartSession.php");
    require_once(__DIR__ . "/html_components.php");
    require_once(__DIR__ . "/../scripts/helpers.php");

    do_html_header("Manage Teams");
    check_valid_user();
    display_user_nav();

    display_add_team_form();
    display_teams_table();
    display_add_player_form();
    display_current_assignments();
    display_team_roster();

    do_html_footer();

    function display_add_team_form() {
        global $db;

        // Get list of stadiums
        $query = "
            SELECT stadium_id, name, city
            FROM Stadium
            ORDER BY name, city
        ";
        $stadiums = query_with_perms($db, $query);

        echo "<h2>Add Team</h2>";
        echo "<form method='post' action='../scripts/team/add_team.php'>";
        echo "<label>Name:</label><br><input type='text' name='name' required><br><br>";
        echo "<label>City:</label><br><input type='text' name='city' required><br><br>";

        /*
         * Conference and division are controlled values.
         * These are acceptable here because they match the allowed domain values
         * of the football league model.
         */
        echo "<label>Conference:</label><br>
            <select name='conference' required>
                <option value=''>-- Select --</option>
                <option value='NFC'>NFC</option>
                <option value='AFC'>AFC</option>
            </select><br><br>";

        echo "<label>Division:</label><br>
            <select name='division' required>
                <option value=''>-- Select --</option>
                <option value='North'>North</option>
                <option value='South'>South</option>
                <option value='East'>East</option>
                <option value='West'>West</option>
            </select><br><br>";

        echo "<label>Stadium:</label><br>
            <select name='stadium_id'>
                <option value=''>-- None --</option>";

        // Display stadiums
        while ($s = $stadiums->fetch_assoc()) {
            echo "<option value='" . intval($s['stadium_id']) . "'>" .
                 sanitize_str($s['city'] . " - " . $s['name']) .
                 "</option>";
        }

        echo "</select><br><br>";
        echo "<input type='submit' value='Add Team'>";
        echo "</form><br>";
    }

    function display_teams_table() {
        global $db;

        // Get team information
        $query = "
            SELECT
                t.team_id,
                t.name,
                t.city, 
                t.conference,
                t.division,
                t.stadium_id
            FROM Team t
            ORDER BY t.city, t.name
        ";
        $teams = query_with_perms($db, $query);

        // Get stadium information
        $query = "
            SELECT stadium_id, name, city
            FROM Stadium
            ORDER BY name, city
        ";
        $stadiums = query_with_perms($db, $query);

        echo "<h2>View and Edit Teams</h2>";
        echo "<table>";
        echo "<tr>
                <th>Name</th>
                <th>City</th>
                <th>Conference</th>
                <th>Division</th>
                <th>Stadium</th>
                <th>Coaches</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>";

        while ($row = $teams->fetch_assoc()) {
            $team_id = intval($row['team_id']);

            echo "<tr>";

            // Display name and city of teams
            echo "<form method='post' action='../scripts/team/edit_team.php'>";
            echo "<input type='hidden' name='team_id' value='$team_id'>";
            echo "<td><input type='text' name='name' value='" . sanitize_str($row['name']) . "' required></td>";
            echo "<td><input type='text' name='city' value='" . sanitize_str($row['city']) . "' required></td>";

            // Display conference
            echo "<td>
                    <select name='conference' required>
                        <option value='NFC'" . ($row['conference'] == 'NFC' ? ' selected' : '') . ">NFC</option>
                        <option value='AFC'" . ($row['conference'] == 'AFC' ? ' selected' : '') . ">AFC</option>
                    </select>
                </td>";

            // Display division
            echo "<td>
                    <select name='division' required>
                        <option value='North'" . ($row['division'] == 'North' ? ' selected' : '') . ">North</option>
                        <option value='South'" . ($row['division'] == 'South' ? ' selected' : '') . ">South</option>
                        <option value='East'" . ($row['division'] == 'East' ? ' selected' : '') . ">East</option>
                        <option value='West'" . ($row['division'] == 'West' ? ' selected' : '') . ">West</option>
                    </select>
                </td>";

            // Display stadiums
            echo "<td>
                    <select name='stadium_id' required>";
                        foreach ($stadiums as $stadium) {
                            $stadium_id = intval($stadium['stadium_id']);
                            $stadium_name = sanitize_str($stadium['city'] . " - " . $stadium['name']);
                            $selected = ($row['stadium_id'] === $stadium['stadium_id']) ? " selected" : "";
                            echo "<option value='$stadium_id' $selected>$stadium_name</option>";
                        }
            echo   "</select>
                </td>";

            echo "<td>";
                // Get the coaches of the current team
                $query = "
                    SELECT first_name, last_name
                    FROM Coach
                    WHERE team_id = ?
                    ORDER BY last_name, first_name
                ";
                $stmt = prepare_with_perms($db, $query);
                $stmt->bind_param("i", $team_id);
                try {
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($coach = $result->fetch_assoc()) {
                        // Display the name of the coach
                        echo sanitize_str($coach['first_name']) . " " . sanitize_str($coach['last_name']) . "<br>";
                    }
                } catch (mysqli_sql_exception $e) {
                } finally {
                    $stmt->close();
                }
            echo "</td>";

            echo "<td><input type='submit' value='Edit'></td>";
            echo "</form>";

            echo "<td>
                    <form method='post' action='../scripts/team/delete_team.php'>
                    <input type='hidden' name='team_id' value='$team_id'>
                    <input type='submit' value='Delete'>
                    </form>
                </td>";

            echo "</tr>";
        }

        echo "</table>";
    }

    function display_team_roster() {
        global $db;

        // Fetch all teams
        $query = "
            SELECT team_id, name
            FROM Team
            ORDER BY city, name
        ";
        $teams_result = query_with_perms($db, $query);

        echo "<h2>Historical Team Rosters</h2>";

        while ($team = $teams_result->fetch_assoc()) {
            $team_id = intval($team['team_id']);
            $team_name = sanitize_str($team['name']);

            echo "<h3>$team_name</h3>";
            // Get each season the team played in
            $query = "
                SELECT s.season_id, s.year
                FROM Team_Season ts
                JOIN Season s
                    ON ts.season_id = s.season_id
                WHERE ts.team_id = ?
                ORDER BY s.year DESC
            ";
            $stmt = prepare_with_perms($db, $query);
            $stmt->bind_param("i", $team_id);
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                error("Could not view rosters", "team_page.php");
            }
            $seasons_result = $stmt->get_result();

            // Loop over each season
            while ($season = $seasons_result->fetch_assoc()) {
                $season_id = intval($season['season_id']);
                $season_year = sanitize_str($season['year']);

                echo "<h4>Season: $season_year</h4>";

                // Get all players on the team
                $query = "
                    SELECT p.first_name, p.last_name, p.position, p.status
                    FROM Player_Team pt
                    JOIN Player p ON pt.player_id = p.player_id
                    WHERE pt.team_id = ? AND pt.end_date IS NULL
                    ORDER BY p.last_name, p.first_name
                ";
                $stmt2 = prepare_with_perms($db, $query);
                $stmt2->bind_param("i", $team_id);
                try {
                    $stmt2->execute();
                } catch (mysqli_sql_exception $e) {
                    error("Could not view roster", "team_page.php");
                }
                $players_result = $stmt2->get_result();

                echo "<table>";
                echo "<tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Position</th>
                        <th>Status</th>
                    </tr>";

                // Display each player's information
                while ($player = $players_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . sanitize_str($player['first_name']) . "</td>";
                    echo "<td>" . sanitize_str($player['last_name']) . "</td>";
                    echo "<td>" . sanitize_str($player['position']) . "</td>";
                    echo "<td>" . sanitize_str($player['status']) . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
                $stmt2->close();
            }
            $stmt->close();
        }
    }

    function display_add_player_form() {
        global $db;

        // Get every player's information
        $query = "
            SELECT player_id, first_name, last_name, position
            FROM Player
            ORDER BY last_name, first_name
        ";
        $players = query_with_perms($db, $query);

        // Get every team
        $query = "
            SELECT team_id, name
            FROM Team
            ORDER BY city, name
        ";
        $teams = query_with_perms($db, $query);

        echo "<h2>Add Player to Team</h2>";
        echo "<form method='post' action='../scripts/team/add_player_team.php'>";

        echo "<label>Select Player:</label><br>";
        echo "<select name='add_player_id' required>";
        echo "<option value=''>-- Select Player --</option>";
        // Display a dropdown to select player
        while ($player = $players->fetch_assoc()) {
            echo "<option value='" . sanitize_str($player['player_id']) . "'>";
            echo sanitize_str($player['first_name'] . " " . $player['last_name'] . " (" . $player['position'] . ")");
            echo "</option>";
        }
        echo "</select><br><br>";

        echo "<label>Select Team:</label><br>";
        echo "<select name='add_team_id' required>";
        echo "<option value=''>-- Select Team --</option>";
        // Display a dropdown to select team
        while ($team = $teams->fetch_assoc()) {
            echo "<option value='" . sanitize_str($team['team_id']) . "'>";
            echo sanitize_str($team['name']);
            echo "</option>";
        }
        echo "</select><br><br>";

        echo "<input type='submit' value='Add Player to Team'>";
        echo "</form><br>";
    }

    function display_current_assignments() {
        global $db;

        // For each team, get the information
        // of all past and present players that
        // ever played for the team
        $query = "
            SELECT
                pt.player_team_id,
                p.first_name,
                p.last_name,
                p.position,
                p.player_id,
                t.name AS team_name,
                t.city AS team_city,
                pt.start_date,
                pt.end_date
            FROM Player_Team pt
            JOIN Player p
                ON pt.player_id = p.player_id
            JOIN Team t
                ON pt.team_id = t.team_id
            ORDER BY t.city, t.name, p.last_name
        ";
        $result = query_with_perms($db, $query);

        if (!$result) {
            echo "Error loading player team data";
            return;
        }

        echo "<h2>Current Player Team Assignments</h2>";
        echo "<table>";
        echo "<tr>
                <th>Player</th>
                <th>Position</th>
                <th>Team</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Action</th>
            </tr>";

        // Display each player's information
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . sanitize_str($row['first_name'] . " " . $row['last_name']) . "</td>";
            echo "<td>" . sanitize_str($row['position']) . "</td>";
            echo "<td>" . sanitize_str($row['team_name']) . "</td>";
            echo "<td>" . sanitize_str($row['start_date']) . "</td>";
            echo "<td>" . sanitize_str($row['end_date'] ?? 'Active') . "</td>";
            echo "<td>";
            if ($row['end_date'] === null) {
                echo "<form method='post' action='../scripts/team/delete_player_team.php'>";
                echo "<input type='hidden' name='player_team_id' value='" . sanitize_str($row['player_team_id']) . "'>";
                echo "<input type='hidden' name='player_id' value='" . sanitize_str($row['player_id']) . "'>";
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
