<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');
  require_once(__DIR__ . '/../scripts/helpers.php');

  do_html_header('Manage Games');
  check_valid_user();
  display_user_nav();

  display_edit_game();
  display_add_game();

  do_html_footer();

  function display_add_game() {
    global $db;

    // Get list of seasons
    $query = "
      SELECT season_id, year
      FROM Season
      ORDER BY year DESC
    ";
    $seasons = query_with_perms($db, $query);

    // Get list of teams
    $query = "
      SELECT team_id, name, city
      FROM Team
      ORDER BY city, name
    ";
    $teams = query_with_perms($db, $query);

    echo "<h2>Add Game</h2>";
    echo "<form method='post' action='../scripts/game/add_game.php'>";

    // Display seasons
    echo "<label>Season:</label><br>";
    echo "<select name='season_id' required>";
    echo "<option value=''>-- Select Season --</option>";
    while ($season = $seasons->fetch_assoc()) {
        $season_id = intval($season['season_id']);
        $year = sanitize_str($season['year']);
        echo "<option value='$season_id'>$year</option>";
    }
    echo "</select><br><br>";

    // Display home teams
    echo "<label>Home Team:</label><br>";
    echo "<select name='home_team_id' required>";
    echo "<option value=''>-- Select Team --</option>";
    while ($team = $teams->fetch_assoc()) {
        $team_id = intval($team['team_id']);
        $team_name = sanitize_str($team['name']);
        echo "<option value='$team_id'>$team_name</option>";
    }
    echo "</select><br><br>";

    // Display away teams
    $teams = query_with_perms($db, $query);
    echo "<label>Away Team:</label><br>";
    echo "<select name='away_team_id' required>";
    echo "<option value=''>-- Select Team --</option>";
    while ($team = $teams->fetch_assoc()) {
        $team_id = intval($team['team_id']);
        $team_name = sanitize_str($team['name']);
        echo "<option value='$team_id'>$team_name</option>";
    }
    echo "</select><br><br>";

    // Week and date
    echo "<label>Week:</label><br><input type='number' name='week' min='1' required><br><br>";
    echo "<label>Date:</label><br><input type='date' name='date' required><br><br>";

    // Home and away scores
    echo "<label>Home Score:</label><br><input type='number' name='home_score' min='0' required><br><br>";
    echo "<label>Away Score:</label><br><input type='number' name='away_score' min='0' required><br><br>";

    echo "<input type='submit' value='Add Game'>";
    echo "</form>";
  }

  function display_edit_game() {
    global $db;

    // Get list of teams
    $query = "
      SELECT team_id, name
      FROM Team
      ORDER BY city, name
    ";
    $teams_result = query_with_perms($db, $query);

    // Get list of seasons
    $query = "
      SELECT season_id, year
      FROM Season
      ORDER BY year DESC;
    ";
    $seasons_result = query_with_perms($db, $query);

    // Get list of games
    $query = "
        SELECT
          g.game_id,
          g.week,
          g.date,
          g.home_score,
          g.away_score,
          g.home_team_id,
          g.away_team_id,
          g.season_id
        FROM Game g
        JOIN Season s
          ON s.season_id = g.season_id
        ORDER BY s.year DESC, g.date ASC, g.week ASC, g.game_id
    ";
    $games_result = query_with_perms($db, $query);

    echo "<h2>Edit Games</h2>";
    echo "<table>";
    echo "<tr>
            <th>Season</th>
            <th>Week</th>
            <th>Date</th>
            <th>Home Team</th>
            <th>Away Team</th>
            <th>Home Score</th>
            <th>Away Score</th>
            <th>Edit</th>
            <th>Delete</th>
          </tr>";

    while ($game = $games_result->fetch_assoc()) {
        echo "<tr>";

        $game_id = intval($game['game_id']);
        echo "<form method='post' action='../scripts/game/edit_game.php'>";
        echo "<input type='hidden' name='game_id' value='$game_id'>";

        // Display season
        echo "<td><select name='season_id' required>";
        foreach ($seasons_result as $season) {
            $year = sanitize_str($season['year']);
            $season_id = intval($season['season_id']);
            $selected = ($game['season_id'] == $season_id) ? "selected" : "";
            echo "<option value='$season_id' $selected>$year</option>";
        }
        echo "</select></td>";

        // Display week
        $week = intval($game['week']);
        echo "<td><input type='number' name='week' value='$week' min='1' max='16' required></td>";

        // Display date
        $date = sanitize_str($game['date']);
        echo "<td><input type='date' name='date' value='$date' required></td>";

        // Display home team
        $home_team_id = intval($game['home_team_id']);
        echo "<td><select name='home_team_id' required>";
        foreach ($teams_result as $team) {
            $team_id = intval($team['team_id']);
            $team_name = sanitize_str($team['name']);
            $selected = ($team_id == $home_team_id) ? "selected" : "";
            echo "<option value='$team_id' $selected>$team_name</option>";
        }
        echo "</select></td>";

        // Display away team
        $away_team_id = intval($game['away_team_id']);
        echo "<td><select name='away_team_id' required>";
        foreach ($teams_result as $team) {
            $team_id = intval($team['team_id']);
            $team_name = sanitize_str($team['name']);
            $selected = ($team_id == $away_team_id) ? "selected" : "";
            echo "<option value='$team_id' $selected>$team_name</option>";
        }
        echo "</select></td>";

        // Display home and away scores
        $home_score = intval($game['home_score']);
        $away_score = intval($game['away_score']);
        echo "<td><input type='number' name='home_score' value='$home_score' min='0' required></td>";
        echo "<td><input type='number' name='away_score' value='$away_score' min='0' required></td>";
        echo "<td><input type='submit' value='Edit'></td>";
        echo "</form>";

        $season_id = intval($game['season_id']);
        echo "<td>
                <form method='post' action='../scripts/game/delete_game.php'>
                    <input type='hidden' name='game_id' value='$game_id'>
                    <input type='hidden' name='season_id' value='$season_id'>
                    <input type='hidden' name='home_team_id' value='$home_team_id'>
                    <input type='hidden' name='away_team_id' value='$away_team_id'>
                    <input type='submit' value='Delete'>
                </form>
              </td>";
        echo "</tr>";
    }

    echo "</table>";
  }
?>
