<?php

session_unset(); // remove all session variables
session_destroy(); // destroy the session

// Redirect to login page or home page
header("Location: /");
require 'pages/home.php';
