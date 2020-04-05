<?php

declare(strict_types=1);

namespace VersionControl\GitCommandBundle\Tests\GitCommands;

use VersionControl\GitCommandBundle\GitCommands\Command\GitUserInterface;

final class GitUserFixture implements GitUserInterface
{
    /**
     * @return string
     */
    public function getUsername(): string
    {
        return 'Paul Schweppe';
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return 'paulschweppe@gmail.com';
    }
}
