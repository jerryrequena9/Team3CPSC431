<?php
    require_once(__DIR__ . '/../StartSession.php');
    require_once(__DIR__ . '/../helpers.php');

    check_valid_user();

    global $db;

    if (!is_valid_post($_POST)) {
        error("Required fields missing", "../../pages/season_page.php");
    }

    $year = intval($_POST['year']);
    $team_ids = $_POST['team_ids'];

    if (!$year || !is_array($team_ids) || count($team_ids) === 0) {
        error("Year and at least one team must be selected", "../../pages/season_page.php");
    }

    $query = "
        INSERT INTO Season (year)
        VALUES (?)
    ";
    $stmt = prepare_with_perms($db, $query);
    $stmt->bind_param("i", $year);

    try {
        $stmt->execute();
        $season_id = $stmt->insert_id;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            error("Season already exists", "../../pages/season_page.php");
        }
        error("Season not created", "../../pages/season_page.php");
    } finally {
        $stmt->close();
    }

    $query = "
        INSERT INTO Team_Season
            (team_id, season_id)
        VALUES (?, ?)
    ";
    $stmt = prepare_with_perms($db, $query);
    foreach ($team_ids as $team_id) {
        $team_id = intval($team_id);
        $stmt->bind_param("ii", $team_id, $season_id);
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $stmt->close();
                error("Team is already in the season", "../../pages/season_page.php");
            }
            $stmt->close();
            error("Failed to add team to season", "../../pages/season_page.php");
        }
    }
    $stmt->close();

    success("Season added", "../../pages/season_page.php");
?>