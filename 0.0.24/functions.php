<?php

require_once 'config.php'; // Include config.php to access the constants

ini_set( 'session.cookie_httponly', 1 ); // Protect session cookies
session_start(); // Start the session

include 'run_q.php'; // Include the database function
include 'functions/redirect.php'; // Include the redirect function
include 'functions/Parsedown.php'; // Include the Parsedown library for Markdown

// Other functions (if any)

?>