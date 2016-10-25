<?php

namespace VersionControl\DoctrineEncryptBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use VersionControl\DoctrineEncryptBundle\DependencyInjection\VersionControlDoctrineEncryptExtension;

class VersionControlDoctrineEncryptBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new VersionControlDoctrineEncryptExtension();
    }
}
