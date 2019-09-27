<?php

declare(strict_types=1);

namespace VersionControl\GitCommandBundle\GitCommands\Command;

interface GitUserInterface
{
    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @return string
     */
    public function getEmail(): string;
}
