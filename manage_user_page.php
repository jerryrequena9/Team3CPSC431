<?php
  require_once('StartSession.php');
  require_once('helpers.php');
  require_once('html_components.php');

  do_html_header('Manage Users'); 
  check_valid_user();
  display_user_nav();

  display_manage_user();
  display_add_user();
  do_html_footer();

  function display_manage_user() {
    echo "<h2>Edit Users</h2>";
    $query = "
      SELECT u.username, r.name as role, u.email, u.last_login
      FROM UserAccount u
      JOIN Role r ON r.role_id = u.role_id
      ORDER BY u.role_id DESC
    ";
    global $db;
    $result = query_with_perms($db, $query);
    echo "<table>";
    echo "<tr>
            <th>Role</th>
            <th>Username</th>
            <th>Email</th>
            <th>Last Login</th>
            <th>Change Password</th>
            <th>Delete User</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      // Dropdown for changing role
      echo "<td>
              <form method='post' action='change_role.php'>
                <input type='hidden' name='manage_user_username' value='" . sanitize_str($row['username']) . "'>
                  <select name='manage_user_role' onchange='this.form.submit()'>";
                  global $DBRoles;
                  foreach ($DBRoles as $role) {
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

      echo "<td>";
      echo '<form method="post" action="force_change_password.php">
              <input type="hidden" name="manage_user_change_password_username" value="' . sanitize_str($row['username']) . '">
              <input type="text" placeholder="New password" name="manage_user_change_new_password">
              <input type="submit" value="Change Password">
            </form>
      ';
      echo "</td>";

      echo "<td>";
      echo '<form method="post" action="delete_user.php">
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
      <form method="post" action="create_user.php">
        <label>Email:</label><br>
        <input type="email" name="manage_user_create_user_email"><br><br>

        <label>Username:</label><br>
        <input type="text" name="manage_user_create_user_username"><br><br>

        <label>Password:</label><br>
        <input type="password" name="manage_user_create_user_password"><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="manage_user_create_user_repeat_password"><br><br>

        <input type="submit" value="Add User"><br><br>
    </form>
    ';
  }
?>