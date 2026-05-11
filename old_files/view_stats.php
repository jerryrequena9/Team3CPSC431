<?php
require_once('StartSession.php');
require_once('html_components.php');
require_once('helpers.php');

check_valid_user();
require_role(['Player','Coach','Manager']);

do_html_header("Player Stats");

$db = db_connect();

$sql = "
SELECT p.first_name, p.last_name,
       s.touchdowns, s.passing_yards, s.rushing_yards,
       s.receiving_yards, s.tackles, s.interceptions
FROM Stat s
JOIN Player p ON s.player_id = p.player_id
";

$result = $db->query($sql);

echo "<h2>Stats</h2>";
echo "<table border='1'>
<tr>
<th>Player</th><th>TDs</th><th>Pass Yds</th>
<th>Rush Yds</th><th>Rec Yds</th><th>Tackles</th><th>INTs</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['touchdowns']}</td>
        <td>{$row['passing_yards']}</td>
        <td>{$row['rushing_yards']}</td>
        <td>{$row['receiving_yards']}</td>
        <td>{$row['tackles']}</td>
        <td>{$row['interceptions']}</td>
    </tr>";
}

echo "</table>";

display_user_nav();
do_html_footer();
?>