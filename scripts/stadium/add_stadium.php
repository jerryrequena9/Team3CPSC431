<?php
    require_once(__DIR__ . "/../StartSession.php");
    require_once(__DIR__ . "/../../pages/html_components.php");
    require_once(__DIR__ . "/../helpers.php");

    check_valid_user();

    if (!is_valid_post($_POST)) {
        error("Required fields are missing", "../../pages/stadium_page.php");
    }

    global $db;

    $name = trim($_POST['name']);
    $city = trim($_POST['city']);

    // Create a stadium
    $query = "
        INSERT INTO Stadium
            (name, city)
        VALUES (?, ?)
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("ss", $name, $city);
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            error("Stadium already exists", "../../pages/stadium_page.php");
        } else {
            error("Stadium not added", "../../pages/stadium_page.php");
        }
    } finally {
        $stmt->close();
    }
    success("Stadium added", "../../pages/stadium_page.php");
?>
