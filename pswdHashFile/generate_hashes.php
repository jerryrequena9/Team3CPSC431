<?php
// generate_hashes.php
// Run from terminal: php generate_hashes.php

$users = [
    'manager1' => 'ManagerPass123!',
    'coach1'   => 'CoachPass123!',
    'player1'  => 'PlayerPass123!',
    'fan1'     => 'FanPass123!',
];

foreach ($users as $username => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    if ($hash === false) {
        echo "Failed to hash password for {$username}\n";
        continue;
    }

    echo "-- {$username} password: {$plainPassword}\n";
    echo "UPDATE UserAccount\n";
    echo "SET password_hash = '{$hash}'\n";
    echo "WHERE username = '{$username}';\n\n";
}
