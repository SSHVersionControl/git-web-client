[ABANDONED] GIT Web Interface Client [![Build Status](https://travis-ci.org/SSHVersionControl/git-web-client.svg?branch=master)](https://travis-ci.org/SSHVersionControl/git-web-client) [![Coverage Status](https://coveralls.io/repos/github/SSHVersionControl/git-web-client/badge.svg?branch=master)](https://coveralls.io/github/SSHVersionControl/git-web-client?branch=master) [![Latest Version](https://img.shields.io/github/release/SSHVersionControl/git-web-client.svg?style=flat-square)](https://github.com/SSHVersionControl/git-web-client/releases) [![Total Downloads](https://img.shields.io/packagist/dt/sshversioncontrol/git-web-client.svg?style=flat-square)](https://packagist.org/packages/sshversioncontrol/git-web-client) [![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/SSHVersionControl/git-web-client/master/LICENSE) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SSHVersionControl/git-web-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SSHVersionControl/git-web-client/?branch=master)
========================

Welcome to the GIT Web Interface Version Control Client. This systems allows you to commit,search history,
branch, push, pull, and many other action on git repositories, locate locally and on remote servers with 
SFTP/SSH access. This system has been build using full stack Symfony2 framework. It comes with an inbuilt issue tracker, to help organise your bugs, but also integrates with
Github or Gitlabs, issue tracker for better remote support.


### Note:
**This application is still very much in alpha state, so expect some issues. If you get any issue try viewing the application
using /app_dev.php/* which uses the Symfony 2 debugging tool which should help in figuring out the problem.**  
     
1) Requirements
----------------------------------
* php > 5.6 (mcrypt)
* mysql or mariadb
* webserver ( apache, nginx, php server)
* Git install on command line for server/computer with repository

2) Installing the Web GIT Version Control System
----------------------------------

When it comes to installing, you have the
following options.

### Use Composer (*recommended*)

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:
```
    $ curl -s http://getcomposer.org/installer | php
```
Create a new folder somewhere under your web server root directory ( eg /var/www/version-control/) and run the following command:
```
    $ php -d memory_limit=-1 composer.phar create-project -s dev sshversioncontrol/git-web-client 
```

If composer is install globally then just run the command: 
```
    $ composer create-project -s dev sshversioncontrol/git-web-client 
```

Composer will install the project and all its dependencies under the current directory.

### Download an Archive File

You can also download a zip of the GIT Web Interface Client and unpack it somewhere under your web server root directory. You will still need to run composer to download other dependencies.

    $ curl -s http://getcomposer.org/installer | php
    $ php -d memory_limit=-1 composer.phar install
    
    or if composer is install globally

    $ composer install


You will be prompted to enter in the database configuration.
Please check the `app/config/parameters.yml` to make sure that the database settings are correct

3) Checking your System Configuration
-------------------------------------

Execute the `check.php` script from the command line, to check your system config:

    $ php app/check.php

The script returns a status code of `0` if all mandatory requirements are met,
`1` otherwise.

4) Install the database
--------------------------------
If the database is not already created run:

    $ php app/console doctrine:database:create
    
To install the schema and inital data run the following command:

    $ php app/console version:install

To create a new administrator run the following command:

    $ php app/console version:admin:create

5) Web Server
--------------------------------
Below are examples of 3 different web servers configurations that you can use:

### Apache Example Config

The minimum configuration to get the application running under Apache is
```
    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            AllowOverride All
            Order Allow,Deny
            Allow from All
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>
```

Check out [Symfony 2 web config page](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html) for more details
### Nginx Example Config
The minimum configuration to get the application running under Nginx is
```
    server {
        server_name domain.tld www.domain.tld;
        root /var/www/project/web;

        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app.php$is_args$args;
        }
        # DEV
        # This rule should only be placed on your development environment
        # In production, don't include this and don't deploy app_dev.php or config.php
        location ~ ^/(app_dev|config)\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
        }
        # PROD
        location ~ ^/app\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/app.php/some-path
            # Remove the internal directive to allow URIs like this
            internal;
        }

        error_log /var/log/nginx/project_error.log;
        access_log /var/log/nginx/project_access.log;
    }
```
### Php Web Server
If you just want to test out the application you can use the default php webserver.
You will not need to configure anything, but this will run slower:

```
    $ php app/console server:run
```

5) Done
--------------------------------

Congratulations! You're now ready to use the Version Control System.

#### Windows Install:
If you install this onto a windows machine using WAMP you will have issues with connection to remote
repositories using a ssh key. To resolve this check out [/Documentation/WindowsGit.md](Documentation/WindowsGit.md) 

### Testing:
To run phpunit test you will need a test database. Run the following command:

    $ php app/console doctrine:database:create --env=test
    
To install the schema and inital data run the following command:

    $ php app/console version:install --env=test

    $ php app/console doctrine:fixtures:load --env=test
