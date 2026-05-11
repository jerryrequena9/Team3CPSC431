<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');
  require_once(__DIR__ . '/../scripts/helpers.php');


  do_html_header('Manage Coaches');
  check_valid_user();
  display_user_nav();

  display_edit_coach();

  do_html_footer();

  function display_edit_coach() {
    global $db;

    $query = "
      SELECT team_id, name
      FROM Team
      ORDER BY city, name
    ";
    $teams_result = query_with_perms($db, $query);

    $query = "
        SELECT coach_id, user_id, team_id, first_name, last_name
        FROM Coach
        ORDER BY last_name, first_name
    ";
    $coach_result = query_with_perms($db, $query);

    echo "<h2>Edit Coaches</h2>";
    echo "<table>";
    echo "<tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Team</th>
            <th>Edit</th>
          </tr>";

    while ($coach = $coach_result->fetch_assoc()) {
        $coach_id = intval($coach['coach_id']);
        $team_id = intval($coach['team_id']);
        $first_name = sanitize_str($coach['first_name']);
        $last_name = sanitize_str($coach['last_name']);

        echo "<tr>";
        echo "<form method='post' action='../scripts/coach/edit_coach.php'>";
        echo "<input type='hidden' name='coach_id' value='$coach_id'>";

        echo "<td><input type='text' name='first_name' value='$first_name' required></td>";
        echo "<td><input type='text' name='last_name' value='$last_name' required></td>";

        echo "<td><select name='team_id'>";
        echo "<option value=''>-- No Team--</option>";
        foreach ($teams_result as $team) {
          $this_team_id = intval($team['team_id']);
          $team_name = sanitize_str($team['name']);
          $selected = ($this_team_id === $team_id) ? "selected" : "";
          echo "<option value='$this_team_id' $selected>$team_name</option>";
        }
        echo "</select></td>";

        echo "<td><input type='submit' value='Edit'></td>";
        echo "</form>";
        echo "</tr>";
    }

    echo "</table>";
  }
?>
