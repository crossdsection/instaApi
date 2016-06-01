<?php

define("DBNAME", "test");
define("USERNAME", "cross");
define("PASSWORD", "password");
define("HOST", "localhost");

// Create connection
$conn = new mysqli( HOST, USERNAME, PASSWORD );

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_error() . PHP_EOL );
} 
?>