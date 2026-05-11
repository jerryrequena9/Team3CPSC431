<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();
    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/game_page.php");
    }

    $game_id = intval($_POST['game_id']);

    $query = "
        DELETE FROM Game
        WHERE game_id = ?
    ";

    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $game_id);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) {
            error("Game not deleted. Game is referenced by another entity", "../../pages/game_page.php");
        }
        error("Game not deleted", "../../pages/game_page.php");
    } finally {
        $stmt->close();
    }
    success("Game deleted", "../../pages/game_page.php");
?>