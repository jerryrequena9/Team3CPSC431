<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/../scripts/helpers.php');
  require_once(__DIR__ . '/html_components.php');

  do_html_header('Manage Seasons');
  check_valid_user();
  display_user_nav();

  display_view_season();
  display_add_season();
  do_html_footer();

  function display_view_season() {
    global $db;

    // Get all seasons and participating teams
    $query = "
        SELECT
          s.season_id,
          s.year,
          t.name AS team_name,
          ts.wins,
          ts.losses,
          ts.ties
        FROM Season s
        JOIN Team_Season ts
          ON s.season_id = ts.season_id
        JOIN Team t
          ON ts.team_id = t.team_id
        ORDER BY s.year DESC, ts.wins DESC, ts.losses ASC, t.name
    ";

    $result = query_with_perms($db, $query);

    // Organize team information by season
    $seasons = [];
    while ($row = $result->fetch_assoc()) {
        $season_id = intval($row['season_id']);
        $seasons[$season_id]['year'] = sanitize_str($row['year']);
        $seasons[$season_id]['teams'][] = [
            'team_name' => sanitize_str($row['team_name']),
            'wins' => intval($row['wins']),
            'losses' => intval($row['losses']),
            'ties' => intval($row['ties']),
        ];
    }

    echo "<h2>Seasons</h2>";

    // Display information for each season
    foreach ($seasons as $season) {
        echo "<h3>" . $season['year'] . "</h3>";

        echo "<table>";
        echo "<tr><th>Team</th><th>Record (W-L-T)</th></tr>";
        foreach ($season['teams'] as $team) {
            echo "<tr>";
            echo "<td>" . $team['team_name'] . "</td>";
            echo "<td>" . $team['wins'] . "-" . $team['losses'] . "-" . $team["ties"] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
  }

  function display_add_season() {
    global $db;

    // Get list of teams 
    $query = "
      SELECT team_id, name
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
        // Allow user to select multiple teams
        echo "<input type='checkbox' name='team_ids[]' value='$team_id'>$name<br>";
    }

    echo "<br><input type='submit' value='Add Season'>";
    echo "</form>";
  }
?>