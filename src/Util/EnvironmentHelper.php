<?php

use Dotenv\Dotenv;

class EnvironmentHelper
{
    /**
     * @return array
     * @throws Exception
     */
    public static function getAppEnvironment()
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
}