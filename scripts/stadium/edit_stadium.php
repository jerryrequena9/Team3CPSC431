<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/stadium_page.php");
    }

    global $db;

    $stadium_id = intval($_POST['stadium_id']);
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);

    // Update a stadium
    $query = "
        UPDATE Stadium
        SET name = ?, city = ?
        WHERE stadium_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ssi", $name, $city, $stadium_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            error("Stadium not updated", "../../pages/stadium_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            error("Stadium already exists", "../../pages/stadium_page.php");
        } else {
            error("Stadium not updated", "../../pages/stadium_page.php");
        }
    } finally {
        $stmt->close();
    }

    success("Stadium updated", "../../pages/stadium_page.php");
?>
