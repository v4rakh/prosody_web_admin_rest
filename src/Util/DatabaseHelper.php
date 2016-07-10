<?php

use Illuminate\Database\Capsule\Manager;

class DatabaseHelper
{
    public static function bootORM()
    {
        $config = Config::$CONFIG['db_settings'];
        $path = $config['database'];

        // create database file of non-existent
        if (!file_exists($path)) {
            fopen($path, 'w') or die('Unable to write database file.');
        }

        $capsule = new Manager();

        $capsule->addConnection([
            'driver'    => $config['driver'],
            'database'  => $path,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], 'default');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }
}