<?php
require_once("StartSession.php");
require_once("html_components.php");
require_once("helpers.php");

do_html_header("Manage Player Teams");
check_valid_user();

$role = $_SESSION['UserRole'];

if ($role !== 'Coach' && $role !== 'Manager') {
  echo "Access denied. Only Coach and Manager users can manage player teams.<br>";
  display_user_nav();
  do_html_footer();
  exit;
}

/* Remove player from team */
if (isset($_POST['remove_player_team_id'])) {
  $player_team_id = intval($_POST['remove_player_team_id']);

  $stmt = $db->prepare("
    UPDATE Player_Team
    SET end_date = CURDATE()
    WHERE player_team_id = ?
  ");

  $stmt->bind_param("i", $player_team_id);

  if ($stmt->execute()) {
    echo "<p><b>Player removed from team successfully.</b></p>";
  } else {
    echo "<p>Error removing player: " . htmlspecialchars($stmt->error) . "</p>";
  }
}

/* Add player to team */
if (isset($_POST['add_player_id']) && isset($_POST['add_team_id'])) {
  $player_id = intval($_POST['add_player_id']);
  $team_id = intval($_POST['add_team_id']);

  $stmt = $db->prepare("
    INSERT INTO Player_Team (player_id, team_id, start_date, end_date)
    VALUES (?, ?, CURDATE(), NULL)
  ");

  $stmt->bind_param("ii", $player_id, $team_id);

  if ($stmt->execute()) {
    echo "<p><b>Player added to team successfully.</b></p>";
  } else {
    echo "<p>Error adding player: " . htmlspecialchars($stmt->error) . "</p>";
  }
}

echo "<h3>Coach/Manager Use Case: Add or Remove Players from Teams</h3>";

/* Add player form */
echo "<h4>Add Player to Team</h4>";

$players = $db->query("
  SELECT player_id, first_name, last_name, position
  FROM Player
  ORDER BY last_name, first_name
");

$teams = $db->query("
  SELECT team_id, name, city
  FROM Team
  ORDER BY city, name
");

echo "<form method='post' action='manage_player_team.php'>";

echo "<label>Select Player:</label><br>";
echo "<select name='add_player_id' required>";
echo "<option value=''>-- Select Player --</option>";

while ($player = $players->fetch_assoc()) {
  echo "<option value='" . htmlspecialchars($player['player_id']) . "'>";
  echo htmlspecialchars($player['first_name'] . " " . $player['last_name'] . " (" . $player['position'] . ")");
  echo "</option>";
}

echo "</select><br><br>";

echo "<label>Select Team:</label><br>";
echo "<select name='add_team_id' required>";
echo "<option value=''>-- Select Team --</option>";

while ($team = $teams->fetch_assoc()) {
  echo "<option value='" . htmlspecialchars($team['team_id']) . "'>";
  echo htmlspecialchars($team['city'] . " " . $team['name']);
  echo "</option>";
}

echo "</select><br><br>";

echo "<input type='submit' value='Add Player to Team'>";
echo "</form><br>";

/* Current team assignments */
echo "<h4>Current Player Team Assignments</h4>";

$result = $db->query("
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
");

if (!$result) {
  echo "Error loading player team data: " . htmlspecialchars($db->error);
} else {
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
    echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['position']) . "</td>";
    echo "<td>" . htmlspecialchars($row['team_city'] . " " . $row['team_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['end_date'] ?? 'Active') . "</td>";
    echo "<td>";

    if ($row['end_date'] === null) {
      echo "<form method='post' action='manage_player_team.php'>";
      echo "<input type='hidden' name='remove_player_team_id' value='" . htmlspecialchars($row['player_team_id']) . "'>";
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

display_user_nav();
do_html_footer();
?>
