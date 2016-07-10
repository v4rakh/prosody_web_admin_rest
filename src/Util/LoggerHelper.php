<?php

class LoggerHelper
{
    public static function getAppLogger()
    {
        $config = Config::$CONFIG['logger_settings'];
        $logger = new Monolog\Logger($config['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($config['path'], Config::$CONFIG['logger_settings']['level']));
        $logger->pushHandler(new \Monolog\Handler\ErrorLogHandler(NULL, Config::$CONFIG['logger_settings']['level']));
        return $logger;
    }
}