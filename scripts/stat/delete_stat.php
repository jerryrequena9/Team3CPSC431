<?php
  require_once(__DIR__ . '/../StartSession.php');
  require_once(__DIR__ . '/../helpers.php');
  require_once(__DIR__ . '/../../pages/html_components.php');

  check_valid_user();

  if (!is_valid_post($_POST)) {
    error("Required fields are missing", "../../pages/user_page.php");
  }

  $stat_id = intval($_POST['delete_stat_id']);
  
  // Validate stat_id is a positive integer
  if ($stat_id <= 0) {
    error("Invalid stat ID", "../../pages/stat_page.php");
  }

  global $db;

  /**
   * Get the current user's role_id to determine permission level.
   * We query this dynamically rather than hardcoding role names.
   */
  $query = "
    SELECT r.role_id
    FROM UserAccount u
    JOIN Role r ON u.role_id = r.role_id
    WHERE u.user_id = ?
  ";
  
  $stmt = prepare_with_perms($db, $query);
  $stmt->bind_param("i", $_SESSION['UserID']);
  
  try {
    $stmt->execute();
    $stmt->bind_result($user_role_id);
    if (!$stmt->fetch()) {
      error("User not found", "../../pages/stat_page.php");
    }
  } catch (mysqli_sql_exception $e) {
    error("Could not determine user role", "../../pages/stat_page.php");
  } finally {
    $stmt->close();
  }

  /**
   * Get the Coach role_id for comparison.
   * This allows us to check permissions without hardcoding role names.
   */
  $query = "
    SELECT role_id
    FROM Role
    WHERE name = 'Coach'
  ";
  
  $coach_role_id_result = query_with_perms($db, $query);
  $coach_role = $coach_role_id_result->fetch_assoc();
  $coach_role_id = intval($coach_role['role_id']);

  /**
   * Permission-based delete logic:
   * - Coaches can only delete stats for players on their team
   * - Other roles can delete any stat (with database-level permission enforcement)
   */
  if ($user_role_id == $coach_role_id) {
    // Coach-level delete: restrict to their own team
    $coach_team_id = isset($_SESSION['team_id']) ? intval($_SESSION['team_id']) : 0;
    
    if ($coach_team_id <= 0) {
      error("Coach not assigned to a team", "../../pages/stat_page.php");
    }

    /*
     * Coaches may only delete stats for players assigned to their own team.
     * This prevents a Coach from changing the hidden stat_id and deleting
     * another team's player stat.
     */
    $query = "
      DELETE s
      FROM Stat s
      INNER JOIN Player_Team pt ON s.player_id = pt.player_id
      WHERE s.stat_id = ?
        AND pt.team_id = ?
        AND pt.end_date IS NULL
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ii", $stat_id, $coach_team_id);
    
    try {
      $stmt->execute();
      if ($stmt->affected_rows == 0) {
        $stmt->close();
        error("Stat not found or you do not have permission to delete it", "../../pages/stat_page.php");
      }
    } catch (mysqli_sql_exception $e) {
      error("Stat not deleted", "../../pages/stat_page.php");
    } finally {
      $stmt->close();
    }
  } else {
    /*
     * Manager-level delete:
     * Managers and other roles can delete any stat, but the database account 
     * still needs DELETE permission on the Stat table for MySQL-level enforcement.
     */
    $query = "
      DELETE s
      FROM Stat s
      WHERE s.stat_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $stat_id);

    try {
      $stmt->execute();
      if ($stmt->affected_rows == 0) {
        $stmt->close();
        error("Stat not found", "../../pages/stat_page.php");
      }
    } catch (mysqli_sql_exception $e) {
      error("Stat not deleted", "../../pages/stat_page.php");
    } finally {
      $stmt->close();
    }
  }

  success("Stat deleted", "../../pages/stat_page.php");
?>

