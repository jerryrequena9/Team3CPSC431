<?php
    require_once(__DIR__ . '/../scripts/StartSession.php');
    require_once(__DIR__ . '/html_components.php');
    require_once(__DIR__ . '/../scripts/helpers.php');

    do_html_header('Manage Stats');
    check_valid_user();
    display_user_nav();

    display_edit_stats();
    display_add_stats();

    do_html_footer();

    function display_edit_stats() {
        echo "<h2>Edit Stats</h2>";

        // Get information relevant to stats: player, game, etc.
        $query = "
            SELECT
                p.first_name,
                p.last_name,
                p.player_id,
                p.position,
                own_team.name as team_name,
                g.week,
                g.date,
                g.game_id,
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
            JOIN Season se
                ON se.season_id = g.season_id
            JOIN Player p
                ON s.player_id = p.player_id
            JOIN Team home
                ON g.home_team_id = home.team_id
            JOIN Team away
                ON g.away_team_id = away.team_id
            JOIN Player_Team pt
                ON pt.player_id = p.player_id
            JOIN Team own_team
                ON own_team.team_id = pt.team_id
            ORDER BY se.year DESC, g.week, g.date, p.last_name ASC
        ";

        global $db;
        $result = query_with_perms($db, $query);

        echo "<table>";
        $headers = [
            'First Name', 'Last Name',
            'Team Name', 'Position', 'Week', 'Date',
            'Home Team', 'Away Team', 'Touchdowns',
            'Passing Yards', 'Rushing Yards',
            'Receiving Yards', 'Tackles',
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

            $fields = ['first_name', 'last_name', 'team_name', 'position', 'week', 'date', 'home_team', 'away_team'];
            foreach ($fields as $field) {
                $val = sanitize_str($stat[$field]);
                echo "<td>$val</td>";
            }

            $fields = ['touchdowns', 'passing_yards', 'rushing_yards', 'receiving_yards', 'tackles', 'interceptions'];
            $stat_id = intval($stat['stat_id']);
            $game_id = intval($stat['game_id']);
            $player_id = intval($stat['player_id']);

            foreach ($fields as $field) {
                $val = intval($stat[$field]);

                echo "<td>
                        <form method='post' action='../scripts/stat/edit_stat.php'>
                            <input type='hidden' name='stat_id' value='$stat_id'>
                            <input type='hidden' name='stat_field' value='$field'>
                            <input type='number' name='stat_value' value='$val' min='0' style='width: 50px;'>
                            <input type='hidden' name='player_id' value='$player_id'>
                            <input type='hidden' name='game_id' value='$game_id'>
                            <br>
                            <input type='submit' value='Edit'>
                        </form>
                    </td>";
            }

            echo "<td>
                    <form method='post' action='../scripts/stat/delete_stat.php'>
                        <input type='hidden' name='stat_id' value='$stat_id'>
                        <input type='hidden' name='player_id' value='$player_id'>
                        <input type='hidden' name='game_id' value='$game_id'>
                        <input type='submit' value='Delete'>
                    </form>
                  </td>";

            echo "</tr>";
        }

        echo "</table>";
    }

    function display_add_stats() {
        global $db;
        // Get list of seasons
        $query = "
            SELECT season_id, year
            FROM Season
            ORDER BY year DESC
        ";
        $seasons = query_with_perms($db, $query);

        echo "<h2>Add Player Stat</h2>";
        echo "<form method='post' action='../scripts/stat/add_stat.php'>";
        echo "<label>Season:</label><br>";
        echo "<select name='season_id' required>";
        echo "<option value=''>-- Select Season --</option>";
        // Display seasons
        while ($season = $seasons->fetch_assoc()) {
            $season_id = intval($season['season_id']);
            $year = sanitize_str($season['year']);
            echo "<option value='$season_id'>$year</option>";
        }
        echo "</select><br><br>";

        // Get list of games
        $query = "
            SELECT 
                g.game_id, 
                g.week, 
                g.date, 
                home.name AS home_team, 
                away.name AS away_team
            FROM Game g
            JOIN Team home
                ON g.home_team_id = home.team_id
            JOIN Team away
                ON g.away_team_id = away.team_id
            ORDER BY g.date ASC
        ";
        $games = query_with_perms($db, $query);

        echo "<label>Game:</label><br>";
        echo "<select name='game_id' required>";
        echo "<option value=''>-- Select Game --</option>";
        // Display list of games
        while ($game = $games->fetch_assoc()) {
            $game_id = intval($game['game_id']);
            $week = intval($game['week']);
            $date = sanitize_str($game['date']);
            $home_team = sanitize_str($game['home_team']);
            $away_team = sanitize_str($game['away_team']);

            echo "<option value='$game_id'>Week $week ($date): $away_team @ $home_team</option>";
        }

        echo "</select><br><br>";

        // Get list of teams
        $query = "
            SELECT team_id, name
            FROM Team
            ORDER BY city, name
        ";
        $teams = query_with_perms($db, $query);
        echo "<label>Team:</label><br>";
        echo "<select name='team_id' required>";
        echo "<option value=''>-- Select Team --</option>";
        // Display list of teams
        while ($team = $teams->fetch_assoc()) {
            $team_id = intval($team['team_id']);
            $team_name = sanitize_str($team['name']);
            echo "<option value='$team_id'>$team_name</option>";
        }
        echo "</select><br><br>";

        // Get list of players
        $query = "
            SELECT player_id, first_name, last_name
            FROM Player
            ORDER BY last_name, first_name
        ";
        $players = query_with_perms($db, $query);
        echo "<label>Player:</label><br>";
        echo "<select name='player_id' required>";
        echo "<option value=''>-- Select Player --</option>";
        // Display list of players
        while ($player = $players->fetch_assoc()) {
            $player_id = intval($player['player_id']);
            $first_name = sanitize_str($player['first_name']);
            $last_name = sanitize_str($player['last_name']);
            echo "<option value='$player_id'>$last_name, $first_name</option>";
        }
        echo "</select><br><br>";

        // Display statistics
        $stat_fields = ['Touchdowns', 'Passing Yards', 'Rushing Yards', 'Receiving Yards', 'Tackles', 'Interceptions'];
        foreach ($stat_fields as $field) {
            echo "<label>" . $field . ":</label><br>";
            $field_name = str_replace(" ", "_", strtolower($field));
            echo "<input type='number' name='$field_name' value='0' min='0'><br><br>";
        }

        echo "<input type='submit' value='Add Stat'>";
        echo "</form>";
    }
?>
