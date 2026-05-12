<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');
  require_once(__DIR__ . '/../scripts/helpers.php');

  do_html_header('Manage Players');
  check_valid_user();
  display_user_nav();

  display_edit_player();

  do_html_footer();

  function display_edit_player() {
    global $db;

    // Get players
    $query = "
        SELECT
          p.player_id,
          p.first_name,
          p.last_name,
          p.position,
          p.status
        FROM Player p
        ORDER BY p.last_name, p.first_name
    ";
    $result = query_with_perms($db, $query);

    echo "<h2>Edit Players</h2>";
    echo "<table>";
    echo "<tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Position</th>
            <th>Status</th>
            <th>Edit</th>
          </tr>";

    while ($player = $result->fetch_assoc()) {
        $player_id = intval($player['player_id']);
        $first_name = sanitize_str($player['first_name']);
        $last_name = sanitize_str($player['last_name']);
        $position = sanitize_str($player['position']);
        $status = sanitize_str($player['status']);

        echo "<tr>";
        echo "<form method='post' action='../scripts/player/edit_player.php'>";
        echo "<input type='hidden' name='player_id' value='$player_id'>";

        // Display first name, last name, and position in editable fields
        echo "<td><input type='text' name='first_name' value='$first_name' required></td>";
        echo "<td><input type='text' name='last_name' value='$last_name' required></td>";
        echo "<td><input type='text' name='position' minlength='2' maxlength='2' value=$position required></td>";
        echo "<td>
                <select name='status' required>";
                  $active_selected = ($status == 'Active') ? 'selected' : '';
                  $inactive_selected = ($status == 'Inactive') ? 'selected' : '';
        echo "
                    <option value='Active' $active_selected>Active</option>
                    <option value='Inactive' $inactive_selected>Inactive</option>
                </select>
              </td>";

        echo "<td><input type='submit' value='Edit'></td>";
        echo "</form>";
        echo "</tr>";
    }

    echo "</table>";
  }
?>
