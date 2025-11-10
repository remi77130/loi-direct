<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Test connexion MySQL</h3>";

$mysqli = new mysqli(
    'kuomkqatchatuser.mysql.db',
    'kuomkqatchatuser',
    'Champagne77', // ton mot de passe OVH exact
    'kuomkqatchatuser'
);

if ($mysqli->connect_errno) {
    echo "Erreur connexion MySQL : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
    echo "Connexion MySQL OK";
}
