<?php

// define routes

$routes = array(
    array( // Homepage
        "method" => "GET",
        "pattern" => "home",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "method" => array("GET","POST"),
        "pattern" => "home/test",
        "controller" => "home",
        "action" => "test"
    ),
    array( // Users/Login
        "method" => array("GET","POST"),
        "pattern" => "users/login",
        "controller" => "users",
        "action" => "login"
    ),
    array( // Users/Profile
        "method" => "GET",
        "pattern" => "users/profile",
        "controller" => "users",
        "action" => "profile"
    ),
    array( // Users/Settings
        "method" => array("GET", "POST"),
        "pattern" => "users/settings",
        "controller" => "users",
        "action" => "settings"
    ),
    array(
        "method" => array("GET", "POST"),
        "pattern" => "admin",
        "controller" => "admin",
        "action" => "index"
    ),
     array( // Walks/View
        "method" => array("GET","POST"),
        "pattern" => "walks/view/:id",
        "controller" => "walks",
        "action" => "view"
    ),
    array( // Walks/Edit
        "method" => array("GET","POST"),
        "pattern" => "walks/edit/:id",
        "controller" => "walks",
        "action" => "edit"
    ),
    array(
        "method" => array("GET", "POST"),
        "pattern" => "walks/create",
        "controller" => "walks",
        "action" => "create"
    ),
    array( // Books/View
        "method" => "GET",
        "pattern" => "books/view/:id",
        "controller" => "books",
        "action" => "view"
    ),
    array( // Books/Edit
        "method" => array("GET","POST"),
        "pattern" => "books/edit/:id",
        "controller" => "books",
        "action" => "edit"
    ),

    array(
        'method' => array("GET","POST"),
        "pattern" => "testForm/index",
        "controller" => "testForm",
        "action" => "index"
    )
);

// add defined routes

foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals

unset($routes);
