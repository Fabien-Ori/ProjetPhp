<?php
/**
 * Database connection - single place to avoid repeating connection code.
 * Uses php_exam_db as per project spec. Replace "root" with "" if your MySQL has no password.
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';  // Use '' if no password
$db_name = 'php_exam_db';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
