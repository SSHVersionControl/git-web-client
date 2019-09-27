<?php
// src/VersionControl/GitCommandBundle/GitCommands/GitTagCommand.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands\Command;

use RuntimeException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidBranchNameException;
use VersionControl\GitCommandBundle\Entity\GitTag;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitTagCommand extends AbstractGitCommand
{
    /**
     * List all of the Tags in your repository.
     *
     * @return array | GitTag
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function getTags()
    {
        $tagEntities = [];
        $command = "git for-each-ref --format '%(refname:short)|%(subject)|%(taggerdate)|%(taggername)|%(taggeremail)"
            ."|%(*objectname)|%(*objectname:short)' refs/tags  --sort=taggerdate";

        $tags = $this->command->runCommand($command);
        $lines = $this->splitOnNewLine($tags);

        foreach ($lines as $line) {
            $tagEntities[] = new GitTag($line);
        }

        rsort($tagEntities);

        return $tagEntities;
    }

    /**
     * Creates a new Tag.
     *
     * @param $version
     * @param $message
     * @param bool $commitShortCode
     *
     * @return string command response
     * @throws RuntimeException
     * @throws InvalidBranchNameException
     * @throws RunGitCommandException
     */
    public function createAnnotatedTag($version, $message, $commitShortCode = false): string
    {
        if (false === $this->validateTagName($version)) {
            throw new InvalidBranchNameException('This is not a valid branch name');
        }

        $command = sprintf($this->initGitCommand().' tag -a %s -m %s', escapeshellarg($version), escapeshellarg($message));
        if ($commitShortCode) {
            $command .= ' ' . escapeshellarg($commitShortCode);
        }

        return $this->command->runCommand($command);
    }

    /**
     * Push specified tag to the remote repository.
     *
     * @param string $remote The remote server to push to eg origin
     * @param string $tag The tag to push to the remote server eg v0.1.0
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function pushTag($remote, $tag): string
    {
        $command = sprintf($this->initGitCommand().' push %s %s', escapeshellarg(trim($remote)), escapeshellarg(trim($tag)));

        return $this->command->runCommand($command);
    }

    /**
     * Validates Tag name. Checks if a tag name is allowed.
     *
     * @param string $tagName Name of new branch
     *
     * @return bool true if valid branch name
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function validateTagName($tagName): bool
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            return true;
        }

        $this->command->runCommand(sprintf('git check-ref-format "refs/tags/%s"', $tagName), false);

        return !($this->command->getLastExitStatus() !== 0);
    }
}
