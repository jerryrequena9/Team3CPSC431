<!DOCTYPE html>
<html>
  <head>
    <title>CPSC 431 HW-3</title>
  </head>
  <body>
    <h1 style="text-align:center">Cal State Fullerton Basketball Statistics</h1>

    <?php

      require_once(__DIR__ . '/Address.php');
      require_once(__DIR__ . '/PlayerStatistic.php');
      require_once(__DIR__ . '/Adaptation.php');

      // Enable error reporting during development
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      /*
      -------------------------------------------------------
      LOGOUT HANDLER FOR HTTP BASIC AUTH
      -------------------------------------------------------
      */
      if (isset($_GET['logout'])) {
        header('WWW-Authenticate: Basic realm="HW3 Basketball Login"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You have been logged out. Close the browser or reload to log in again.";
        exit;
      }

      /*
      -------------------------------------------------------
      HTTP BASIC AUTHENTICATION
      -------------------------------------------------------
      */
      if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="HW3 Basketball Login"');
        header('HTTP/1.0 401 Unauthorized');
        echo "Authentication required.";
        exit;
      }

      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];

      /*
      -------------------------------------------------------
      CONNECT USING auth_user
      -------------------------------------------------------
      Used only to verify login against Users table.
      */
      $authConn = new mysqli(
        DATA_BASE_HOST,
        AUTH_USER_NAME,
        AUTH_USER_PASSWORD,
        DATA_BASE_NAME
      );

      if ($authConn->connect_error) {
        die("Authentication DB connection failed: " . $authConn->connect_error);
      }

      $authConn->set_charset('utf8mb4');

      $stmt = $authConn->prepare(
        "SELECT password_hash, role, roster_id
         FROM Users
         WHERE username = ?"
      );

      if (!$stmt) {
        die("Prepare failed: " . htmlspecialchars($authConn->error, ENT_QUOTES, 'UTF-8'));
      }

      $stmt->bind_param("s", $username);
      $stmt->execute();
      $stmt->bind_result($password_hash, $role, $roster_id);

      if (!$stmt->fetch()) {
        $stmt->close();
        $authConn->close();
        die("Invalid username.");
      }

      $stmt->close();
      $authConn->close();

      if (!password_verify($password, $password_hash)) {
        die("Invalid password.");
      }

      /*
      -------------------------------------------------------
      CONNECT USING ROLE-BASED DATABASE ACCOUNT
      -------------------------------------------------------
      */
      $creds = getDatabaseCredentials($role);

      if ($creds === null) {
        die("No database credentials found for role.");
      }

      $conn = new mysqli(
        DATA_BASE_HOST,
        $creds['username'],
        $creds['password'],
        DATA_BASE_NAME
      );

      if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
      }

      $conn->set_charset('utf8mb4');

      /*
      -------------------------------------------------------
      HELPERS
      -------------------------------------------------------
      */
      function h($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
      }

      function avg_int($x) {
        return ($x === null) ? null : (int)round($x);
      }

      /*
      -------------------------------------------------------
      MAIN ROSTER + AVERAGE STATS QUERY
      -------------------------------------------------------
      */
      $rows = [];

      $sql = "
        SELECT
          tr.ID,
          tr.Name_First,
          tr.Name_Last,
          tr.Street,
          tr.City,
          tr.State,
          tr.Country,
          tr.ZipCode,
          COUNT(s.ID) AS GamesPlayed,
          AVG(s.PlayingTimeMin) AS AvgMin,
          AVG(s.PlayingTimeSec) AS AvgSec,
          AVG(s.Points) AS AvgPoints,
          AVG(s.Assists) AS AvgAssists,
          AVG(s.Rebounds) AS AvgRebounds
        FROM TeamRoster tr
        LEFT JOIN Statistics s
          ON tr.ID = s.Player
        GROUP BY
          tr.ID, tr.Name_First, tr.Name_Last,
          tr.Street, tr.City, tr.State, tr.Country, tr.ZipCode
        ORDER BY tr.Name_Last, tr.Name_First
      ";

      $stmt = $conn->prepare($sql);

      if (!$stmt) {
        echo "<p style='color:red; text-align:center;'>Prepare failed: " . h($conn->error) . "</p>";
      } else {
        if (!$stmt->execute()) {
          echo "<p style='color:red; text-align:center;'>Execute failed: " . h($stmt->error) . "</p>";
        } else {
          $id = $first = $last = $street = $city = $state = $country = $zip = null;
          $gamesPlayed = $avgMin = $avgSec = $avgPoints = $avgAssists = $avgRebounds = null;

          $stmt->bind_result(
            $id, $first, $last, $street, $city, $state, $country, $zip,
            $gamesPlayed, $avgMin, $avgSec, $avgPoints, $avgAssists, $avgRebounds
          );

          while ($stmt->fetch()) {
            $rows[] = [
              'ID' => $id,
              'First' => $first,
              'Last' => $last,
              'Street' => $street,
              'City' => $city,
              'State' => $state,
              'Country' => $country,
              'Zip' => $zip,
              'GamesPlayed' => (int)$gamesPlayed,
              'AvgMin' => $avgMin,
              'AvgSec' => $avgSec,
              'AvgPoints' => $avgPoints,
              'AvgAssists' => $avgAssists,
              'AvgRebounds' => $avgRebounds
            ];
          }
        }

        $stmt->close();
      }

      /*
      -------------------------------------------------------
      EXISTING STAT RECORDS FOR COACH UPDATE DROPDOWN
      -------------------------------------------------------
      */
      $statRows = [];

      $statSql = "
        SELECT
          s.ID,
          s.Player,
          tr.Name_First,
          tr.Name_Last,
          s.PlayingTimeMin,
          s.PlayingTimeSec,
          s.Points,
          s.Assists,
          s.Rebounds
        FROM Statistics s
        JOIN TeamRoster tr
          ON tr.ID = s.Player
        ORDER BY tr.Name_Last, tr.Name_First, s.ID
      ";

      $statStmt = $conn->prepare($statSql);

      if ($statStmt) {
        if ($statStmt->execute()) {
          $sid = $player = $sfname = $slname = $smin = $ssec = $spts = $sast = $sreb = null;

          $statStmt->bind_result(
            $sid, $player, $sfname, $slname, $smin, $ssec, $spts, $sast, $sreb
          );

          while ($statStmt->fetch()) {
            $statRows[] = [
              'StatID' => $sid,
              'PlayerID' => $player,
              'First' => $sfname,
              'Last' => $slname,
              'Min' => $smin,
              'Sec' => $ssec,
              'Points' => $spts,
              'Assists' => $sast,
              'Rebounds' => $sreb
            ];
          }
        }
        $statStmt->close();
      }

      // Role flags for page display
      $isManager = ($role === 'manager');
      $isCoach   = ($role === 'coach');
      $isPlayer  = ($role === 'player');
    ?>

    <p style="text-align:center;">
      Logged in as: <strong><?php echo h($username); ?></strong>
      (role: <strong><?php echo h($role); ?></strong>)
      |
      <a href="?logout=1" style="color:red;">Logout</a>
    </p>

    <table style="width: 100%; border:0px solid black; border-collapse:collapse;">
      <tr>
        <th style="width: 40%;">Name and Address</th>
        <th style="width: 60%;">Statistics</th>
      </tr>
      <tr>
        <td style="vertical-align:top; border:1px solid black;">

          <?php if ($isManager) { ?>
            <form action="processAddressUpdate.php" method="post">
              <table style="margin: 0px auto; border: 0px; border-collapse:separate;">
                <tr>
                  <td style="text-align: right; background: lightblue;">First Name</td>
                  <td><input type="text" name="firstName" value="" size="35" maxlength="250"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Last Name</td>
                  <td><input type="text" name="lastName" value="" size="35" maxlength="250"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Street</td>
                  <td><input type="text" name="street" value="" size="35" maxlength="250"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">City</td>
                  <td><input type="text" name="city" value="" size="35" maxlength="250"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">State</td>
                  <td><input type="text" name="state" value="" size="35" maxlength="100"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Country</td>
                  <td><input type="text" name="country" value="" size="20" maxlength="250"/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Zip</td>
                  <td><input type="text" name="zipCode" value="" size="10" maxlength="10"/></td>
                </tr>

                <tr>
                  <td colspan="2" style="text-align: center;">
                    <input type="submit" value="Add/Update Address" />
                  </td>
                </tr>
              </table>
            </form>
          <?php } else { ?>
            <p style="text-align:center; padding:20px;">
              Only managers may add new player address records.
            </p>
          <?php } ?>

        </td>

        <td style="vertical-align:top; border:1px solid black;">

          <?php if ($isCoach) { ?>
            <form action="processStatisticUpdate.php" method="post">
              <table style="margin: 0px auto; border: 0px; border-collapse:separate;">
                <tr>
                  <td style="text-align: right; background: lightblue;">Existing Statistic Record</td>
                  <td>
                    <select name="stat_id" required>
                      <option value="" selected disabled hidden>Choose existing statistic record</option>
                      <?php
                        foreach ($statRows as $s) {
                          $label = $s['StatID'] . " - " .
                                   $s['Last'] . ", " . $s['First'] .
                                   " (" .
                                   $s['Min'] . ":" . str_pad((string)$s['Sec'], 2, "0", STR_PAD_LEFT) .
                                   ", P:" . $s['Points'] .
                                   ", A:" . $s['Assists'] .
                                   ", R:" . $s['Rebounds'] . ")";
                          echo '<option value="' . h($s['StatID']) . '">' . h($label) . '</option>';
                        }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Playing Time (min:sec)</td>
                  <td><input type="text" name="time" value="" size="5" maxlength="5" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Points Scored</td>
                  <td><input type="text" name="points" value="" size="3" maxlength="3" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Assists</td>
                  <td><input type="text" name="assists" value="" size="2" maxlength="2" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Rebounds</td>
                  <td><input type="text" name="rebounds" value="" size="2" maxlength="2" required/></td>
                </tr>

                <tr>
                  <td colspan="2" style="text-align: center;">
                    <input type="submit" value="Update Statistic" />
                  </td>
                </tr>
              </table>
            </form>
          <?php } else { ?>
            <form action="processStatisticUpdate.php" method="post">
              <table style="margin: 0px auto; border: 0px; border-collapse:separate;">
                <tr>
                  <td style="text-align: right; background: lightblue;">Name (Last, First)</td>
                  <td>
                    <select name="name_ID" required>
                      <option value="" selected disabled hidden>Choose player's name here</option>
                      <?php
                        foreach ($rows as $r) {
                          $nameStr = $r['Last'] . ", " . $r['First'];

                          if ($isManager) {
                            echo "<option value=\"" . h($r['ID']) . "\">" . h($nameStr) . "</option>";
                          }

                          if ($isPlayer && (int)$r['ID'] === (int)$roster_id) {
                            echo "<option value=\"" . h($r['ID']) . "\" selected>" . h($nameStr) . "</option>";
                          }
                        }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Playing Time (min:sec)</td>
                  <td><input type="text" name="time" value="" size="5" maxlength="5" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Points Scored</td>
                  <td><input type="text" name="points" value="" size="3" maxlength="3" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Assists</td>
                  <td><input type="text" name="assists" value="" size="2" maxlength="2" required/></td>
                </tr>

                <tr>
                  <td style="text-align: right; background: lightblue;">Rebounds</td>
                  <td><input type="text" name="rebounds" value="" size="2" maxlength="2" required/></td>
                </tr>

                <tr>
                  <td colspan="2" style="text-align: center;">
                    <input type="submit" value="Add Statistic" />
                  </td>
                </tr>
              </table>
            </form>
          <?php } ?>

        </td>
      </tr>
    </table>

    <h2 style="text-align:center">Player Statistics</h2>

    <?php
      echo "<p style=\"text-align:center\">(" . count($rows) . " records)</p>";
    ?>

    <table style="border:1px solid black; border-collapse:collapse;">
      <tr>
        <th colspan="1" style="vertical-align:top; border:1px solid black; background: lightgreen;"></th>
        <th colspan="2" style="vertical-align:top; border:1px solid black; background: lightgreen;">Player</th>
        <th colspan="1" style="vertical-align:top; border:1px solid black; background: lightgreen;"></th>
        <th colspan="4" style="vertical-align:top; border:1px solid black; background: lightgreen;">Statistic Averages</th>
      </tr>
      <tr>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;"></th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Name</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Address</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Games Played</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Time on Court</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Points Scored</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Number of Assists</th>
        <th style="vertical-align:top; border:1px solid black; background: lightgreen;">Number of Rebounds</th>
      </tr>

      <?php
        $rowNum = 0;

        foreach ($rows as $r) {
          $rowNum++;

          $nameStr = $r['Last'] . ", " . $r['First'];

          $address = new Address(
            $nameStr,
            $r['Street'],
            $r['City'],
            $r['State'],
            $r['Country'],
            $r['Zip']
          );

          $gp  = (int)$r['GamesPlayed'];
          $min = avg_int($r['AvgMin']);
          $sec = avg_int($r['AvgSec']);
          $pts = avg_int($r['AvgPoints']);
          $ast = avg_int($r['AvgAssists']);
          $reb = avg_int($r['AvgRebounds']);

          $stat = null;
          if ($gp > 0) {
            $timeStr = ($min === null ? 0 : $min) . ":" . str_pad((string)($sec === null ? 0 : $sec), 2, "0", STR_PAD_LEFT);

            $stat = new PlayerStatistic(
              $nameStr,
              $timeStr,
              ($pts === null ? 0 : $pts),
              ($ast === null ? 0 : $ast),
              ($reb === null ? 0 : $reb)
            );
          }

          echo "<tr>";
          echo "<td style=\"vertical-align:top; border:1px solid black;\">{$rowNum}</td>";
          echo "<td style=\"vertical-align:top; border:1px solid black;\">" . h($nameStr) . "</td>";

          $addrHtml =
            h($address->street()) . "<br/>" .
            h($address->city()) . ", " . h($address->state()) . " " . h($address->zip()) . "<br/>" .
            h($address->country());

          echo "<td style=\"vertical-align:top; border:1px solid black;\">{$addrHtml}</td>";
          echo "<td style=\"vertical-align:top; border:1px solid black;\">{$gp}</td>";

          if ($gp === 0) {
            echo "<td style=\"border:1px solid black; background:#e6e6e6;\"></td>";
            echo "<td style=\"border:1px solid black; background:#e6e6e6;\"></td>";
            echo "<td style=\"border:1px solid black; background:#e6e6e6;\"></td>";
            echo "<td style=\"border:1px solid black; background:#e6e6e6;\"></td>";
          } else {
            echo "<td style=\"vertical-align:top; border:1px solid black;\">" . h($stat->playingTime()) . "</td>";
            echo "<td style=\"vertical-align:top; border:1px solid black;\">" . h($stat->pointsScored()) . "</td>";
            echo "<td style=\"vertical-align:top; border:1px solid black;\">" . h($stat->assists()) . "</td>";
            echo "<td style=\"vertical-align:top; border:1px solid black;\">" . h($stat->rebounds()) . "</td>";
          }

          echo "</tr>";
        }
      ?>
    </table>

    <?php
      if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
      }
    ?>

  </body>
</html>
