<?php

use Slim\Http\Request;
use Slim\Http\Response;

// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

require __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

/**
 * Step 2: Bootstrap database, ACL, Twig, FlashMessages, Logger
 */
$app = new Slim\App(['settings' => Config::$CONFIG['slim_settings']]);

// Dependencies/Container
$container = $app->getContainer();

// Environment
$env = EnvironmentHelper::getAppEnvironment();
$container['env'] = function() use ($env) {
    return $env;
};

// Config
$container['config'] = function() {
    return Config::$CONFIG;
};

// Database
$capsule = DatabaseHelper::bootORM();
$container['db'] = function () use ($capsule) {
    return $capsule;
};

// Translation
$translator = TranslationHelper::getAppTranslator();
$container['translator'] = function () use ($translator) {
    return $translator;
};

// Logger
$container['logger'] = function () {
    $logger = LoggerHelper::getAppLogger();
    return $logger;
};

// View
$container['flash'] = function () {
    return new Slim\Flash\Messages;
};
$container['view'] = function ($container) use ($translator) {
    $view = new \Slim\Views\Twig(Config::$CONFIG['twig_settings']['twig_dir'], [
        'cache' => Config::$CONFIG['twig_settings']['twig_cache_dir']
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    $view->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($translator));
    $view->getEnvironment()->addFunction(new Twig_SimpleFunction('getenv', function($value) {
        $res = getenv($value);
        return $res;
    }));
    $view['flash'] = $container['flash'];
    $view['config'] = $container['config'];
    return $view;
};

// Error handling
$container['notFoundHandler'] = function ($container) {
    return function (Request $request, Response $response) use ($container) {
        return $response->withRedirect('404');
    };
};
$container['errorHandler'] = function ($container) {
    return function (Request $request, Response $response, $exception) use ($container) {
        $container['logger']->error($container['translator']->trans('log.internal.application.error'), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'previous' => $exception->getPrevious(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return $response->withRedirect('500');
    };
};

/**
 * Step 3: Define the Slim application routes
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '../config/Routes.php';

/**
 * Step 4: Start a session (flash messages) and run the Slim application
 */
session_start();
$app->run();
