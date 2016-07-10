<?php

use Monolog\Logger;

class Config
{
    public static $CONFIG =
        [
            // no need to change anything here
            'db_settings' => [
                'driver' => 'sqlite',
                'database' => __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR .'db.sqlite',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ],

            'slim_settings' => [
                'displayErrorDetails' => true,
                'determineRouteBeforeAppMiddleware' => true,
            ],

            'twig_settings' => [
                'twig_dir' => __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'View',
                'twig_cache_dir' => false,
                //'twig_cache_dir'          => __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR .'cache',
            ],

            'logger_settings' => [
                'level' => Logger::DEBUG,
                'name' => 'application',
                'path' => __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'log'. DIRECTORY_SEPARATOR .'application.log',
            ],
        ];
}