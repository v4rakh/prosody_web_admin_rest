<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager;
use Monolog\Logger;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class BootstrapHelper
{
    /**
     * Bootstraps eloquent database
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function bootDatabase()
    {
        $config = Config::$CONFIG['db_settings'];
        $path = $config['database'];

        // create database file of non-existent
        if (!file_exists($path)) {
            fopen($path, 'w') or die('Unable to write database file.');
        }

        $capsule = new Manager();

        $capsule->addConnection([
            'driver' => $config['driver'],
            'database' => $path,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ], 'default');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }

    /**
     * Bootstrap env variables
     *
     * @return array
     * @throws Exception
     */
    public static function bootEnvironment()
    {
        $envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config';
        $envFile = 'env';

        $exists = is_file($envPath . DIRECTORY_SEPARATOR . $envFile);
        if (!$exists) {
            die('Configure your environment in ' . $envPath . '.');
        } else {
            $env = new Dotenv($envPath, $envFile);
            $res = $env->load();

            return $res;
        }
    }

    /**
     * Bootstrap translator
     *
     * @return Translator
     */
    public static function bootTranslator()
    {
        $translator = new Translator('en_EN', new MessageSelector());
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__ . DIRECTORY_SEPARATOR . '../../data/locale/messages.en.yml', 'en_EN');
        $translator->setFallbackLocales(['en']);

        return $translator;
    }

    /**
     * Bootstrap logger
     *
     * @return \Monolog\Logger
     */
    public static function bootLogger()
    {
        $logName = getenv('log_name');
        $logLevel = getenv('log_level');

        switch ($logLevel) {
            case 'DEBUG':
                $logLevelTranslated = Logger::DEBUG;
                break;
            case 'INFO':
                $logLevelTranslated = Logger::INFO;
                break;
            case 'NOTICE':
                $logLevelTranslated = Logger::NOTICE;
                break;
            case 'WARNING':
                $logLevelTranslated = Logger::WARNING;
                break;
            case 'ERROR';
                $logLevelTranslated = Logger::ERROR;
                break;
            case 'CRITICAL':
                $logLevelTranslated = Logger::CRITICAL;
                break;
            case 'ALERT':
                $logLevelTranslated = Logger::ALERT;
                break;
            case 'EMERGENCY':
                $logLevelTranslated = Logger::EMERGENCY;
                break;
            default:
                $logLevelTranslated = Logger::DEBUG;
        }

        $logPath = Config::$CONFIG['logger_settings']['path'];

        $logger = new Monolog\Logger($logName);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($logPath, $logLevelTranslated));
        $logger->pushHandler(new \Monolog\Handler\ErrorLogHandler(NULL, $logLevelTranslated));

        return $logger;
    }
}