<?php
    require_once("StartSession.php");
    require_once("html_components.php");
    require_once("helpers.php");

    check_valid_user();

    /*
     * Team management is a Manager-level feature.
     * This page only controls UI access. The actual protection should still
     * come from MySQL GRANTs on Team and Stadium.
     */
    if ($_SESSION['UserRole'] !== 'Manager') {
        err_permission_denied();
    }

    do_html_header("Manage Teams");
    display_user_nav();

    display_status_message();

    display_add_team_form();
    display_teams_table();

    do_html_footer();

    function display_status_message() {
        if (isset($_GET['success'])) {
            echo "<p style='color: green;'>" . sanitize_str($_GET['success']) . "</p>";
        }

        if (isset($_GET['error'])) {
            echo "<p style='color: red;'>" . sanitize_str($_GET['error']) . "</p>";
        }
    }

    function display_add_team_form() {
        global $db;

        /*
         * Stadium data is loaded from the database instead of hardcoded.
         * This keeps the form in sync with the Stadium table.
         */
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

        /*
         * Stadium can be NULL, so LEFT JOIN keeps teams visible even when
         * they are not assigned to a stadium.
         */
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

            /*
             * Hidden IDs are never trusted for authorization.
             * edit_team.php and delete_team.php must still validate permissions
             * and rely on the Manager DB account permissions.
             */
            echo "<form method='post' action='edit_team.php'>";
            echo "<input type='hidden' name='team_id' value='$team_id'>";
            echo "<td><input type='text' name='name' value='" . sanitize_str($row['name']) . "' required></td>";
            echo "<td><input type='text' name='city' value='" . sanitize_str($row['city']) . "' required></td>";

            echo "<td>
                    <select name='conference' required>
                    <option value='NFC'" . ($row['conference'] == 'NFC' ? ' selected' : '') . ">NFC</option>
                    <option value='AFC'" . ($row['conference'] == 'AFC' ? ' selected' : '') . ">AFC</option>
                    </select>
                </td>";

            echo "<td>
                    <select name='division' required>
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
