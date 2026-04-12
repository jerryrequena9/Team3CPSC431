<?php

/*
-------------------------------------------------------
HW3 PASSWORD HASH GENERATOR
-------------------------------------------------------

This script generates secure password hashes for the
Users table using PHP's password_hash() function.

Algorithm Used:
    PASSWORD_DEFAULT

Currently this corresponds to the bcrypt hashing
algorithm (Blowfish).

Security features:
    • Automatic salting
    • Adjustable cost factor
    • Resistant to rainbow table attacks
    • Safe for password storage

Example hash format:
    $2y$10$...
*/

$passwords = [
    "manager123",
    "coach123",
    "donald123",
    "mickey123",
    "louie123"
];

foreach ($passwords as $p) {
    echo $p . " → " . password_hash($p, PASSWORD_DEFAULT) . "<br>";
}

?>
