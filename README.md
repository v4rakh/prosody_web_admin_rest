# README #

A simple webinterface for users. This app uses [`admin_rest`](https://github.com/snowblindroan/mod_admin_rest) module of prosody. So [prosody.im](http://prosody.im) and this module are hard dependencies. The interface allows users

* to have two step verification (as an alternative to the integrated `register_web` module),
* to delete of their accounts and
* to change their password.

This app uses

* Slim Version 3
* Slim Auth
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
* Copy `config/legal.example.md` to `config/legal.md` and adjust to your needs
* `composer install`
* `php bin/phpmig migrate`

## Deployment ##

* Set up a cron job using `php projectRootDir/bin/UsersAwaitingVerificationCleanUpCronJob.php` to clean up users who signed up but did not verify their account periodically.
* Point your document root to `public/`.
* Example nginx conf:

```  
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
```  

You should be able to set a very strict Content-Security-Policy.

## Upgrade ##

* Change directory to project home
* `git pull`
* `composer update`
* `php bin/phpmig migrate`
* look into Changelog for major changes

## Developers ##
* start server with `php -S localhost:8080 -t public public/index.php`
* point browser to [localhost:8080](http://localhost:8080) to have a preview

## Translations ##
This app uses Symfony Translator. It's bootstraped in `Util\BootstrapHelper` and locales are placed under `data/locale/`. Adjust to your needs or help translating.

## Changelog ##
- 0.3.0.1
    - Remove cookie consent as session cookies should be allowed because they provide core functionality
    - Adjust `legal.example.md` and add `PHPSESSID`
    - Fix styles
- 0.3.0.0
    - Fixes
    - Cookie consent
- 0.2.0.1
    - Design fixes
    - GDPR adjustments (specify agreement in a `.md` file)
- 0.1.3.3 to 0.2.0.0
    - update to latest yaml
    - Force to lower on email and username
    - Update README and htaccess for Apache
    - Change to bootstrap4 theme
    - Add legal information hint on sign up page
- 0.1.3.2:
    - Refactor
    - Bugfixes
    - Add possibility to determine LogLevel and logger name in environment

- 0.1.3.1:
    - Bugfixes

- 0.1.3:
    - add authentication after sign up
    - only logged in users can delete their account (with the help of the token)
    - only logged in users can change their account password

- 0.1.2:
    - Bugfixes

- 0.1.1:
    - updated readme and `env.example`
    - fix some language validator inconsistencies
    - added admin notifications
    - added possibility for users to delete their account
    - added back index page
    - works with mod_admin_rest version [afc42d7](https://github.com/snowblindroan/mod_admin_rest/commit/afc42d70f0aceb2351a1bc786d61e3f4dbdfb948)
- 0.1: 
    - initial release
    - works with mod_admin_rest version [afc42d7](https://github.com/snowblindroan/mod_admin_rest/commit/afc42d70f0aceb2351a1bc786d61e3f4dbdfb948)