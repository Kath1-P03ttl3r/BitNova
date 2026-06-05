<?php
// simple logout - clears session and redirects to landing
// We call session_start() to ensure session exists, then clear all session data
session_start();
// clear session variables and destroy session
session_unset();
session_destroy();
// optionally one might clear the session cookie as well; not strictly necessary here
header('Location: index.php');
exit;
