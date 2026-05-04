<?php
require_once('StartSession.php');
require_once('html_components.php');
require_once('helpers.php');

check_valid_user();
require_role(['Fan','Player','Coach','Manager']);

do_html_header("Players");

$db = db_connect();

$sql = "SELECT player_id, first_name, last_name, position, status FROM Player";
$result = $db->query($sql);

echo "<h2>Players</h2>";
echo "<table border='1'>
<tr><th>ID</th><th>Name</th><th>Position</th><th>Status</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['player_id']}</td>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['position']}</td>
        <td>{$row['status']}</td>
    </tr>";
}

echo "</table>";

display_user_nav();
do_html_footer();
?>