Online GIT Version Control System
========================

Welcome to the Online GIT Version Control System. This systems allows you to commit,search history,
branch, push, pull, and many other action on git repositories, locate locally and on remote servers with 
SSH access. This system has been build using symfony2. It comes with an inbuilt issue tracker, to help organise your bugs, but also integrates with
Github or Gitlabs, issue tracker for better remote support.


### Why Create another GIT Client
I have been using GIT for a number of years now and love it, using it in IDEs, and mostly from command line.
I also use GIT to deploy changes to live servers. My typical set up is a Development Server, Staging Server, and
Live server running Debian or Ubuntu server, with a remote git repository (Gitlab). While using the command line was 
fine I found I missed having a nicer visual display of the files. I wanted to be able to switch between the environments 
and quickly see the different commits. I have not found an adequate git client that allows for SSH access to remote servers,
as the reasons for it is unconventional and possibly bad practice.      


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



