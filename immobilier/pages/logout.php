<?php
require_once '../config/app.php';
session_destroy();
header('Location: ' . APP_URL . '/index.php');
exit;