<?php

function do_html_header($title) {
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
    li, td, th { font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
    hr { color: #3333cc; }
    a { color: #000; margin-right: 10px; }
    table { border-collapse: collapse; margin-bottom: 20px; }
    th { background: #ddd; }
    th, td { padding: 6px; border: 1px solid #333; }
    div.formblock {
      background: #ccc;
      width: 300px;
      padding: 6px;
      border: 1px solid #000;
    }
  </style>
</head>
<body>
<hr/>

<?php
  if ($title) {
    echo "<h1>" . htmlspecialchars($title) . "</h1>";
  }
}

function do_html_footer() {
?>
</body>
</html>
<?php
}

function display_user_nav() {
?>
  <br>
  <a href='home_page.php'>Home</a>
  <a href='logout.php'>Logout</a>
  <a href='change_password_page.php'>Change Password</a>
<?php
}

function display_teams($db) {
  echo "<h3>Fan Use Case: What teams are in the league?</h3>";

  $query = "
    SELECT name, city, conference, division
    FROM Team
    ORDER BY conference, division, city
  ";

  $result = $db->query($query);

  if (!$result) {
    echo "Error loading teams: " . htmlspecialchars($db->error) . "<br>";
    return;
  }

  echo "<table>";
  echo "<tr>
          <th>Team</th>
          <th>City</th>
          <th>Conference</th>
          <th>Division</th>
        </tr>";

  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['city']) . "</td>";
    echo "<td>" . htmlspecialchars($row['conference']) . "</td>";
    echo "<td>" . htmlspecialchars($row['division']) . "</td>";
    echo "</tr>";
  }

  echo "</table>";
}

function display_recent_games($db) {
  echo "<h3>Player Use Case: What teams played last this season?</h3>";

  $query = "
    SELECT
      g.week,
      g.date,
      home.name AS home_team,
      away.name AS away_team,
      g.home_score,
      g.away_score
    FROM Game g
    JOIN Team home ON g.home_team_id = home.team_id
    JOIN Team away ON g.away_team_id = away.team_id
    JOIN Season s ON g.season_id = s.season_id
    WHERE s.year = 2025
    ORDER BY g.date DESC
    LIMIT 5
  ";

  $result = $db->query($query);

  if (!$result) {
    echo "Error loading recent games: " . htmlspecialchars($db->error) . "<br>";
    return;
  }

  echo "<table>";
  echo "<tr>
          <th>Week</th>
          <th>Date</th>
          <th>Home Team</th>
          <th>Away Team</th>
          <th>Score</th>
        </tr>";

  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['week']) . "</td>";
    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['home_team']) . "</td>";
    echo "<td>" . htmlspecialchars($row['away_team']) . "</td>";
    echo "<td>" . htmlspecialchars($row['home_score'] . " - " . $row['away_score']) . "</td>";
    echo "</tr>";
  }

  echo "</table>";
}

function display_player_games($db) {
  echo "<h3>Coach Use Case: What games did Player X play in?</h3>";

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
      s.receiving_yards
    FROM Stat s
    JOIN Player p ON s.player_id = p.player_id
    JOIN Game g ON s.game_id = g.game_id
    JOIN Team home ON g.home_team_id = home.team_id
    JOIN Team away ON g.away_team_id = away.team_id
    ORDER BY p.last_name, g.date DESC
  ";

  $result = $db->query($query);

  if (!$result) {
    echo "Error loading player games: " . htmlspecialchars($db->error) . "<br>";
    return;
  }

  echo "<table>";
  echo "<tr>
          <th>Player</th>
          <th>Position</th>
          <th>Week</th>
          <th>Date</th>
          <th>Matchup</th>
          <th>TD</th>
          <th>Passing</th>
          <th>Rushing</th>
          <th>Receiving</th>
        </tr>";

  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['position']) . "</td>";
    echo "<td>" . htmlspecialchars($row['week']) . "</td>";
    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['away_team'] . " at " . $row['home_team']) . "</td>";
    echo "<td>" . htmlspecialchars($row['touchdowns']) . "</td>";
    echo "<td>" . htmlspecialchars($row['passing_yards']) . "</td>";
    echo "<td>" . htmlspecialchars($row['rushing_yards']) . "</td>";
    echo "<td>" . htmlspecialchars($row['receiving_yards']) . "</td>";
    echo "</tr>";
  }

  echo "</table>";
}

?>
