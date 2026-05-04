<?php
  require_once('StartSession.php');
  require_once('helpers.php');

  do_html_header('Manage Users'); 
  check_valid_user();

  $query = "
    SELECT u.username, r.name as role, u.email, u.last_login
    FROM UserAccount u
    JOIN Role r ON r.role_id = u.role_id
    ORDER BY u.role_id DESC
  ";
  global $db;
  $result = $db->query($query);
  if (!$result) {
    echo "Error loading users.";
    return;
  }

  echo "<table>";
  echo "<tr>
          <th>Role</th>
          <th>Username</th>
          <th>Email</th>
          <th>Last Login</th>
          <th>Actions</th>
        </tr>";

  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['last_login']) . "</td>";
    echo "<td>Reset Password</td>";
    echo "<td>Delete</td>";
    echo "<td>Change Role</td>";
    echo "</tr>";
  }

  echo "</table>";

  echo "ADD USER";

  display_user_nav();
  do_html_footer();
?>