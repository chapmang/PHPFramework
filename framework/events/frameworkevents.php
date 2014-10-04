<?php
namespace Framework {

	use Framework\Events\Logger as Logger;

	$logger = new Logger(array(
		'file' => path('storage') . "logs/events/" . date("Y-m-d") . ".log"
	));

	// log controller events
	Event::add("framework.controller.construct.before", function($name) use ($logger) {
	    $logger->log("framework.controller.construct.before: " . $name);
	});

	Event::add("framework.controller.construct.after", function($name) use ($logger) {
	    $logger->log("framework.controller.construct.after: " . $name);
	});

	Event::add("framework.controller.render.before", function($name) use ($logger) {
	    $logger->log("framework.controller.render.before: " . $name);
	});

	Event::add("framework.controller.render.after", function($name) use ($logger) {
	    $logger->log("framework.controller.render.after: " . $name);
	});

	Event::add("framework.controller.destruct.before", function($name) use ($logger) {
	    $logger->log("framework.controller.destruct.before: " . $name);
	});

	Event::add("framework.controller.destruct.after", function($name) use ($logger) {
	    $logger->log("framework.controller.destruct.after: " . $name);
	});

	// log database events
	Event::add("framework.database.connect.before", function($type) use ($logger) {
	    $logger->log("framework.database.connect.before: " . $type);
	});

	Event::add("framework.database.connect.after", function($type) use ($logger) {
	    $logger->log("framework.database.connect.after: " . $type);
	});

	// log request events
	Event::add("framework.request.request.before", function($method, $url, $parameters) use ($logger) {
	    $logger->log("framework.request.request.before: " . $method . ", " . $url);
	});

	Event::add("framework.request.request.after", function($method, $url, $parameters, $response) use ($logger) {
	    $logger->log("framework.request.request.after: " . $method . ", " . $url);
	});

	// log router events
	Event::add("framework.router.initialize.before", function($url) use ($logger) {
	    $logger->log("framework.router.initialize.before: " . $url);
	});

	Event::add("framework.router.initialize.after", function($url, $method, $controller, $action, $parameters) use ($logger) {
	    $logger->log("framework.router.initialize.after: " . $url . ", " . $method . ", " . $controller . ", " . $action);
	});

	Event::add("framework.router.controller.before", function($controller, $parameters) use ($logger) {
		$logger->log("framework.router.controller.before: " . $controller);
	});

	Event::add("framework.router.controller.after", function($controller, $parameters) use ($logger) {
		$logger->log("framework.router.controller.after: " . $controller);
	});

	Event::add("framework.router.beforehooks.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.beforehooks.before: " . $action);
	});

	Event::add("framework.router.beforehooks.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.beforehooks.after: " . $action);
	});

	Event::add("framework.router.action.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.action.before: " . $action);
	});

	Event::add("framework.router.action.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.action.after: " . $action);
	});

	Event::add("framework.router.afterhooks.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.afterhooks.before: " . $action);
	});

	Event::add("framework.router.afterhooks.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.afterhooks.after: " . $action);
	});

	// log session events
	Event::add("framework.session.initialize.before", function($type, $options) use ($logger) {
	    $logger->log("framework.session.initialize.before: " . $type);
	});

	Event::add("framework.session.initialize.after", function($type, $options) use ($logger) {
	    $logger->log("framework.session.initialize.after: " . $type);
	});

	// log view events
	Event::add("framework.view.construct.before", function($file) use ($logger) {
	    $logger->log("framework.view.construct.before: " . $file);
	});

	Event::add("framework.view.construct.after", function($file, $template) use ($logger) {
	    $logger->log("framework.view.construct.after: " . $file);
	});

	Event::add("framework.view.render.before", function($file) use ($logger) {
	    $logger->log("framework.view.render.before: " . $file);
	});

	// log framework finish
	Event::add("framework.done", function($url) use ($logger) {
			$logger->log("framework.done: ". $url);
		});

}