<?php
/**
 * Including components.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PUBLIC_PATH', __DIR__);
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../Borraz/Datafeed.php';

$dataFeed = new Borraz\Datafeed();
$dataFeed->refresh();