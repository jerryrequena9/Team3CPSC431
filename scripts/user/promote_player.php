<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/user_page.php");
    }

    $user_id = intval($_POST['user_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $position = trim($_POST['position']);

    global $db;

    // Check that the user is not a coach
    $query = "
        SELECT COUNT(*)
        FROM Coach
        WHERE user_id = ?
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $user_id);
    try {
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error("User not promoted", "../../pages/user_page.php");
    }
    if ($count !== 0) {
        error("User is a coach", "../../pages/user_page.php");
    }

    // Promote user to coach
    $query = "
        INSERT INTO Player
            (user_id, first_name, last_name, position, status)
        VALUES (?, ?, ?, ?, 'Active');
    ";
    try {
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("isss", $user_id, $first_name, $last_name, $position);
        $stmt->execute();
        $stmt->close();

        success("User promoted to Player", "../../pages/user_page.php");

    } catch (mysqli_sql_exception $e) {
        error("User not promoted", "../../pages/user_page.php");
    }
?>
