<?php
require_once '../includes/functions.php';

session_start();

logoutUser();

header('Location: ../index.php');
exit;
