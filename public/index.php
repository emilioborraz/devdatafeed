<?php
/**
 * Including components and running the app.
 */

define('PUBLIC_PATH', __DIR__);
require_once __DIR__.'/../vendor/autoload.php';
/**
 * @todo To create a package from the Borraz classes?
 */
require_once __DIR__.'/../Borraz/Datafeed.php';
require_once __DIR__.'/../Borraz/Bootstrap.php';

$app = new Borraz\Bootstrap();
$app->run();