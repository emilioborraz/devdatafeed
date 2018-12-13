<?php

namespace Borraz;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Dotenv\Dotenv;

class Bootstrap{
	/**
	 * Runs the application using a simple function for routing & execution
	 * @return void
	 */
	public function run(){
		$this->loadEnvVariables();
		$this->setErrorSettings();
		$this->serve();
	}

	protected function serve(){
		$routes = new RouteCollection();
		$context = new RequestContext();

		$context->fromRequest(Request::createFromGlobals());

		$getDataFeedRoute = new Route('/', 
			array('_class' => 'Borraz\Datafeed',
			'_method' => 'get'));

		$routes->add('getDataAction', $getDataFeedRoute);

		$matcher = new UrlMatcher($routes, $context);

		try {
			$parameters = $matcher->match($context->getPathInfo());
			$classHandler = new $parameters['_class']();
			$classHandler->{$parameters['_method']}();
		} catch (ResourceNotFoundException $e) {
			$notFoundResponse = new Response('This may exists in a different dimension :)', Response::HTTP_NOT_FOUND);
			$notFoundResponse->send();
			die();
		}
	}

	public function loadEnvVariables(){
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../.env');
	}

	protected function setErrorSettings(){
		if(!empty(getenv(APP_ENV)) &&
			getenv(APP_ENV) !== 'production'){
				error_reporting(E_ALL);
				ini_set('display_errors', 1);
		}
	}
}