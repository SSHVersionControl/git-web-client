<?php

    $db = parse_url(getenv('CLEARDB_DATABASE_URL'));

    $container->setParameter('database_driver', 'pdo_mysql');
    $container->setParameter('database_host', $db['host']);
    $container->setParameter('database_port', $db['port']);
    $container->setParameter('database_name', substr($db["path"], 1));
    $container->setParameter('database_user', $db['user']);
    $container->setParameter('database_password', $db['pass']);
    $container->setParameter('secret', getenv('SECRET'));
    $container->setParameter('locale', 'en');
    $container->setParameter('mailer_transport', null);
    $container->setParameter('mailer_host', null);
    $container->setParameter('mailer_user', null);
    $container->setParameter('mailer_password', null);
    
    $container->setParameter('debug_toolbar', true);
    $container->setParameter('debug_redirects', false);
    $container->setParameter('use_assetic_controller', true);
    $container->setParameter('enable_registration', false);
    $container->setParameter('secret_key',  getenv('SECRET_KEY_32'));

