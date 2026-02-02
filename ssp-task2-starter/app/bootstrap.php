<?php
// Base path from index.php; fallback if called directly
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Colombo');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once APP_PATH.'/Database.php';
require_once APP_PATH.'/Views/helpers.php';
