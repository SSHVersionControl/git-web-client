#Installation

1. Download VersionControlDoctrineEncryptBundle using composer
2. Enable the Bundle

### Step 1: Download VersionControlDoctrineEncryptBundle using composer

Add VersionControlDoctrineEncryptBundle in your composer.json:

```js
{
    "require": {
        "versioncontrol/doctrine-encrypt-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update versioncontrol/doctrine-encrypt-bundle
```

Composer will install the bundle to your project's `vendor/versioncontrol` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new VersionControl\DoctrineEncryptBundle\VersionControlDoctrineEncryptBundle(),
    );
}
```