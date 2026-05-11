<?php
  require_once(__DIR__ . '/../scripts/StartSession.php');
  require_once(__DIR__ . '/../scripts/helpers.php');
  require_once(__DIR__ . '/html_components.php');

  do_html_header('Manage Users'); 
  check_valid_user();
  display_user_nav();
  display_manage_user();
  display_add_user();
  display_promote_player();
  display_promote_coach();
  do_html_footer();

  function display_manage_user() {
    echo "<h2>Edit Users</h2>";

    // left join player and coach because not all users are players
    // or coaches
    $query = "
      SELECT 
          u.username,
          r.name AS role,
          u.email,
          u.last_login,
          p.first_name AS player_first_name,
          p.last_name AS player_last_name,
          c.first_name AS coach_first_name,
          c.last_name AS coach_last_name
      FROM UserAccount u
      JOIN Role r
          ON r.role_id = u.role_id
      LEFT JOIN Player p
          ON u.user_id = p.user_id
      LEFT JOIN Coach c
          ON u.user_id = c.user_id
      ORDER BY u.role_id DESC, u.username ASC
  ";
    global $db;
    $result = query_with_perms($db, $query);
    echo "<table>";
    echo "<tr>
            <th>Role</th>
            <th>Username</th>
            <th>Email</th>
            <th>Last Login</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Change Password</th>
            <th>Delete User</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      // Dropdown for changing role
      echo "<td>
              <form method='post' action='../scripts/user/edit_role.php'>
                <input type='hidden' name='manage_user_username' value='" . sanitize_str($row['username']) . "'>
                  <select name='manage_user_role' onchange='this.form.submit()'>";
                  $roles = get_roles();
                  foreach ($roles as $role) {
                      echo "<option value='$role'" . ($row['role'] == $role ? ' selected' : '') . ">$role</option>";
                  }
      echo "
                  </select>
                  <input type='hidden' name='username' value='" . sanitize_str($row['username']) . "'>
              </form>
            </td>";
      echo "<td>" . sanitize_str($row['username']) . "</td>";
      echo "<td>" . sanitize_str($row['email']) . "</td>";
      echo "<td>" . sanitize_str($row['last_login']) . "</td>";
      $first_name = '';
      if (!empty($row['player_first_name'])) {
          $first_name = $row['player_first_name'];
      } else if (!empty($row['coach_first_name'])) {
          $first_name = $row['coach_first_name'];
      }

      $last_name = '';
      if (!empty($row['player_last_name'])) {
          $last_name = $row['player_last_name'];
      } else if (!empty($row['coach_last_name'])) {
          $last_name = $row['coach_last_name'];
      }
      echo "<td>" . sanitize_str($first_name) . "</td>";
      echo "<td>" . sanitize_str($last_name) . "</td>";

      echo "<td>";
      echo '<form method="post" action="../scripts/user/force_change_password.php">
              <input type="hidden" name="manage_user_change_password_username" value="' . sanitize_str($row['username']) . '">
              <input type="text" placeholder="New password" name="manage_user_change_new_password">
              <input type="submit" value="Change Password">
            </form>
      ';
      echo "</td>";

      echo "<td>";
      echo '<form method="post" action="../scripts/user/delete_user.php">
              <input type="hidden" name="manage_user_delete_username" value="' . sanitize_str($row['username']) . '">
              <input type="submit" value="Delete">
            </form>
      ';
      echo "</td>";
      echo "</tr>";
    }

    echo "</table>";
  }
  
  function display_add_user() {
    echo '
      <br>
      <h2>Add User</h2>
      <form method="post" action="../scripts/user/create_user.php">
        <label>Email:</label><br>
        <input type="email" name="manage_user_create_user_email" required><br><br>

        <label>Username:</label><br>
        <input type="text" name="manage_user_create_user_username" minlength="4" maxlength="50" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="manage_user_create_user_password" minlength="4" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="manage_user_create_user_repeat_password" minlength="4" required><br><br>
        <label>Role:</label><br>
        <select name="manage_user_create_user_role" required>
          <option value="">-- Select Role --</option>
    ';
          $roles = get_roles();
          foreach ($roles as $role) {
            echo '<option value='.$role.'>'.$role.'</option>';
          }
    echo '
        </select>
        <br><br>

      <input type="submit" value="Add User"><br><br>
    </form>
    ';
  }

  function get_roles() {
    global $db;

    // Fetch roles from database
    // Ordered from lowest permission to highest
    $query = "
      SELECT name
      FROM Role
      ORDER BY role_id
    ";
    $result = query_with_perms($db, $query);
    $roles = [];
    while ($row = $result->fetch_assoc()) {
      $role = $row['name'];
      array_push($roles, $role);
    }

    return $roles;
  }

  function display_promote_player() {
    global $db;

    // Fetch all users who are not players
    $query = "
      SELECT user_id, username
      FROM UserAccount
      WHERE user_id NOT IN (SELECT user_id FROM Player)
      ORDER BY username
    ";
    $users_result = query_with_perms($db, $query);

    echo "<h2>Promote User to Player</h2>";
    echo "<form method='post' action='../scripts/user/promote_player.php'>";

    echo "<label>Select User:</label><br>";
    echo "<select name='user_id' required>";
    echo "<option value=''>-- Select Username --</option>";
    while ($user = $users_result->fetch_assoc()) {
        $user_id = intval($user['user_id']);
        $username = sanitize_str($user['username']);
        echo "<option value='$user_id'>$username</option>";
    }
    echo "</select><br><br>";

    echo "<label>First Name:</label><br>";
    echo "<input type='text' name='first_name' required><br><br>";

    echo "<label>Last Name:</label><br>";
    echo "<input type='text' name='last_name' required><br><br>";

    echo "<label>Position:</label><br>";
    echo "<input type='text' name='position' required minlength='2' maxlength='2'><br><br>";

    echo "<input type='submit' value='Promote to Player'>";
    echo "</form>";
  }

  function display_promote_coach() {
      global $db;

      // Fetch all users who are not coaches
      $query = "
        SELECT user_id, username
        FROM UserAccount
        WHERE user_id NOT IN (SELECT user_id FROM Coach)
        ORDER BY username
      ";
      $users_result = query_with_perms($db, $query);

      echo "<h2>Promote User to Coach</h2>";
      echo "<form method='post' action='../scripts/user/promote_coach.php'>";

      echo "<label>Select User:</label><br>";
      echo "<select name='user_id' required>";
      echo "<option value=''>-- Select Username --</option>";
      while ($user = $users_result->fetch_assoc()) {
          $user_id = intval($user['user_id']);
          $username = sanitize_str($user['username']);
          echo "<option value='$user_id'>$username</option>";
      }
      echo "</select><br><br>";

      echo "<label>First Name:</label><br>";
      echo "<input type='text' name='first_name' required><br><br>";

      echo "<label>Last Name:</label><br>";
      echo "<input type='text' name='last_name' required><br><br>";

      echo "<input type='submit' value='Promote to Coach'>";
      echo "</form>";
  }
?>
