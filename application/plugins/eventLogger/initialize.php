<?php
include("logger.php");

$logger = new Logger(array(
		'file' => path('storage') . "logs/events/" . date("Y-m-d") . ".log"
	));

	// log controller events
	Framework\Event::add("framework.controller.construct.before", function($name) use ($logger) {
	    $logger->log("framework.controller.construct.before: " . $name);
	});

	Framework\Event::add("framework.controller.construct.after", function($name) use ($logger) {
	    $logger->log("framework.controller.construct.after: " . $name);
	});

	Framework\Event::add("framework.controller.render.before", function($name) use ($logger) {
	    $logger->log("framework.controller.render.before: " . $name);
	});

	Framework\Event::add("framework.controller.render.after", function($name) use ($logger) {
	    $logger->log("framework.controller.render.after: " . $name);
	});

	Framework\Event::add("framework.controller.destruct.before", function($name) use ($logger) {
	    $logger->log("framework.controller.destruct.before: " . $name);
	});

	Framework\Event::add("framework.controller.destruct.after", function($name) use ($logger) {
	    $logger->log("framework.controller.destruct.after: " . $name);
	});

	// log database events
	Framework\Event::add("framework.database.connect.before", function($type) use ($logger) {
	    $logger->log("framework.database.connect.before: " . $type);
	});

	Framework\Event::add("framework.database.connect.after", function($type) use ($logger) {
	    $logger->log("framework.database.connect.after: " . $type);
	});

	// log request events
	Framework\Event::add("framework.request.request.before", function($method, $url, $parameters) use ($logger) {
	    $logger->log("framework.request.request.before: " . $method . ", " . $url);
	});

	Framework\Event::add("framework.request.request.after", function($method, $url, $parameters, $response) use ($logger) {
	    $logger->log("framework.request.request.after: " . $method . ", " . $url);
	});

	// log router events
	Framework\Event::add("framework.router.initialize.before", function($url) use ($logger) {
	    $logger->log("framework.router.initialize.before: " . $url);
	});

	Framework\Event::add("framework.router.initialize.after", function($url, $method, $controller, $action, $parameters) use ($logger) {
	    $logger->log("framework.router.initialize.after: " . $url . ", " . $method . ", " . $controller . ", " . $action);
	});

	Framework\Event::add("framework.router.controller.before", function($controller, $parameters) use ($logger) {
		$logger->log("framework.router.controller.before: " . $controller);
	});

	Framework\Event::add("framework.router.controller.after", function($controller, $parameters) use ($logger) {
		$logger->log("framework.router.controller.after: " . $controller);
	});

	Framework\Event::add("framework.router.beforehooks.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.beforehooks.before: " . $action);
	});

	Framework\Event::add("framework.router.beforehooks.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.beforehooks.after: " . $action);
	});

	Framework\Event::add("framework.router.action.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.action.before: " . $action);
	});

	Framework\Event::add("framework.router.action.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.action.after: " . $action);
	});

	Framework\Event::add("framework.router.afterhooks.before", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.afterhooks.before: " . $action);
	});

	Framework\Event::add("framework.router.afterhooks.after", function($action, $parameters) use ($logger) {
		$logger->log("framework.router.afterhooks.after: " . $action);
	});

	// log session events
	Framework\Event::add("framework.session.initialize.before", function($type, $options) use ($logger) {
	    $logger->log("framework.session.initialize.before: " . $type);
	});

	Framework\Event::add("framework.session.initialize.after", function($type, $options) use ($logger) {
	    $logger->log("framework.session.initialize.after: " . $type);
	});

	// log view events
	Framework\Event::add("framework.view.construct.before", function($file) use ($logger) {
	    $logger->log("framework.view.construct.before: " . $file);
	});

	Framework\Event::add("framework.view.construct.after", function($file, $template) use ($logger) {
	    $logger->log("framework.view.construct.after: " . $file);
	});

	Framework\Event::add("framework.view.render.before", function($file) use ($logger) {
	    $logger->log("framework.view.render.before: " . $file);
	});

	// log framework finish
	Framework\Event::add("framework.done", function($url) use ($logger) {
			$logger->log("framework.done: ". $url);
		});