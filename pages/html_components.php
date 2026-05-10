<?php

function do_html_header($title) {
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($title); ?></title>

  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }

    li, td, th {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }

    hr {
      color: #3333cc;
    }

    a {
      color: #000;
      margin-right: 10px;
    }

    table {
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th {
      background: #ddd;
    }

    th, td {
      padding: 6px;
      border: 1px solid #333;
    }

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

  echo "<a href='home_page.php'>Home</a>";
  echo "<a href='../scripts/user/logout.php'>Logout</a>";
  echo "<a href='change_password_page.php'>Change Password</a>";
  echo "<br>";

  echo "<a href='user_page.php'>Manage Users</a>";

  echo "<a href='team_page.php'>Manage Teams</a>";

  echo "<a href='stadium_page.php'>
          Manage Stadiums
        </a>";

  echo "<a href='season_page.php'>
          Manage Seasons
        </a>";

  echo "<a href='game_page.php'>
          Manage Games
        </a>";

  echo "<a href='stat_page.php'>
          Manage Stats
        </a>";

  echo "<a href='player_page.php'>
          Manage Players
        </a>";

  echo "<a href='coach_page.php'>
          Manage Coaches
        </a>";

  echo "<hr>";
}
?>
