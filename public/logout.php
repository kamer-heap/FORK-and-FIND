<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requirePost();                 // logout must be a POST so other sites cannot log users out
logoutUser();
session_start();               // fresh session just to carry the flash message
setFlash('success', 'You have been logged out.');
redirect('/login.php');
