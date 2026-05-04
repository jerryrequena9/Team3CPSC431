<?php
    require_once('StartSession.php');
    require_once('html_components.php');
    require_once('helpers.php');

    do_html_header('Manage Stats');
    check_valid_user();
    display_user_nav();

    display_edit_stats();
    display_add_stats();

    do_html_footer();

    function display_edit_stats() {
        echo "<h2>Edit Stats</h2>";
        if (isset($_POST['edit_stat_id'], $_POST['edit_stat_field'], $_POST['edit_stat_value'])) {
            global $db;
            
            handle_stat_edit();
        }

        $query = "
            SELECT
                p.first_name,
                p.last_name,
                p.position,
                g.week,
                g.date,
                home.name AS home_team,
                away.name AS away_team,
                s.touchdowns,
                s.passing_yards,
                s.rushing_yards,
                s.receiving_yards,
                s.tackles,
                s.interceptions,
                s.stat_id
            FROM Stat s
            JOIN Game g
                ON s.game_id = g.game_id
            JOIN Player p
                ON s.player_id = p.player_id
            JOIN Team home
                ON g.home_team_id = home.team_id
            JOIN Team away
                ON g.away_team_id = away.team_id
            ORDER BY g.week, g.date, p.last_name ASC
        ";

        global $db;
        $result = query_with_perms($db, $query);

        echo "<table>";
        $headers = [
            'First Name', 'Last Name',
            'Position', 'Week', 'Date',
            'Home Team', 'Away Team', 'Touchdowns',
            'Passing Yards', 'Rushing Yards',
            'Receiving Yards',' Tackles',
            'Interceptions'
        ];
        echo "<tr>";
        foreach ($headers as $header) {
            echo "<th>$header</th>";
        }
        echo "<th>Delete</th>";
        echo "</tr>";

        while ($stat = $result->fetch_assoc()) {
            echo "<tr>";
            $fields = ['first_name', 'last_name', 'position', 'week', 'date', 'home_team', 'away_team'];
            foreach ($fields as $field) {
                $val = sanitize_str($stat[$field]);
                echo "<td>$val</td>";
            }
            $fields = ['touchdowns', 'passing_yards', 'rushing_yards', 'receiving_yards', 'tackles', 'interceptions'];
            $stat_id = intval($stat['stat_id']);
            foreach ($fields as $field) {
                $val = intval($stat[$field]);
                echo "<td>
                        <form method='post' action='edit_stat.php'>
                            <input type='hidden' name='edit_stat_id' value='$stat_id'>
                            <input type='hidden' name='edit_stat_field' value='$field'>
                            <input type='number' name='edit_stat_value' value='$val' min='0' style='width: 50px;'>
                            <br>
                            <input type='submit' value='Edit'>
                        </form>
                    </td>";
            }

            echo "<td>
                        <form method='post' action='delete_stat.php'>
                            <input type='hidden' name='delete_stat_id' value='$stat_id'>
                            <input type='submit' value='Delete'>
                        </form>
                    </td>";
        }
        echo "</table>";
    }

    function display_add_stats() {
        echo "<h2>Add Stats (WIP)</h2>";

        // display_team();

        // if (isset($_POST['manage_player_stats_team_id'])) {
        //     display_season();
        // }

        // if (isset($_POST['manage_player_stats_season_id'])) {
        //     display_game();
        // }

        // if (isset($_POST['manage_player_stats_game_id'])) {
        //     display_player();
        // }

        function display_team() {
            $query = "
                SELECT team_id, name
                FROM Team
                ORDER BY name ASC
            ";
            global $db;
            $result = query_with_perms($db, $query);

            echo '<form method="POST" action="manage_player_stats_page.php">
                    <label for="team">Select Team:</label>
                    <select name="manage_player_stats_team_id" required>
                    <option value="">Select Team</option>';

            $selected_team = isset($_POST['manage_player_stats_team_id']) ? intval($_POST['manage_player_stats_team_id']) : 0;
            while ($row = $result->fetch_assoc()) {
                $team_id = intval($row['team_id']);
                $name = sanitize_str($row['name']);
                $selected = ($selected_team === $team_id) ? 'selected' : '';
                echo "<option value='$team_id' $selected>$name</option>";
            }

            echo '</select>
                    <button type="submit">Next</button>
                    </form>';
        }

        function display_season() {
            $team_id = intval($_POST['manage_player_stats_team_id']);

            // only shows seasons that the team played in
            $query = "
                SELECT DISTINCT s.season_id, s.year
                FROM Season s
                JOIN Game g
                    ON g.season_id = s.season_id
                WHERE g.home_team_id = ? OR g.away_team_id = ?
                ORDER BY s.year ASC
            ";
            global $db;
            $stmt = prepare_with_perms($db, $query);
            if (!$stmt->bind_param('ii', $team_id, $team_id) || !$stmt->execute()) {
                display_error_exit("failed to get seasons for the team");
            }

            echo '<form method="POST" action="manage_player_stats_page.php">
                    <input type="hidden" name="manage_player_stats_team_id" value="'.$team_id.'">
                    <label for="season">Select Season:</label>
                    <select name="manage_player_stats_season_id" required>
                    <option value="">Select Season</option>';

            $selected_season = isset($_POST['manage_player_stats_season_id']) ? intval($_POST['manage_player_stats_season_id']) : 0;
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $season_id = intval($row['season_id']);
                $year = sanitize_str($row['year']);
                $selected = ($selected_season === $season_id) ? 'selected' : '';
                echo "<option value='$season_id' $selected>$year</option>";
            }

            echo '</select>
                    <input type="submit" value="Next">
                    </form>';
        }

        function display_game() {
            $team_id = intval($_POST['manage_player_stats_team_id']);
            $season_id = intval($_POST['manage_player_stats_season_id']);

            $query = "
                SELECT
                    g.game_id,
                    ht.name AS home_team_name,
                    at.name AS away_team_name,
                    g.date
                FROM Game g
                JOIN Team ht
                    ON g.home_team_id = ht.team_id
                JOIN Team at
                    ON g.away_team_id = at.team_id
                WHERE g.season_id = ?
                    AND (g.home_team_id = ? OR g.away_team_id = ?)
                ORDER BY g.date ASC
            ";

            global $db;
            $stmt = prepare_with_perms($db, $query);
            if (!$stmt->bind_param('iii', $season_id, $team_id, $team_id) || !$stmt->execute()) {
                display_error_exit("failed to get games for the season");
            }

            $result = $stmt->get_result();

            echo '<form method="POST" action="manage_player_stats_page.php">
                    <input type="hidden" name="manage_player_stats_team_id" value="' . $team_id . '">
                    <input type="hidden" name="manage_player_stats_season_id" value="' . $season_id . '">
                    <label for="game">Select Game:</label>
                    <select name="manage_player_stats_game_id" required>
                    <option value="">Select Game</option>';

            $selected_game = isset($_POST['manage_player_stats_game_id']) ? intval($_POST['manage_player_stats_game_id']) : 0;
            while ($game = $result->fetch_assoc()) {
                $game_id = intval($game['game_id']);
                $home_team_name = sanitize_str($game['home_team_name']);
                $away_team_name = sanitize_str($game['away_team_name']);
                $date = sanitize_str($game['date']);
                $game_name = "$home_team_name vs $away_team_name on $date";
                $selected = ($selected_game === $game_id) ? 'selected' : '';
                echo "<option value='$game_id' $selected>$game_name</option>";
            }

            echo '</select>
                    <input type="submit" value="Next">
                    </form>';
        }

        function display_player() {
            $team_id = intval($_POST['manage_player_stats_team_id']);
            $season_id = intval($_POST['manage_player_stats_season_id']);
            $game_id = intval($_POST['manage_player_stats_game_id']);

            $query = "
                SELECT DISTINCT p.player_id, p.first_name, p.last_name
                FROM Player p
                JOIN Player_Team pt
                    ON pt.player_id = p.player_id
                JOIN Stat s
                    ON s.player_id = p.player_id
                WHERE s.game_id = ? AND pt.team_id = ?
                ORDER BY p.last_name ASC
            ";

            global $db;
            $stmt = prepare_with_perms($db, $query);
            if (!$stmt->bind_param('ii', $game_id, $team_id) || !$stmt->execute()) {
                display_error_exit("failed to fetch players for the game");
            }

            $result = $stmt->get_result();

            echo '<form method="POST" action="manage_player_stats_page.php">
                    <input type="hidden" name="manage_player_stats_team_id" value="' . $team_id . '">
                    <input type="hidden" name="manage_player_stats_season_id" value="' . $season_id . '">
                    <input type="hidden" name="manage_player_stats_game_id" value="' . $game_id . '">
                    <label for="player">Select Player:</label>
                    <select name="manage_player_stats_player_id" required>
                    <option value="">Select Player</option>';

            $selected_player = isset($_POST['manage_player_stats_player_id']) ? intval($_POST['manage_player_stats_player_id']) : 0;
            while ($player = $result->fetch_assoc()) {
                $player_id = intval($player['player_id']);
                $full_name = sanitize_str($player['first_name'] . ' ' . $player['last_name']);
                $selected = ($selected_player === $player_id) ? 'selected' : '';
                echo "<option value='$player_id' $selected>$full_name</option>";
            }

            echo '</select>
                    <input type="submit" value="View Stats">
                    </form>';
        }
    }
    
?>