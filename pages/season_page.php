<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/../scripts/helpers.php');
  require_once(__DIR__ . '/html_components.php');

  do_html_header('Manage Seasons');
  check_valid_user();
  display_user_nav();

  display_view_season();
  display_add_season();
  display_add_champion();
  do_html_footer();

  function display_view_season() {
    global $db;

    $query = "
        SELECT s.season_id, s.champion, s.year, t.name AS team_name, t.team_id
        FROM Season s
        LEFT JOIN Team_Season ts ON s.season_id = ts.season_id
        LEFT JOIN Team t ON ts.team_id = t.team_id
        ORDER BY s.year DESC, t.name
    ";

    $result = query_with_perms($db, $query);

    $seasons = [];
    while ($row = $result->fetch_assoc()) {
        $season_id = intval($row['season_id']);
        if (!isset($seasons[$season_id])) {
            $seasons[$season_id] = [
                'year' => $row['year'],
                'teams' => [],
                'champion' => $row['champion']
            ];
        }
        if ($row['team_name']) {
            array_push($seasons[$season_id]['teams'], [$row['team_id'], $row['team_name']]);
        }
    }

    echo "<h2>Seasons</h2>";
    echo "<table>";
    echo "<tr><th>Year</th><th>Teams</th></tr>";
    foreach ($seasons as $season) {
        echo "<tr>";
        echo "<td>" . sanitize_str($season['year']) . "</td>";
        foreach ($season['teams'] as $team) {
          [$team_id, $team_name] = $team;
          if ($team_id == $season['champion']) {
            echo "<td style='color: gold'>" . sanitize_str($team_name) . " (Champion)</td>";
          } else {
            echo "<td>" . sanitize_str($team_name) . "</td>";
          }
        }
        echo "</tr>";
    }
    echo "</table><br>";
  }

  function display_add_season() {
    global $db;
    $query = "
      SELECT team_id, name, city
      FROM Team
      ORDER BY city, name
    ";
    $result = query_with_perms($db, $query);

    echo "<h2>Add New Season</h2>";
    echo "<form method='post' action='../scripts/season/add_season.php'>";
    echo "<label>Year:</label><br><input type='number' name='year' min='2026' max='2100' required><br><br>";
    echo "<label>Select Teams:</label><br>";

    while ($team = $result->fetch_assoc()) {
        $team_id = intval($team['team_id']);
        $name = sanitize_str($team['name']);
        echo "<input type='checkbox' name='team_ids[]' value='$team_id'>$name<br>";
    }

    echo "<br><input type='submit' value='Add Season'>";
    echo "</form>";
  }

  function display_add_champion() {
      global $db;

      // Fetch all seasons
      $query = "
        SELECT season_id, year
        FROM Season
        ORDER BY year DESC
      ";
      $seasons_result = query_with_perms($db, $query);

      // Fetch all teams
      $query = "
        SELECT team_id, name, city
        FROM Team
        ORDER BY city, name
      ";
      $teams_result = query_with_perms($db, $query);

      echo "<h2>Set Season Champion</h2>";
      echo "<form method='post' action='../scripts/season/add_champion.php'>";

      echo "<label>Season Year:</label><br>";
      echo "<select name='season_id' required>";
      echo "<option value=''>-- Select Year --</option>";
      while ($season = $seasons_result->fetch_assoc()) {
          $season_id = intval($season['season_id']);
          $year = sanitize_str($season['year']);
          echo "<option value='$season_id'>$year</option>";
      }
      echo "</select><br><br>";

      echo "<label>Champion Team:</label><br>";
      echo "<select name='team_id' required>";
      echo "<option value=''>-- Select Team --</option>";
      while ($team = $teams_result->fetch_assoc()) {
          $team_id = intval($team['team_id']);
          $team_name = sanitize_str($team['name']);
          echo "<option value='$team_id'>$team_name</option>";
      }
      echo "</select><br><br>";

      echo "<input type='submit' value='Set Champion'>";
      echo "</form>";
  }
?>