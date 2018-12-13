<?php
/**
 * Including components and running the console app.
 */

define('PUBLIC_PATH', __DIR__.'/public');
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/Borraz/Datafeed.php';
require_once __DIR__.'/Borraz/Bootstrap.php';
require_once __DIR__.'/Borraz/Command/RefreshDevFeedData.php';

use Symfony\Component\Console\Application;
use Borraz\Command\RefreshDevFeedData;
// var_dump(file_exists(__DIR__.'/Borraz/Bootstrap.php'), __DIR__.'/Borraz/Bootstrap.php');exit;
(new \Borraz\Bootstrap())->loadEnvVariables();

$application = new Application();

$application->add(new RefreshDevFeedData());

$application->run();