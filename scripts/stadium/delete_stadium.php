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

    // Delete a stadium
    $query = "
        DELETE FROM Stadium
        WHERE stadium_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $stadium_id);
    try {
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            error("Stadium not deleted", "../../pages/stadium_page.php");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            error('Stadium not deleted. Stadium is referenced by another entity', "../../pages/stadium_page.php");
        }
    } finally {
        $stmt->close();
    }
    success('Stadium deleted', '../../pages/stadium_page.php');
?>
