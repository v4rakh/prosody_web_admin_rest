# README #

A simple WebUI for [`admin_rest`](https://github.com/snowblindroan/mod_admin_rest) module of prosody allowing 2 step verification of new user accounts (as an alternative to the integrated `register_web` module).

This app uses

* Slim Version 3
* Eloquent ORM
* PHPMigration
* GUMP Validation
* Twig
* Curl
* PHPMailer
* Symfony Translation
* Sqlite

as dependencies.

## Requirements ##

* admin_rest module of prosody
* composer
* sqlite pdo, mb_string

## Install ##

* Install composer
* Change directory to project home
* Copy `config/env.example` to `config/env` and adjust to your needs
* `composer install`
* `php bin/phpmig migrate`
* start server with `php -S localhost:8080 -t public public/index.php`
* point browser to [localhost:8080](http://localhost:8080) to have a preview

## Deployment ##

* Set up a cron job using `php projectRootDir/bin/UsersAwaitingVerificationCleanUpCronJob.php` to clean up users who signed up but did not verify their account periodically.
* Point your document root to `public/`.
* Example nginx conf:
  
      root   .../public;
      index index.php;    
      
      rewrite_log on;
      
      location / {
          try_files $uri $uri/ @ee;
      }
      
      location @ee {
          rewrite ^(.*) /index.php?$1 last;
      }
      
      # php fpm
      location ~ \.php$ {
          fastcgi_split_path_info ^(.+\.php)(/.+)$;
          fastcgi_pass   unix:/var/run/php-fpm/php-fpm.sock;
          include        fastcgi_params;
      }    

## Upgrade ##

* Change directory to project home
* `git pull`
* `composer update`
* `php bin/phpmig migrate`

## Translations ##
This app uses Symfony Translator. It's bootstraped in `Util\TranslationHelper` and locales are placed under `data/locale/`. Adjust to your needs or help translating.