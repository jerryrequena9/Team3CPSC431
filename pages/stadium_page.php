<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/html_components.php');
  require_once(__DIR__ . '/../scripts/helpers.php');


  do_html_header('Manage Stadiums');
  check_valid_user();
  display_user_nav();

  display_add_stadium();
  display_edit_stadium();

  do_html_footer();

  function display_add_stadium() {
    global $db;
    echo "<h2>Add Stadium</h2>";
    echo "<form method='post' action='../scripts/stadium/add_stadium.php'>";
    echo "<label>Name:</label><br><input type='text' name='name' required><br><br>";
    echo "<label>City:</label><br><input type='text' name='city' required><br><br>";
    echo "</select>";
    echo "<input type='submit' value='Add'>";
    echo "</form><br>";
  }

  function display_edit_stadium() {
    global $db;

    $query = "
        SELECT stadium_id, name, city
        FROM Stadium
        ORDER BY name, city
    ";
    $result = query_with_perms($db, $query);

    echo "<h2>View and Edit Stadiums</h2>";
    echo "<table>";
    echo "<tr>
            <th>Name</th>
            <th>City</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>";

    while ($row = $result->fetch_assoc()) {
        $stadium_id = intval($row['stadium_id']);

        echo "<tr>";

        echo "<form method='post' action='../scripts/stadium/edit_stadium.php'>";
        echo "<input type='hidden' name='stadium_id' value='$stadium_id'>";
        echo "<td><input type='text' name='name' value='" . sanitize_str($row['name']) . "' required></td>";
        echo "<td><input type='text' name='city' value='" . sanitize_str($row['city']) . "' required></td>";
        echo "<td><input type='submit' name='submit' value='Edit'></td>";
        echo "</form>";

        echo "<form method='post' action='../scripts/stadium/delete_stadium.php'>";
        echo "<input type='hidden' name='stadium_id' value='$stadium_id'>";
        echo "<td><input type='submit' name='submit' value='Delete'></td>";
        echo "</form>";

        echo "</tr>";
    }

    echo "</table>";
  }
?>
