<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/user_page.php");
    }

    $user_id = intval($_POST['user_id']);
    $first_name = sanitize_str($_POST['first_name']);
    $last_name = sanitize_str($_POST['last_name']);

    global $db;

    $query = "
        INSERT INTO Coach
            (user_id, first_name, last_name)
        VALUES (?, ?, ?);
    ";
    try {
        $stmt = prepare_with_perms($db, $query);
        $stmt->bind_param("iss", $user_id, $first_name, $last_name);
        $stmt->execute();
        $stmt->close();

        success("User promoted to Coach", "../../pages/user_page.php");

    } catch (mysqli_sql_exception $e) {
        error("User not promoted", "../../pages/user_page.php");
    }
?>