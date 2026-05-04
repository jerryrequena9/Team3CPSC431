<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    do_html_header("Manage Teams");
    check_valid_user();
    display_user_nav();

    display_add_team_form();
    display_teams_table();

    do_html_footer();

    function display_add_team_form() {
        global $db;

        $query = "
            SELECT stadium_id, name, city
            FROM Stadium
            ORDER BY name, city
        ";
        $stadiums = query_with_perms($db, $query);

        echo "<h2>Add Team</h2>";
        echo "<form method='post' action='add_team.php'>";
        echo "<label>Name:</label><br><input type='text' name='name' required><br><br>";
        echo "<label>City:</label><br><input type='text' name='city' required><br><br>";
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
        while ($s = $stadiums->fetch_assoc()) {
            echo "<option value='" . intval($s['stadium_id']) . "'>" . sanitize_str($s['city'] . " - " . $s['name']) . "</option>";
        }
        echo "</select><br><br>";
        echo "<input type='submit' value='Add Team'>";
        echo "</form><br>";
    }

    function display_teams_table() {
        global $db;

        // stadium can be null so we left join
        $query = "
            SELECT t.team_id, t.name, t.city, t.conference, t.division, s.name AS stadium_name
            FROM Team t
            LEFT JOIN Stadium s
                ON t.stadium_id = s.stadium_id
            ORDER BY t.city, t.name
        ";
        $result = query_with_perms($db, $query);

        echo "<h2>Edit Teams</h2>";
        echo "<table>";
        echo "<tr>
                <th>Name</th>
                <th>City</th>
                <th>Conference</th>
                <th>Division</th>
                <th>Stadium</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>";

        while ($row = $result->fetch_assoc()) {
            $team_id = intval($row['team_id']);
            echo "<tr>";
            echo "<form method='post' action='edit_team.php'>";
            echo "<input type='hidden' name='team_id' value='$team_id'>";
            echo "<td><input type='text' name='name' value='" . sanitize_str($row['name']) . "'></td>";
            echo "<td><input type='text' name='city' value='" . sanitize_str($row['city']) . "'></td>";
            echo "<td>
                    <select name='conference'>
                    <option value='NFC'" . ($row['conference'] == 'NFC' ? ' selected' : '') . ">NFC</option>
                    <option value='AFC'" . ($row['conference'] == 'AFC' ? ' selected' : '') . ">AFC</option>
                    </select>
                </td>";
            echo "<td>
                    <select name='division'>
                    <option value='North'" . ($row['division'] == 'North' ? ' selected' : '') . ">North</option>
                    <option value='South'" . ($row['division'] == 'South' ? ' selected' : '') . ">South</option>
                    <option value='East'" . ($row['division'] == 'East' ? ' selected' : '') . ">East</option>
                    <option value='West'" . ($row['division'] == 'West' ? ' selected' : '') . ">West</option>
                    </select>
                </td>";
            echo "<td>" . sanitize_str($row['stadium_name'] ?? 'None') . "</td>";
            echo "<td><input type='submit' value='Save'></td>";
            echo "</form>";
            echo "<td>
                    <form method='post' action='delete_team.php'>
                    <input type='hidden' name='team_id' value='$team_id'>
                    <input type='submit' value='Delete'>
                    </form>
                </td>";
            echo "</tr>";
        }
        echo "</table>";
    }
?>