<?php

session_start();
$requestUri = $_SERVER['REQUEST_URI'];

$uri = parse_url($requestUri, PHP_URL_PATH);

switch ($uri) {
  case '/':
    require 'pages/home.php';
    break;
  case '/about':
    require 'pages/about.php';
    break;
  case '/contact':
    require 'pages/contact.php';
    break;
  case '/register':
    require 'pages/register.php';
    break;
  case '/login':
    require 'pages/login.php';
    break;
  case '/logout':
    // check if user is logged in
    if (isset($_SESSION['loggedin'])) {
      require 'pages/logout.php';
    } else {
      // Redirect to login page or home page
      require 'pages/home.php';
    }
    break;
  case '/account':
    // check if user is logged in
    if (isset($_SESSION['loggedin'])) {
      require 'pages/account.php';
    } else {
      // Redirect to login page or home page
      require 'pages/home.php';
    }
    break;
  case '/cart':
    require 'pages/shoppingCart.php';
    break;
  case '/checkout':
    if (isset($_SESSION['loggedin'])) {
      require 'pages/checkout.php';
    } else {
      // Redirect to login page or home page
      require 'pages/home.php';
    }
    break;
  default:
    // handle not found
    header("HTTP/1.1 404 Not Found");
    require 'pages/404.php';
}
