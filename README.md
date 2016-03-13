Online GIT Version Control System
========================

Welcome to the Online GIT Version Control System. This systems allows you to commit,search history,
branch, push, pull, and many other action on git repositories, locate locally and on remote servers with 
SSH access. 

It has an issue tracking system, to help organise your bugs. (Hope to integrate with github, gitlab, and jira in the future).    

### Why Create another GIT Client
I have been using GIT for a number of years now and love it. Been using it in IDEs, and mostly from command line.
I also use you GIT to deploy changes to live servers. My typical set up is a Development Server, Staging Server, and
Live server. My computer is Windows (I know, Sigh... ) and a servers for Development(Internal to office), Staging(Preview/Testing server), Live Servers running Debian/Ubuntu.

A lot of the sites I run rely on certain OS packages and can be a nightmare to set up on Windows so usually work over the network 
with samba. This cause the first of the issues. Access right to .git folder.     


1) Installing the Online GIT Version Control System
----------------------------------

When it comes to installing, you have the
following options.

### Use Composer (*recommended*)

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `create-project` command to generate a new Symfony application:

    php composer.phar create-project (project) path/to/install

Composer will install the project and all its dependencies under the
`path/to/install` directory.

### Download an Archive File

To quickly to test the project, you can also download an of the Version Control
System and unpack it somewhere under your web server root directory.


2) Checking your System Configuration
-------------------------------------

Before starting coding, make sure that your local system is properly
configured for Symfony.

Execute the `check.php` script from the command line:

    php app/check.php

The script returns a status code of `0` if all mandatory requirements are met,
`1` otherwise.

Access the `config.php` script from a browser:

    http://localhost/path-to-project/web/config.php

If you get any warnings or recommendations, fix them before moving on.

3) Install the database
--------------------------------

3) Browsing the Demo Application
--------------------------------

Congratulations! You're now ready to use the Version Control System.

From the `config.php` page, click the "Bypass configuration and go to the
Welcome page" link to load up your first Symfony page.

You can also use a web-based configurator by clicking on the "Configure your
Symfony Application online" link of the `config.php` page.

To see a real-live Symfony page in action, access the following page:

    web/app_dev.php/demo/hello/Fabien



