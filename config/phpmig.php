<?php

use \Phpmig\Adapter;

$container = new ArrayObject();
$container['env'] = EnvironmentHelper::getAppEnvironment();
$container['db'] = DatabaseHelper::getAppDatabase();

$container['phpmig.adapter'] = new Phpmig\Adapter\PDO\Sql($container['db']->getConnection()->getPdo(), 'migrations');

$container['phpmig.migrations_template_path'] = __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR .'phpmig_template.php';
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR .'migrations';


$container['schema'] = $container['db']->schema();

return $container;